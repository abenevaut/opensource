#!/bin/bash
# entrypoint.sh — single entry point for the runner container.
#
# Runs in two phases:
#   • PHASE 1 (root):   verify bind mounts, stage the runner, fix Docker socket
#                       GID, then re-exec itself as the "docker" user.
#   • PHASE 2 (docker): obtain a registration token, configure & start the
#                       runner, forward signals for clean shutdown.
#
# The same script handles both phases — the user switch is detected via $UID.
set -e

RUNNER_DIR="${RUNNER_DIR:-/home/docker/actions-runner}"
RUNNER_DIST=/opt/runner-dist

# ─────────────────────────────────────────────────────────────────────────────
#  PHASE 1 — runs as root
# ─────────────────────────────────────────────────────────────────────────────
if [ "$(id -u)" = "0" ]; then
    echo "[entrypoint] RUNNER_DIR=${RUNNER_DIR}"

    # ── Verify RUNNER_DIR is bind-mounted from the host with the SAME path ──
    # REQUIRED for DooD container/services jobs: when container hooks call
    # `docker create -v ${RUNNER_DIR}/_work:/__w ...`, the host Docker daemon
    # resolves `${RUNNER_DIR}/_work` on the HOST filesystem. If host path !=
    # container path, the daemon fails with:
    #   "Bind mount failed: '/home/docker/actions-runner/_work' does not exist"
    #
    # We query the host Docker daemon directly via `docker inspect` on our own
    # container. This is the ONLY reliable check on filesystems like Synology
    # btrfs, where /proc/self/mountinfo field 4 does NOT reflect the actual
    # host path (it shows the btrfs subvolume internal path instead).
    if [ ! -S /var/run/docker.sock ]; then
        echo "[entrypoint] FATAL: /var/run/docker.sock is not mounted." >&2
        echo "[entrypoint]        Add: -v /var/run/docker.sock:/var/run/docker.sock" >&2
        exit 1
    fi
    SELF_ID=$(cat /proc/self/cgroup 2>/dev/null | awk -F/ '/docker|containerd|kubepods/ {print $NF; exit}')
    if [ -z "${SELF_ID}" ]; then
        SELF_ID=$(cat /etc/hostname 2>/dev/null)
    fi
    HOST_SOURCE=$(/usr/bin/docker inspect -f \
        "{{range .Mounts}}{{if eq .Destination \"${RUNNER_DIR}\"}}{{.Source}}{{end}}{{end}}" \
        "${SELF_ID}" 2>/dev/null || true)
    if [ -z "${HOST_SOURCE}" ]; then
        echo "[entrypoint] FATAL: ${RUNNER_DIR} is NOT bind-mounted from the host (no matching mount in docker inspect)." >&2
        echo "[entrypoint]        Self container ID used: ${SELF_ID}" >&2
        echo "[entrypoint]        Add to your docker-compose.yml volumes:" >&2
        echo "[entrypoint]          - ${RUNNER_DIR}:${RUNNER_DIR}" >&2
        exit 1
    fi
    if [ "${HOST_SOURCE}" != "${RUNNER_DIR}" ]; then
        echo "[entrypoint] FATAL: DooD path mismatch detected." >&2
        echo "[entrypoint]        Host source path : ${HOST_SOURCE}" >&2
        echo "[entrypoint]        Container path   : ${RUNNER_DIR}" >&2
        echo "[entrypoint]        These MUST be identical for container:/services: jobs to work," >&2
        echo "[entrypoint]        because the host Docker daemon resolves bind-mount sources on the" >&2
        echo "[entrypoint]        HOST filesystem — not inside this container." >&2
        echo "[entrypoint]" >&2
        echo "[entrypoint]        Fix: set RUNNER_DIR to match the host path AND mount it identically." >&2
        echo "[entrypoint]        Example (Synology):" >&2
        echo "[entrypoint]          environment:" >&2
        echo "[entrypoint]            - RUNNER_DIR=${HOST_SOURCE}" >&2
        echo "[entrypoint]          volumes:" >&2
        echo "[entrypoint]            - ${HOST_SOURCE}:${HOST_SOURCE}" >&2
        exit 1
    fi
    echo "[entrypoint] ✓ ${RUNNER_DIR} is bind-mounted from the host at the SAME path (DooD-ready)."

    # ── Stage the runner installation to the host-mounted volume (first run) ──
    if [ ! -f "${RUNNER_DIR}/run.sh" ]; then
        echo "[entrypoint] First run: staging runner installation to ${RUNNER_DIR}..."

        if [ ! -d "${RUNNER_DIST}" ] || [ ! -f "${RUNNER_DIST}/run.sh" ]; then
            echo "[entrypoint] ERROR: runner distribution not found at ${RUNNER_DIST}." >&2
            exit 1
        fi
        if ! mkdir -p "${RUNNER_DIR}"; then
            echo "[entrypoint] ERROR: cannot create ${RUNNER_DIR} — check your volume mount." >&2
            exit 1
        fi
        if [ ! -w "${RUNNER_DIR}" ]; then
            echo "[entrypoint] ERROR: ${RUNNER_DIR} is not writable — check volume permissions." >&2
            exit 1
        fi

        cp -a "${RUNNER_DIST}/." "${RUNNER_DIR}/"
        chown -R docker:docker "${RUNNER_DIR}"
        echo "[entrypoint] Runner installation staged."
    else
        echo "[entrypoint] Runner installation already present in ${RUNNER_DIR}."
    fi

    # ── Ensure _work exists (sub-dirs are mkdir'd on demand by docker-wrapper.sh) ──
    mkdir -p "${RUNNER_DIR}/_work"
    chown docker:docker "${RUNNER_DIR}" "${RUNNER_DIR}/_work" 2>/dev/null || true

    # ── Grant the "docker" user access to the host Docker socket ──
    if [ -S /var/run/docker.sock ]; then
        SOCK_GID=$(stat -c '%g' /var/run/docker.sock)
        if ! getent group "${SOCK_GID}" >/dev/null 2>&1; then
            groupadd --gid "${SOCK_GID}" dockerhost
        fi
        usermod -aG "${SOCK_GID}" docker
        echo "[entrypoint] Docker socket GID=${SOCK_GID} granted to user 'docker'."
    else
        echo "[entrypoint] WARNING: /var/run/docker.sock not found — DooD container jobs will fail."
    fi

    # ── Re-exec self as the "docker" user (PHASE 2) ──
    # `sudo -E` paired with `Defaults:docker env_keep += "..."` (set in the
    # Dockerfile sudoers) preserves RUNNER_DIR, ORGANIZATION, ACCESS_TOKEN, etc.
    exec sudo -u docker -E /bin/bash "$0" "$@"
