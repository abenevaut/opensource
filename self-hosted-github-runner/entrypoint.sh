#!/bin/bash
# entrypoint.sh — single entry point for the runner container.
#
# Runs in two phases:
#   • PHASE 1 (root):   verify bind mounts, stage the runner, authenticate
#                       to GHCR (CAUSE-01 fix), pre-create _work sub-dirs
#                       (CAUSE-02 fix), fix Docker socket GID, then
#                       re-exec itself as the "docker" user.
#   • PHASE 2 (docker): validate env vars, obtain a registration token,
#                       configure & start the runner, forward signals for
#                       clean shutdown.
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

    # ── Gate 1: Verify Docker socket is mounted ──────────────────────────────
    # Without the socket, DooD (and Gate 2) cannot work.
    if [ ! -S /var/run/docker.sock ]; then
        echo "[entrypoint] FATAL: /var/run/docker.sock is not mounted." >&2
        echo "[entrypoint]        Add: -v /var/run/docker.sock:/var/run/docker.sock" >&2
        exit 1
    fi

    # ── Gate 2: Verify RUNNER_DIR is bind-mounted with the SAME path ─────────
    # REQUIRED for DooD container/services jobs: when container hooks call
    # `docker create -v ${RUNNER_DIR}/_work:/__w ...`, the host Docker daemon
    # resolves `${RUNNER_DIR}/_work` on the HOST filesystem. If host path !=
    # container path, the daemon fails with "Bind mount failed".
    #
    # Strategy (3 levels):
    #   L1: docker inspect → conclusive if it returns a source path
    #       → FATAL only when source path ≠ RUNNER_DIR (confirmed mismatch)
    #   L2: /proc/mounts fallback → checks RUNNER_DIR is a real mount point
    #   L3: inconclusive (Synology btrfs, cgroup v2, …) → WARNING + continue
    #
    # We never FATAL on "can't verify" — only on confirmed mismatch.
    SELF_ID=$(cat /proc/self/cgroup 2>/dev/null \
        | awk -F/ '/docker|containerd|kubepods/ {print $NF; exit}' \
        | sed 's/^docker-//;s/\.scope$//')
    if [ -z "${SELF_ID}" ]; then
        SELF_ID=$(cat /etc/hostname 2>/dev/null)
    fi

    HOST_SOURCE=$(/usr/bin/docker inspect -f \
        "{{range .Mounts}}{{if eq .Destination \"${RUNNER_DIR}\"}}{{.Source}}{{end}}{{end}}" \
        "${SELF_ID}" 2>/dev/null || true)

    if [ -n "${HOST_SOURCE}" ]; then
        # L1 conclusive: inspect returned a source path — check for mismatch
        if [ "${HOST_SOURCE}" != "${RUNNER_DIR}" ]; then
            echo "[entrypoint] FATAL: DooD path mismatch detected." >&2
            echo "[entrypoint]   Container RUNNER_DIR : ${RUNNER_DIR}" >&2
            echo "[entrypoint]   Host source path     : ${HOST_SOURCE}" >&2
            echo "[entrypoint] These must be identical for DooD container:/services: jobs to work." >&2
            echo "[entrypoint] The host Docker daemon resolves bind-mount sources on the HOST" >&2
            echo "[entrypoint] filesystem — not inside this container." >&2
            echo "[entrypoint]" >&2
            echo "[entrypoint] Fix: use  -v ${HOST_SOURCE}:${HOST_SOURCE}" >&2
            echo "[entrypoint]      and  RUNNER_DIR=${HOST_SOURCE}" >&2
            exit 1
        fi
        echo "[entrypoint] ✓ ${RUNNER_DIR} is bind-mounted from the host at the SAME path (DooD-ready)."
    else
        # L1 inconclusive (inspect returned empty — common on Synology DSM btrfs / cgroup v2)
        # L2: check /proc/mounts as a fallback
        if grep -q " ${RUNNER_DIR} " /proc/mounts 2>/dev/null; then
            echo "[entrypoint] ✓ ${RUNNER_DIR} is a mount point (/proc/mounts confirmed)."
            echo "[entrypoint]   (docker inspect was inconclusive — normal on Synology DSM)"
        else
            # L3: cannot verify — issue a warning and continue.
            # A misconfiguration will surface at job time with a clear Docker error.
            echo "[entrypoint] WARNING: Could not verify that ${RUNNER_DIR} is bind-mounted." >&2
            echo "[entrypoint]          docker inspect container ID '${SELF_ID}' returned no mount info." >&2
            echo "[entrypoint]          This is normal on Synology DSM (btrfs / cgroup v2 / Container Manager)." >&2
            echo "[entrypoint]          Ensure your compose has:" >&2
            echo "[entrypoint]            volumes:" >&2
            echo "[entrypoint]              - ${RUNNER_DIR}:${RUNNER_DIR}" >&2
            echo "[entrypoint]          Continuing — a misconfigured path will fail at job time." >&2
        fi
    fi

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

    # ── CAUSE-02 fix: Pre-create ALL standard _work sub-directories ──────────
    # The container hook (index.js) calls `docker create` with these exact bind mounts:
    #   -v=RUNNER_DIR/_work:/__w
    #   -v=RUNNER_DIR/externals:/__e:ro
    #   -v=RUNNER_DIR/_work/_temp:/__w/_temp
    #   -v=RUNNER_DIR/_work/_actions:/__w/_actions
    #   -v=RUNNER_DIR/_work/_tool:/__w/_tool          ← NOTE: _tool not _tool_cache!
    #   -v=RUNNER_DIR/_work/_temp/_github_home:/github/home
    #   -v=RUNNER_DIR/_work/_temp/_github_workflow:/github/workflow
    # ALL source paths must exist on the HOST before `docker start` fires.
    echo "[entrypoint] Pre-creating standard _work sub-directories (CAUSE-02 fix)..."
    mkdir -p \
        "${RUNNER_DIR}/_work" \
        "${RUNNER_DIR}/_work/_temp" \
        "${RUNNER_DIR}/_work/_temp/_github_home" \
        "${RUNNER_DIR}/_work/_temp/_github_workflow" \
        "${RUNNER_DIR}/_work/_actions" \
        "${RUNNER_DIR}/_work/_tool" \
        "${RUNNER_DIR}/_work/_tool_cache" \
        "${RUNNER_DIR}/_work/_PipelineMapping" \
        "${RUNNER_DIR}/_diag"
    chown -R docker:docker \
        "${RUNNER_DIR}/_work" \
        "${RUNNER_DIR}/_diag" 2>/dev/null || true
        "${RUNNER_DIR}/_work" \
        "${RUNNER_DIR}/_diag" 2>/dev/null || true

    # ── CAUSE-01 fix: Authenticate to GHCR if GHCR_TOKEN is set (DA-08 Mech. B) ──
    # The host Docker daemon needs credentials to pull private GHCR images used
    # in `container:` directives. Without credentials, the Pre Job Hook exits 1
    # causing Worker exit code 102. Set GHCR_TOKEN and GHCR_USER to enable login.
    if [ -n "${GHCR_TOKEN:-}" ]; then
        echo "[entrypoint] GHCR_TOKEN set — logging in to ghcr.io as ${GHCR_USER:-x-token}..."
        echo "${GHCR_TOKEN}" | /usr/bin/docker login ghcr.io \
            -u "${GHCR_USER:-x-token}" --password-stdin
        echo "[entrypoint] ✓ Authenticated to ghcr.io."
    fi

    # ── DA-08 Mechanism A: DOCKER_CONFIG_HOST validation ─────────────────────
    # If DOCKER_CONFIG_HOST is set, it should be mounted as a volume at
    # /home/docker/.docker (read-only) in docker-compose.yml or docker run.
    # Here we validate the mount is present inside the container.
    if [ -n "${DOCKER_CONFIG_HOST:-}" ]; then
        if [ ! -d "/home/docker/.docker" ]; then
            echo "[entrypoint] WARNING: DOCKER_CONFIG_HOST is set but /home/docker/.docker is not mounted." >&2
            echo "[entrypoint]          Add to docker-compose.yml volumes:" >&2
            echo "[entrypoint]            - \${DOCKER_CONFIG_HOST}:/home/docker/.docker:ro" >&2
        else
            echo "[entrypoint] ✓ Docker config directory mounted at /home/docker/.docker (DOCKER_CONFIG_HOST)."
        fi
    fi

    # ── Grant the "docker" user access to the host Docker socket ──────────────
    SOCK_GID=$(stat -c '%g' /var/run/docker.sock)
    if ! getent group "${SOCK_GID}" >/dev/null 2>&1; then
        groupadd --gid "${SOCK_GID}" dockerhost
    fi
    usermod -aG "${SOCK_GID}" docker
    echo "[entrypoint] Docker socket GID=${SOCK_GID} granted to user 'docker'."

    # ── Re-exec self as the "docker" user (PHASE 2) ───────────────────────────
    # `sudo -E` paired with `Defaults:docker env_keep += "..."` (set in the
    # Dockerfile sudoers) preserves RUNNER_DIR, ORGANIZATION, ACCESS_TOKEN,
    # GHCR_TOKEN, GHCR_USER, DOCKER_CONFIG_HOST, etc.
    exec sudo -u docker -E /bin/bash "$0" "$@"
