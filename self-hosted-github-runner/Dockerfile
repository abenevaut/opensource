#
# docker build --tag ghcr.io/abenevaut/self-hosted-github-runner:latest .
# docker push ghcr.io/abenevaut/self-hosted-github-runner:latest
#
# Declare TARGETPLATFORM/TARGETARCH BEFORE FROM so Docker Buildx can inject them
# and pull the correct base image for the target architecture.
ARG TARGETPLATFORM=linux/amd64
ARG TARGETARCH=amd64

FROM --platform=${TARGETPLATFORM} ubuntu:24.04

# Re-declare ARGs after FROM (ARGs before FROM are reset after the FROM instruction)
ARG RUNNER_VERSION="2.334.0"
# SHA256 per architecture (linux/amd64 → x64, linux/arm64 → arm64)
ARG RUNNER_SHA256_AMD64="048024cd2c848eb6f14d5646d56c13a4def2ae7ee3ad12122bee960c56f3d271"
ARG RUNNER_SHA256_ARM64="f44255bd3e80160eb25f71bc83d06ea025f6908748807a584687b3184759f7e4"
# TARGETARCH is automatically set by Docker Buildx (amd64 or arm64)
ARG TARGETARCH=amd64
# Container hooks version (DooD support — allows container:/services: jobs from inside a container)
# https://github.com/actions/runner-container-hooks/releases
ARG CONTAINER_HOOKS_VERSION="0.6.2"

# update the base packages and add a non-sudo user
# Ubuntu 24.04 ships with a default "ubuntu" user (uid 1000); remove it before creating "docker"
    && apt-get update -y \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
        ca-certificates curl gnupg jq git build-essential libssl-dev libffi-dev \
        python3 python3-venv python3-dev python3-pip sudo nodejs npm unzip \
    && rm -rf /var/lib/apt/lists/* \
    && (userdel -r ubuntu 2>/dev/null || true) \
    && useradd -m docker \
    && echo "docker ALL=(ALL) NOPASSWD: ALL" > /etc/sudoers.d/docker

# install Docker CLI (socket passthrough / DooD)
# the Docker daemon runs on the host; we only need the client inside the container
RUN install -m 0755 -d /etc/apt/keyrings \
    && curl -fsSL https://download.docker.com/linux/ubuntu/gpg \
        | gpg --dearmor -o /etc/apt/keyrings/docker.gpg \
    && chmod a+r /etc/apt/keyrings/docker.gpg \
    && echo \
        "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
        https://download.docker.com/linux/ubuntu noble stable" \
        > /etc/apt/sources.list.d/docker.list \
    && apt-get update -y \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
        docker-ce-cli docker-buildx-plugin docker-compose-plugin \
    && rm -rf /var/lib/apt/lists/* \
    && usermod -aG docker docker

# cd into the user directory, download and unzip the github actions runner
# Map Docker TARGETARCH (amd64/arm64) to GitHub runner naming (x64/arm64)
RUN cd /home/docker && mkdir actions-runner && cd actions-runner \
    && RUNNER_ARCH=$([ "${TARGETARCH}" = "amd64" ] && echo "x64" || echo "${TARGETARCH}") \
    && RUNNER_SHA256=$([ "${TARGETARCH}" = "amd64" ] && echo "${RUNNER_SHA256_AMD64}" || echo "${RUNNER_SHA256_ARM64}") \
    && curl -fsSL -o actions-runner.tar.gz \
        https://github.com/actions/runner/releases/download/v${RUNNER_VERSION}/actions-runner-linux-${RUNNER_ARCH}-${RUNNER_VERSION}.tar.gz \
    && echo "${RUNNER_SHA256}  actions-runner.tar.gz" | sha256sum -c - \
    && tar xzf ./actions-runner.tar.gz \
    && rm actions-runner.tar.gz

# install some additional dependencies
RUN chown -R docker:docker /home/docker && /home/docker/actions-runner/bin/installdependencies.sh

# Install runner container hooks (DooD — enables container:/services: jobs when runner is inside a container)
# https://github.com/actions/runner-container-hooks
RUN mkdir -p /home/docker/container-hooks \
    && curl -fsSL -o /tmp/container-hooks.zip \
        https://github.com/actions/runner-container-hooks/releases/download/v${CONTAINER_HOOKS_VERSION}/actions-runner-hook-docker-${CONTAINER_HOOKS_VERSION}.zip \
    && unzip /tmp/container-hooks.zip -d /home/docker/container-hooks \
    && rm /tmp/container-hooks.zip \
    && chown -R docker:docker /home/docker/container-hooks

# copy over the start.sh script
COPY start.sh /start.sh

# strip Windows CRLF line endings (if any) then make the script executable
RUN sed -i 's/\r//' /start.sh && chmod +x /start.sh

# since the config and run script for actions are not allowed to be run by root,
# set the user to "docker" so all subsequent commands are run as the docker user
USER docker
WORKDIR /home/docker/actions-runner

# set the entrypoint to the start.sh script
# Use explicit interpreter to avoid "exec format error" (shebang parsing issues)
ENTRYPOINT ["/bin/bash", "/start.sh"]