fi

# ─────────────────────────────────────────────────────────────────────────────
#  PHASE 2 — runs as the "docker" user
# ─────────────────────────────────────────────────────────────────────────────
set -uo pipefail

: "${ACCESS_TOKEN:?ACCESS_TOKEN env var is required (GitHub PAT with admin:org or repo scope)}"
: "${ORGANIZATION:?ORGANIZATION env var is required (GitHub org or owner name)}"

REPOSITORY="${REPOSITORY:-}"
RUNNER_NAME="${RUNNER_NAME:-$(hostname)}"
RUNNER_LABELS="${RUNNER_LABELS:-self-hosted,linux,x64}"
RUNNER_WORKDIR="${RUNNER_WORKDIR:-_work}"
EPHEMERAL="${EPHEMERAL:-true}"

echo "[runner] RUNNER_DIR=${RUNNER_DIR}"
echo "[runner] RUNNER_NAME=${RUNNER_NAME}"
echo "[runner] RUNNER_LABELS=${RUNNER_LABELS}"
echo "[runner] EPHEMERAL=${EPHEMERAL}"

if [ ! -x "${RUNNER_DIR}/config.sh" ]; then
    echo "[runner] ERROR: ${RUNNER_DIR}/config.sh not found or not executable." >&2
    exit 1
fi

# Container hooks: enable container:/services: jobs from inside this DooD runner.
# Hooks live in /opt/container-hooks (image layer, not the host-mounted volume).
# https://github.com/actions/runner-container-hooks
export ACTIONS_RUNNER_CONTAINER_HOOKS=/opt/container-hooks/index.js
export ACTIONS_RUNNER_CONTAINER_NETWORK="${ACTIONS_RUNNER_CONTAINER_NETWORK:-host}"

if [ -n "${REPOSITORY}" ]; then
    API_URL="https://api.github.com/repos/${ORGANIZATION}/${REPOSITORY}/actions/runners/registration-token"
    RUNNER_URL="https://github.com/${ORGANIZATION}/${REPOSITORY}"
else
    API_URL="https://api.github.com/orgs/${ORGANIZATION}/actions/runners/registration-token"
    RUNNER_URL="https://github.com/${ORGANIZATION}"
fi

echo "[runner] Requesting registration token from ${API_URL}..."
REG_TOKEN=$(curl -fsSX POST \
    -H "Accept: application/vnd.github+json" \
    -H "Authorization: Bearer ${ACCESS_TOKEN}" \
    -H "X-GitHub-Api-Version: 2022-11-28" \
    "${API_URL}" | jq -r .token)

if [ -z "${REG_TOKEN}" ] || [ "${REG_TOKEN}" = "null" ]; then
    echo "[runner] Failed to obtain a registration token" >&2
    exit 1
fi

cd "${RUNNER_DIR}"

CONFIG_ARGS=(
    --unattended
    --replace
    --url "${RUNNER_URL}"
    --token "${REG_TOKEN}"
    --name "${RUNNER_NAME}"
    --labels "${RUNNER_LABELS}"
    --work "${RUNNER_WORKDIR}"
)
if [ "${EPHEMERAL}" = "true" ]; then
    CONFIG_ARGS+=(--ephemeral)
fi

./config.sh "${CONFIG_ARGS[@]}"

cleanup() {
    echo "[runner] Removing runner..."
    # NOTE: `config.sh remove` does NOT accept --unattended (only `config.sh` does).
    ./config.sh remove --token "${REG_TOKEN}" || true
}
trap 'cleanup; exit 130' INT
trap 'cleanup; exit 143' TERM

./run.sh & wait $!


