ARG TYPE=latest
FROM ollama/ollama:${TYPE}

LABEL maintainer="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.source=https://github.com/abenevaut/opensource
LABEL org.opencontainers.image.path="ollama-and-friends/Dockerfile"
LABEL org.opencontainers.image.title="ollama-and-friends"
LABEL org.opencontainers.image.description="Custom ollama image that use qwen models and custom ones"
LABEL org.opencontainers.image.authors="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.licenses="MIT"
LABEL org.opencontainers.image.documentation="https://github.com/abenevaut/opensource/ollama-and-friends/README.md"

ENV OLLAMA_MODEL=qwen3.5:0.8b

RUN apt-get update && apt-get install -y curl && rm -rf /var/lib/apt/lists/*

COPY rootfs /

RUN chmod +x /entrypoint.sh
RUN chmod +x /find-agent-md.sh

COPY models /models

ENTRYPOINT ["/entrypoint.sh"]
CMD ["serve"]
