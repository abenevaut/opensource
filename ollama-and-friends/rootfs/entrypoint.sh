#!/bin/sh
set -e

/bin/ollama serve &
until curl -s http://localhost:11434/ | grep -q 'Ollama'; do sleep 1; done
/bin/ollama pull qwen2.5:7b-instruct-q4_K_M
/bin/ollama pull mistral:7b-instruct-v0.3-q4_K_M
/bin/ollama pull codellama:7b-instruct-q4_K_M
/bin/ollama pull qwen2.5-coder:7b-instruct-q4_K_M

/bin/ollama create abenevaut/generalist-writer:dsm7xx-mistral -f /models/dsm7xx-generalist-writer-mistral.modelfile
/bin/ollama create abenevaut/generalist-writer-mistral:dsm7xx-qwen2.5 -f /models/dsm7xx-generalist-writer-qwen2.5.modelfile

/bin/ollama create abenevaut/php-quality-expert:dsm7xx-codellama -f /models/dsm7xx-php-quality-expert-codellama.modelfile
/bin/ollama create abenevaut/php-quality-expert:dsm7xx-mistral -f /models/dsm7xx-php-quality-expert-mistral.modelfile
/bin/ollama create abenevaut/php-quality-expert:dsm7xx-qwen2.5 -f /models/dsm7xx-php-quality-expert-qwen2.5.modelfile

pkill ollama

# Remplace le process par ollama avec tous les arguments reçus
exec /bin/ollama "$@"
