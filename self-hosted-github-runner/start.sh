#!/bin/bash
# start.sh — runs as the "docker" user (after entrypoint.sh switches via sudo).
#
# 1. Validate required env vars.
# 2. Request a registration token from the GitHub API (org-level or repo-level).
# 3. Configure the runner via ./config.sh (in ${RUNNER_DIR}).
# 4. Start ./run.sh and forward signals for clean shutdown.
set -euo pipefail

: "${ACCESS_TOKEN:?ACCESS_TOKEN env var is required (GitHub PAT with admin:org or repo scope)}"
: "${ORGANIZATION:?ORGANIZATION env var is required (GitHub org or owner name)}"

RUNNER_DIR="${RUNNER_DIR:-/home/docker/actions-runner}"
# Optional: if REPOSITORY is set, the runner is registered at the repo level instead of org level
REPOSITORY="${REPOSITORY:-}"
RUNNER_NAME="${RUNNER_NAME:-$(hostname)}"
RUNNER_LABELS="${RUNNER_LABELS:-self-hosted,linux,x64}"
RUNNER_WORKDIR="${RUNNER_WORKDIR:-_work}"
EPHEMERAL="${EPHEMERAL:-true}"

echo "[start] RUNNER_DIR=${RUNNER_DIR}"
echo "[start] RUNNER_NAME=${RUNNER_NAME}"
echo "[start] RUNNER_LABELS=${RUNNER_LABELS}"
echo "[start] EPHEMERAL=${EPHEMERAL}"

# Sanity check: the runner installation must be present in RUNNER_DIR.
# entrypoint.sh is responsible for staging /opt/runner-dist to RUNNER_DIR; if this
# file is missing, either entrypoint.sh failed or RUNNER_DIR was overridden after.
if [ ! -x "${RUNNER_DIR}/config.sh" ]; then
    echo "[start] ERROR: ${RUNNER_DIR}/config.sh not found or not executable." >&2
    echo "[start]        entrypoint.sh did not stage the runner correctly." >&2
    exit 1
fi

# Enable container hooks so that workflows using container:/services: work
# even when the runner itself is inside a Docker container (DooD pattern).
# Hooks live in /opt/container-hooks (image layer, NOT the host-mounted volume).
# https://github.com/actions/runner-container-hooks
export ACTIONS_RUNNER_CONTAINER_HOOKS=/opt/container-hooks/index.js
export ACTIONS_RUNNER_CONTAINER_NETWORK="${ACTIONS_RUNNER_CONTAINER_NETWORK:-host}"

if [[ -n "${REPOSITORY}" ]]; then
    API_URL="https://api.github.com/repos/${ORGANIZATION}/${REPOSITORY}/actions/runners/registration-token"
    RUNNER_URL="https://github.com/${ORGANIZATION}/${REPOSITORY}"
else
    API_URL="https://api.github.com/orgs/${ORGANIZATION}/actions/runners/registration-token"
    RUNNER_URL="https://github.com/${ORGANIZATION}"
fi

echo "[start] Requesting registration token from ${API_URL}..."
REG_TOKEN=$(curl -fsSX POST \
    -H "Accept: application/vnd.github+json" \
    -H "Authorization: Bearer ${ACCESS_TOKEN}" \
    -H "X-GitHub-Api-Version: 2022-11-28" \
    "${API_URL}" | jq -r .token)

if [[ -z "${REG_TOKEN}" || "${REG_TOKEN}" == "null" ]]; then
    echo "[start] Failed to obtain a registration token" >&2
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

if [[ "${EPHEMERAL}" == "true" ]]; then
    CONFIG_ARGS+=(--ephemeral)
fi

./config.sh "${CONFIG_ARGS[@]}"

cleanup() {
    echo "[start] Removing runner..."
    # NOTE: `config.sh remove` does NOT accept --unattended (only `config.sh` does).
    ./config.sh remove --token "${REG_TOKEN}" || true
}

trap 'cleanup; exit 130' INT
trap 'cleanup; exit 143' TERM

./run.sh & wait $!