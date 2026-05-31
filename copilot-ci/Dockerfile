FROM python:3-alpine

LABEL maintainer="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.source=https://github.com/abenevaut/opensource
LABEL org.opencontainers.image.path="copilot-ci/Dockerfile"
LABEL org.opencontainers.image.title="copilot-ci"
LABEL org.opencontainers.image.description="Inherits from Docker Python images, this suite provides GitHub Copilot CLI with speckit stack to work with GitHub pipelines."
LABEL org.opencontainers.image.authors="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.licenses="MIT"
LABEL org.opencontainers.image.documentation="https://github.com/abenevaut/opensource/copilot-ci/README.md"

RUN apk --update --no-cache add \
        gnupg \
        gpgv \
        perl-utils \
        openssh-client \
        git \
        nodejs \
        npm \
# Remove alpine cache
    && rm -rf /var/cache/apk/*

# Add configuration files
COPY --chown=nobody rootfs/ /

ENV PATH="/root/.local/bin:$PATH"

RUN python3 -m ensurepip --upgrade
RUN pip3 install uv

RUN uv tool install specify-cli --from git+https://github.com/github/spec-kit.git@v0.6.1

RUN npm install -g @github/copilot
