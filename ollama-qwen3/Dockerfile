ARG TYPE=latest
FROM ollama/ollama:${TYPE}

LABEL maintainer="Antoine Benevaut <me@abenevaut.dev>"
LABEL org.opencontainers.image.source=https://github.com/abenevaut/dev.abenevaut

RUN ollama pull qwen3:0.6b
