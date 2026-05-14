#!/bin/bash
# docker-wrapper.sh — DooD bind-mount pre-creation fix.
#
# Installed as /usr/local/bin/docker (takes priority over /usr/bin/docker via PATH).
# Container hooks use `DOCKER=docker` (PATH lookup), so this script intercepts
# every `docker start` and `docker run` call and ensures all bind-mount source
# directories exist on the host filesystem BEFORE the Docker daemon tries to
# mount them.
#
# This fixes:
#   "Bind mount failed: '/path/_work/_temp/_github_home' does not exist"
# which occurs because the GitHub Actions runner cleans _work/_temp before each
# job, removing directories that entrypoint.sh would have pre-created.
#
# Strategy:
#   - `docker start <container>` → inspect the container's HostConfig.Binds and
#     mkdir -p each absolute source path that is missing.
#   - `docker run [opts] image [cmd]` → parse -v / --volume / --mount flags from
#     the argv and mkdir -p each absolute source path before delegating.
#   - All other commands pass through unchanged.

set -u
REAL_DOCKER=/usr/bin/docker

# mkdir -p a single bind-mount source path if it's an absolute path that doesn't
# already exist as a non-directory (e.g. /var/run/docker.sock is a socket — skip).
ensure_dir() {
    local src="$1"
    [[ -z "$src" ]] && return 0
    [[ "$src" != /* ]] && return 0   # skip named volumes
    [[ -e "$src" && ! -d "$src" ]] && return 0  # skip files/sockets
    mkdir -p "$src" 2>/dev/null || true
}

# Parse "src[:dst[:opts]]" → echo src
extract_v_source() {
    echo "${1%%:*}"
}

# Parse "type=bind,source=/foo,target=/bar,..." or "type=bind,src=/foo,..." → echo source
extract_mount_source() {
    local spec="$1" part src=""
    IFS=',' read -ra parts <<< "$spec"
    for part in "${parts[@]}"; do
        case "$part" in
            source=*|src=*) src="${part#*=}";;
        esac
    done
    echo "$src"
}

case "${1:-}" in
    start)
        # Container ID(s) are the trailing positional args (after any --flags).
        # Robust enough: just iterate non-flag args from position 2 onwards.
        shift
        for arg in "$@"; do
            [[ "$arg" == -* ]] && continue
            "$REAL_DOCKER" inspect \
                --format '{{range .HostConfig.Binds}}{{println .}}{{end}}' \
                "$arg" 2>/dev/null \
            | while IFS= read -r bind; do
                ensure_dir "$(extract_v_source "$bind")"
            done
        done
        exec "$REAL_DOCKER" start "$@"
        ;;
    run|create)
        # Walk the args and look for -v / --volume / --mount.
        i=2
        argv=("$@")
        while [ $i -le $# ]; do
            a="${argv[$((i-1))]:-}"
            case "$a" in
                -v|--volume)
                    next="${argv[$i]:-}"
                    ensure_dir "$(extract_v_source "$next")"
                    i=$((i+2))
                    ;;
                --volume=*|-v=*)
                    ensure_dir "$(extract_v_source "${a#*=}")"
                    i=$((i+1))
                    ;;
                --mount)
                    next="${argv[$i]:-}"
                    ensure_dir "$(extract_mount_source "$next")"
                    i=$((i+2))
                    ;;
                --mount=*)
                    ensure_dir "$(extract_mount_source "${a#*=}")"
                    i=$((i+1))
                    ;;
                *)
                    i=$((i+1))
                    ;;
            esac
        done
        exec "$REAL_DOCKER" "$@"
        ;;
    *)
        exec "$REAL_DOCKER" "$@"
        ;;
esac

