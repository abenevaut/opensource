#!/bin/bash
# entrypoint.sh — runs as root
# Detects the host Docker socket GID at runtime and grants the "docker" user access,
# then switches to that user to run start.sh.
set -e

if [ -S /var/run/docker.sock ]; then
    SOCK_GID=$(stat -c '%g' /var/run/docker.sock)
    # Create a group with the socket GID if it doesn't already exist
    if ! getent group "${SOCK_GID}" >/dev/null 2>&1; then
        groupadd --gid "${SOCK_GID}" dockerhost
    fi
    # Add the "docker" user to that group
    usermod -aG "${SOCK_GID}" docker
    echo "[entrypoint] Docker socket GID=${SOCK_GID} granted to user 'docker'."
else
    echo "[entrypoint] WARNING: /var/run/docker.sock not found — Docker-in-Docker will not work."
fi

# Switch to the non-root "docker" user, preserving all environment variables
exec sudo -u docker -E /bin/bash /start.sh

