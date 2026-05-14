#!/bin/bash
# entrypoint.sh — runs as root
#
# 1. First-run: copy the runner installation from /opt/runner-dist to the
#    host-mounted volume at /home/docker/actions-runner.
#    Both the host and the container see the same absolute path, which is required
#    for Docker-outside-of-Docker (DooD) bind mounts to work.
# 2. Pre-create the _work directory tree so the host filesystem already contains
#    these paths before Docker tries to bind-mount them into a job container.
# 3. Fix the Docker socket GID so the "docker" user can access it.
# 4. Switch to the non-root "docker" user and exec start.sh.
set -e

RUNNER_DIR=/home/docker/actions-runner
RUNNER_DIST=/opt/runner-dist

# ── Step 1: install runner files to the host-mounted volume (first run only) ──
if [ ! -f "${RUNNER_DIR}/run.sh" ]; then
    echo "[entrypoint] First run: copying runner installation to ${RUNNER_DIR}..."
    cp -a "${RUNNER_DIST}/." "${RUNNER_DIR}/"
    echo "[entrypoint] Runner installation copied."
fi

# ── Step 2: pre-create _work tree (as root — always, safe to repeat) ──
# Container hooks pass these absolute paths to the host Docker daemon.
# The daemon resolves them on the HOST filesystem, so they must exist there
# BEFORE docker start is called on the job container.
mkdir -p \
    "${RUNNER_DIR}/_work" \
    "${RUNNER_DIR}/_work/_actions" \
    "${RUNNER_DIR}/_work/_temp" \
    "${RUNNER_DIR}/_work/_temp/_github_home" \
    "${RUNNER_DIR}/_work/_temp/_github_workflow" \
    "${RUNNER_DIR}/_work/_tool"

# Fix ownership so the "docker" user can write to the whole runner directory
chown -R docker:docker "${RUNNER_DIR}"
echo "[entrypoint] Runner directory ready: ${RUNNER_DIR}"

# ── Step 3: grant the "docker" user access to the host Docker socket ──
if [ -S /var/run/docker.sock ]; then
    SOCK_GID=$(stat -c '%g' /var/run/docker.sock)
    if ! getent group "${SOCK_GID}" >/dev/null 2>&1; then
        groupadd --gid "${SOCK_GID}" dockerhost
    fi
    usermod -aG "${SOCK_GID}" docker
    echo "[entrypoint] Docker socket GID=${SOCK_GID} granted to user 'docker'."
else
    echo "[entrypoint] WARNING: /var/run/docker.sock not found — Docker-in-Docker will not work."
fi

# ── Step 4: switch to the non-root "docker" user and run the start script ──
exec sudo -u docker -E /bin/bash /start.sh
