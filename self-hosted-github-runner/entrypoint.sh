#!/bin/bash
# entrypoint.sh — runs as root
#
# 1. First-run only: copy the runner installation from /opt/runner-dist (image layer)
#    to the host-mounted volume at ${RUNNER_DIR}. Both host and container MUST see
#    the same absolute path (DooD requirement — the host Docker daemon resolves
#    bind-mount sources on the HOST filesystem).
# 2. Ensure _work exists (root of all per-job state).
# 3. Fix Docker socket GID so the "docker" user can talk to the host daemon.
# 4. Switch to the non-root "docker" user and exec start.sh.
#
# NOTE: bind-mount source paths under _work/_temp (e.g. _github_home, _github_workflow)
#       are NOT pre-created here because the runner cleans _work/_temp before each job.
#       They are created on-demand by /usr/local/bin/docker (docker-wrapper.sh) which
#       intercepts `docker start` / `docker run` and mkdir -p's the missing paths.
set -e

RUNNER_DIR="${RUNNER_DIR:-/home/docker/actions-runner}"
RUNNER_DIST=/opt/runner-dist

echo "[entrypoint] RUNNER_DIR=${RUNNER_DIR}"

# ── Step 1: install runner files to the host-mounted volume (first run only) ──
if [ ! -f "${RUNNER_DIR}/run.sh" ]; then
    echo "[entrypoint] First run: staging runner installation to ${RUNNER_DIR}..."

    # Verify the runner distribution exists in the image layer
    if [ ! -d "${RUNNER_DIST}" ] || [ ! -f "${RUNNER_DIST}/run.sh" ]; then
        echo "[entrypoint] ERROR: runner distribution not found at ${RUNNER_DIST}." >&2
        echo "[entrypoint]        The image may be corrupted or built incorrectly." >&2
        exit 1
    fi

    # Ensure the target directory exists. If the volume mount is misconfigured the
    # mkdir will fail with a clear message instead of a confusing cp error.
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

# ── Step 2: ensure _work exists and is owned by the runner user ──
# Sub-directories like _temp/_github_home are created on-demand by docker-wrapper.sh.
mkdir -p "${RUNNER_DIR}/_work"
chown docker:docker "${RUNNER_DIR}" "${RUNNER_DIR}/_work" 2>/dev/null || true

# ── Step 3: grant the "docker" user access to the host Docker socket ──
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

# ── Step 4: switch to the non-root "docker" user and run the start script ──
# `sudo -E` is paired with `Defaults:docker env_keep += "..."` (set in the Dockerfile)
# to preserve RUNNER_DIR, ORGANIZATION, ACCESS_TOKEN, etc. across the user switch.
exec sudo -u docker -E /bin/bash /start.sh