fi

# ─────────────────────────────────────────────────────────────────────────────
#  PHASE 2 — runs as the "docker" user
# ─────────────────────────────────────────────────────────────────────────────
set -uo pipefail

# ── Prevent accidental root execution in Phase 2 ─────────────────────────────
export RUNNER_ALLOW_RUNASROOT=0

# ── Required env validation ───────────────────────────────────────────────────
: "${ACCESS_TOKEN:?[runner] FATAL: ACCESS_TOKEN is required}"
: "${ORGANIZATION:?[runner] FATAL: ORGANIZATION is required}"

# ── Placeholder detection (FR-022) ───────────────────────────────────────────
# Default placeholder values in docker-compose.yml / .env.example intentionally
# fail fast so the container doesn't silently run with a non-functional config.
if [ "${ACCESS_TOKEN}" = "<YOUR-GITHUB-ACCESS-TOKEN>" ]; then
    echo "[runner] FATAL: ACCESS_TOKEN contains a placeholder value '<YOUR-GITHUB-ACCESS-TOKEN>'." >&2
    echo "[runner]        Edit your .env file and set a real GitHub Personal Access Token." >&2
    exit 1
fi
if [ "${ORGANIZATION}" = "<YOUR-GITHUB-ORGANIZATION>" ]; then
    echo "[runner] FATAL: ORGANIZATION contains a placeholder value '<YOUR-GITHUB-ORGANIZATION>'." >&2
    echo "[runner]        Edit your .env file and set your real GitHub organization name." >&2
    exit 1
