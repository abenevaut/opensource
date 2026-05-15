#
# docker build --platform linux/amd64 --tag ghcr.io/abenevaut/self-hosted-github-runner:latest .
# docker push ghcr.io/abenevaut/self-hosted-github-runner:latest
#
# AMD64 ONLY. ARM64 is intentionally not supported — see README.md for details.
FROM --platform=linux/amd64 ubuntu:24.04

ARG RUNNER_VERSION="2.334.0"
ARG RUNNER_SHA256="048024cd2c848eb6f14d5646d56c13a4def2ae7ee3ad12122bee960c56f3d271"
# Container hooks version (DooD support — allows container:/services: jobs from inside a container)
# Compatibility matrix: https://github.com/actions/runner-container-hooks/releases
# v0.8.1 is verified compatible with actions/runner v2.334.0
ARG CONTAINER_HOOKS_VERSION="0.8.1"

# update the base packages and add a non-sudo user
# Ubuntu 24.04 ships with a default "ubuntu" user (uid 1000); remove it before creating "docker"
RUN apt-get update -y \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
        ca-certificates curl gnupg jq git build-essential libssl-dev libffi-dev \
        python3 python3-venv python3-dev python3-pip sudo nodejs npm unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && (userdel -r ubuntu 2>/dev/null || true) \
    && useradd -m -u 1000 docker \
    && echo "docker ALL=(ALL) NOPASSWD: ALL" > /etc/sudoers.d/docker \
    # Preserve runner-related environment variables when entrypoint.sh switches
    # to the "docker" user via `sudo -u docker -E`. Without this, sudo's default
    # `env_reset` policy strips them and entrypoint.sh falls back to defaults.
    && echo 'Defaults:docker env_keep += "RUNNER_DIR ORGANIZATION ACCESS_TOKEN REPOSITORY RUNNER_NAME RUNNER_LABELS RUNNER_WORKDIR EPHEMERAL ACTIONS_RUNNER_CONTAINER_HOOKS ACTIONS_RUNNER_CONTAINER_NETWORK GHCR_TOKEN GHCR_USER DOCKER_CONFIG_HOST RUNNER_ALLOW_RUNASROOT"' \
        >> /etc/sudoers.d/docker \
    && chmod 0440 /etc/sudoers.d/docker

# install Docker CLI (socket passthrough / DooD)
# the Docker daemon runs on the host; we only need the client inside the container
RUN install -m 0755 -d /etc/apt/keyrings \
    && curl -fsSL https://download.docker.com/linux/ubuntu/gpg \
        | gpg --dearmor -o /etc/apt/keyrings/docker.gpg \
    && chmod a+r /etc/apt/keyrings/docker.gpg \
    && echo \
        "deb [arch=amd64 signed-by=/etc/apt/keyrings/docker.gpg] \
        https://download.docker.com/linux/ubuntu noble stable" \
        > /etc/apt/sources.list.d/docker.list \
    && apt-get update -y \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
        docker-ce-cli docker-buildx-plugin docker-compose-plugin \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && usermod -aG docker docker

# Install the GitHub Actions runner into /opt/runner-dist (NOT the runtime workdir).
# On container startup, entrypoint.sh copies this to the host-mounted volume at
# ${RUNNER_DIR} so that Docker (host daemon) can resolve those paths.
RUN mkdir -p /opt/runner-dist \
    && curl -fsSL -o /opt/runner-dist/actions-runner.tar.gz \
        https://github.com/actions/runner/releases/download/v${RUNNER_VERSION}/actions-runner-linux-x64-${RUNNER_VERSION}.tar.gz \
    && echo "${RUNNER_SHA256}  /opt/runner-dist/actions-runner.tar.gz" | sha256sum -c - \
    && tar xzf /opt/runner-dist/actions-runner.tar.gz -C /opt/runner-dist \
    && rm /opt/runner-dist/actions-runner.tar.gz

# install some additional dependencies
RUN /opt/runner-dist/bin/installdependencies.sh

# Install runner container hooks into /opt/container-hooks (permanent, inside the image).
# This path is NOT on the host-mounted volume, which is intentional: the hooks run
# inside the runner container and do not need to be visible to the host Docker daemon.
# Version pinned for compatibility with Runner v${RUNNER_VERSION}.
RUN mkdir -p /opt/container-hooks \
    && curl -fsSL -o /tmp/container-hooks.zip \
        https://github.com/actions/runner-container-hooks/releases/download/v${CONTAINER_HOOKS_VERSION}/actions-runner-hooks-docker-${CONTAINER_HOOKS_VERSION}.zip \
    && unzip /tmp/container-hooks.zip -d /opt/container-hooks \
    && rm /tmp/container-hooks.zip \
    && chown -R docker:docker /opt/container-hooks

# copy over the entrypoint
COPY entrypoint.sh /entrypoint.sh

# Docker wrapper: intercepts `docker start` / `docker run` / `docker create` to mkdir -p all
# bind-mount source paths before the host Docker daemon tries to mount them
# (DooD bind-mount fix). Placed in /usr/local/bin/ so it takes priority over
# /usr/bin/docker via PATH. Container hooks resolve `docker` via PATH lookup.
COPY docker-wrapper.sh /usr/local/bin/docker

# strip Windows CRLF line endings (if any) then make the scripts executable
RUN sed -i 's/\r//' /entrypoint.sh /usr/local/bin/docker \
    && chmod +x /entrypoint.sh /usr/local/bin/docker

# entrypoint.sh runs as root to:
# 1. Copy the runner installation from /opt/runner-dist to the host-mounted volume.
# 2. Fix the Docker socket GID so the "docker" user can access it.
# 3. Switch to the non-root "docker" user via sudo.
# NOTE: WORKDIR is set to / to avoid implicitly creating /home/docker/actions-runner
#       inside the image — that path is intentionally left for the host-mounted volume.
USER root
WORKDIR /

ENTRYPOINT ["/bin/bash", "/entrypoint.sh"]
