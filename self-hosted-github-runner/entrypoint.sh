#!/bin/bash
# entrypoint.sh — runs as root
#
# 1. First-run: copy the runner installation from /opt/runner-dist to the
#    host-mounted volume at /home/docker/actions-runner.
#    Both the host and the container see the same absolute path, which is required
#    for Docker-outside-of-Docker (DooD) bind mounts to work.
# 2. Fix the Docker socket GID so the "docker" user can access it.
# 3. Switch to the non-root "docker" user and exec start.sh.
set -e

RUNNER_DIR=/home/docker/actions-runner
RUNNER_DIST=/opt/runner-dist

# ── Step 1: install runner files to the host-mounted volume (first run only) ──
if [ ! -f "${RUNNER_DIR}/run.sh" ]; then
    echo "[entrypoint] First run: copying runner installation to ${RUNNER_DIR}..."
    cp -a "${RUNNER_DIST}/." "${RUNNER_DIR}/"
    chown -R docker:docker "${RUNNER_DIR}"
    echo "[entrypoint] Runner installation copied."
else
    echo "[entrypoint] Runner installation already present in ${RUNNER_DIR}."
fi

# ── Step 2: grant the "docker" user access to the host Docker socket ──
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

# ── Step 3: switch to the non-root "docker" user and run the start script ──
exec sudo -u docker -E /bin/bash /start.sh