fi

# ── Default values for optional variables ────────────────────────────────────
RUNNER_DIR="${RUNNER_DIR:-/home/docker/actions-runner}"
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

# ── Container hooks: enable container:/services: jobs (DooD) ─────────────────
# Hooks live in /opt/container-hooks (image layer, not the host-mounted volume).
# ACTIONS_RUNNER_CONTAINER_NETWORK defaults to "bridge" (not "host") to avoid
# unexpected host networking side-effects. Override with ACTIONS_RUNNER_CONTAINER_NETWORK=host
# if your jobs require host-network access.
export ACTIONS_RUNNER_CONTAINER_HOOKS=/opt/container-hooks/index.js
export ACTIONS_RUNNER_CONTAINER_NETWORK="${ACTIONS_RUNNER_CONTAINER_NETWORK:-bridge}"

echo "[runner] ACTIONS_RUNNER_CONTAINER_HOOKS=${ACTIONS_RUNNER_CONTAINER_HOOKS}"
echo "[runner] ACTIONS_RUNNER_CONTAINER_NETWORK=${ACTIONS_RUNNER_CONTAINER_NETWORK}"

# ── Derive API URL and runner registration URL ────────────────────────────────
if [ -n "${REPOSITORY}" ]; then
    API_URL="https://api.github.com/repos/${ORGANIZATION}/${REPOSITORY}/actions/runners/registration-token"
    RUNNER_URL="https://github.com/${ORGANIZATION}/${REPOSITORY}"
