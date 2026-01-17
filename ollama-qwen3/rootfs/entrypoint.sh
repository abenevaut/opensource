#!/bin/sh
set -e

/bin/ollama serve &
until curl -s http://localhost:11434/ | grep -q 'Ollama'; do sleep 1; done
/bin/ollama pull qwen3:0.6b
pkill ollama

# Remplace le process par ollama avec tous les arguments reçus
exec /bin/ollama "$@"
