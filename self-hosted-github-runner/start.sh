#!/bin/bash
set -euo pipefail

: "${ACCESS_TOKEN:?ACCESS_TOKEN env var is required (GitHub PAT with admin:org or repo scope)}"
: "${ORGANIZATION:?ORGANIZATION env var is required (GitHub org or owner name)}"
# Optional: if REPOSITORY is set, the runner is registered at the repo level instead of org level
REPOSITORY="${REPOSITORY:-}"
RUNNER_NAME="${RUNNER_NAME:-$(hostname)}"
RUNNER_LABELS="${RUNNER_LABELS:-self-hosted,linux,x64}"
RUNNER_WORKDIR="${RUNNER_WORKDIR:-_work}"
EPHEMERAL="${EPHEMERAL:-true}"

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

echo "Requesting registration token from ${API_URL}..."
REG_TOKEN=$(curl -fsSX POST \
    -H "Accept: application/vnd.github+json" \
    -H "Authorization: Bearer ${ACCESS_TOKEN}" \
    -H "X-GitHub-Api-Version: 2022-11-28" \
    "${API_URL}" | jq -r .token)

if [[ -z "${REG_TOKEN}" || "${REG_TOKEN}" == "null" ]]; then
    echo "Failed to obtain a registration token" >&2
    exit 1
fi

cd /home/docker/actions-runner

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

# Pre-create _work directory tree required by container hooks.
# Docker bind-mounts these paths into job containers BEFORE the runner creates them,
# causing "Bind mount failed: ... does not exist" if they are absent.
mkdir -p \
    "/home/docker/actions-runner/${RUNNER_WORKDIR}" \
    "/home/docker/actions-runner/${RUNNER_WORKDIR}/_actions" \
    "/home/docker/actions-runner/${RUNNER_WORKDIR}/_temp" \
    "/home/docker/actions-runner/${RUNNER_WORKDIR}/_tool"

./config.sh "${CONFIG_ARGS[@]}"

cleanup() {
    echo "Removing runner..."
    ./config.sh remove --unattended --token "${REG_TOKEN}" || true
}

trap 'cleanup; exit 130' INT
trap 'cleanup; exit 143' TERM

./run.sh & wait $!