else
    API_URL="https://api.github.com/orgs/${ORGANIZATION}/actions/runners/registration-token"
    RUNNER_URL="https://github.com/${ORGANIZATION}"
fi

# ── Fetch registration token (with retry for transient network issues) ────────
# --retry 3 --retry-delay 10: covers up to ~30 s of DNS/network instability at boot
# (e.g., Synology DSM startup delay). Docker Compose restart: always handles longer outages.
echo "[runner] Requesting registration token from ${API_URL}..."
REG_TOKEN=$(curl \
    --retry 3 \
    --retry-delay 10 \
    --retry-connrefused \
    -fsSL \
    -X POST \
    -H "Accept: application/vnd.github+json" \
    -H "Authorization: Bearer ${ACCESS_TOKEN}" \
    -H "X-GitHub-Api-Version: 2022-11-28" \
    "${API_URL}" | jq -r .token)

if [ -z "${REG_TOKEN}" ] || [ "${REG_TOKEN}" = "null" ]; then
    echo "[runner] FATAL: Failed to obtain a registration token." >&2
    echo "[runner]        Check that ACCESS_TOKEN is valid and has the required scopes:" >&2
    echo "[runner]          Org-level : admin:org (classic) or org self-hosted runners: write" >&2
    echo "[runner]          Repo-level: repo (classic) or repo administration: write" >&2
    exit 1
fi

cd "${RUNNER_DIR}"

# ── Build config arguments ────────────────────────────────────────────────────
CONFIG_ARGS=(
    --unattended
    --replace
    --disableupdate
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

# ── Sentinel file for Docker healthcheck (T023) ───────────────────────────────
# Written after successful config.sh so the healthcheck confirms the runner is ready.
touch "${RUNNER_DIR}/.runner-ready"
echo "[runner] ✓ Runner configured and ready (sentinel: ${RUNNER_DIR}/.runner-ready)."

# ── Signal handling: clean deregistration on SIGTERM / SIGINT ────────────────
cleanup() {
    echo "[runner] Removing runner..."
    # NOTE: `config.sh remove` does NOT accept --unattended (only `config.sh` does).
    rm -f "${RUNNER_DIR}/.runner-ready"
    ./config.sh remove --token "${REG_TOKEN}" || true
}
trap 'cleanup; exit 130' INT
trap 'cleanup; exit 143' TERM

# ── CAUSE-02 fix: Pre-create _work sub-dirs before ./run.sh ─────────────
# The runner may clean _work/_temp between jobs. Pre-creating unconditionally
# here ensures the host daemon finds all bind-mount sources on every job start,
# complementing the docker-wrapper.sh interception layer.
# Full list mirrors docker create in container-hooks index.js:
#   _work/_tool (not _tool_cache!), _work/_temp/_github_home, _work/_temp/_github_workflow
mkdir -p \
    "${RUNNER_DIR}/_work" \
    "${RUNNER_DIR}/_work/_temp" \
    "${RUNNER_DIR}/_work/_temp/_github_home" \
    "${RUNNER_DIR}/_work/_temp/_github_workflow" \
    "${RUNNER_DIR}/_work/_actions" \
    "${RUNNER_DIR}/_work/_tool" \
    "${RUNNER_DIR}/_work/_tool_cache" \
    "${RUNNER_DIR}/_work/_PipelineMapping" \
    "${RUNNER_DIR}/_diag"

./run.sh & wait $!

