ARG TYPE=latest
FROM ollama/ollama:${TYPE}

LABEL maintainer="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.source=https://github.com/abenevaut/opensource
LABEL org.opencontainers.image.path="ollama-qwen3/Dockerfile"
LABEL org.opencontainers.image.title="ollama-qwen3"
LABEL org.opencontainers.image.description="Custom ollama image that use qwen3 model"
LABEL org.opencontainers.image.authors="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.licenses="MIT"
LABEL org.opencontainers.image.documentation="https://github.com/abenevaut/opensource/ollama-qwen3/README.md"

ENV OLLAMA_MODEL=qwen3:0.6b

RUN apt-get update && apt-get install -y curl && rm -rf /var/lib/apt/lists/*

COPY rootfs/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["serve"]
