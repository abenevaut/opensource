ARG TYPE=latest
FROM ollama/ollama:${TYPE}

LABEL maintainer="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.source=https://github.com/abenevaut/opensource

ENV OLLAMA_MODEL=qwen3:0.6b

RUN ollama pull ${OLLAMA_MODEL}

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
