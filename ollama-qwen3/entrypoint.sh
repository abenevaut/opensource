#!/bin/sh
set -e

if ! ollama list | grep -q "$OLLAMA_MODEL"; then
  ollama pull "$OLLAMA_MODEL"
fi

exec /bin/ollama serve
