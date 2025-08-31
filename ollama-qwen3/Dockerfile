ARG TYPE=latest
FROM ollama/ollama:${TYPE}

LABEL maintainer="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.source=https://github.com/abenevaut/opensource

ENV OLLAMA_MODEL=qwen3:0.6b

RUN apt-get update && apt-get install -y curl && rm -rf /var/lib/apt/lists/*

COPY rootfs/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["serve"]
