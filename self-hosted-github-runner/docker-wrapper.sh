#!/bin/bash
# docker-wrapper.sh — DooD bind-mount pre-creation fix
#
# Installed as /usr/local/bin/docker (takes priority over /usr/bin/docker via PATH).
# Container hooks use `DOCKER=docker` (PATH lookup), so this script intercepts
# every `docker start` call and ensures all bind-mount source directories exist
# on the host filesystem BEFORE the Docker daemon tries to mount them.
#
# This fixes:
#   "Bind mount failed: '/path/_work/_temp/_github_home' does not exist"
# which occurs because the GitHub Actions runner cleans _work/_temp before each
# job, removing directories that entrypoint.sh pre-created at container start.

REAL_DOCKER=/usr/bin/docker

if [[ "$1" == "start" ]]; then
    # The container ID is the last positional argument
    CONTAINER="${@: -1}"

    # Inspect all bind-mount sources and mkdir -p any that are missing
    "$REAL_DOCKER" inspect \
        --format '{{range .HostConfig.Binds}}{{println .}}{{end}}' \
        "$CONTAINER" 2>/dev/null \
    | while IFS= read -r bind; do
        [[ -z "$bind" ]] && continue
        # bind format: /host/path:/container/path[:options]
        src="${bind%%:*}"
        # Only handle absolute paths (skip named volumes)
        if [[ "$src" == /* ]]; then
            mkdir -p "$src" 2>/dev/null || true
        fi
    done
fi

exec "$REAL_DOCKER" "$@"

