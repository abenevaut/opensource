#!/bin/bash
# Trouve le fichier AGENTS.md le plus proche
AGENTS_FILE=$(find $(dirname "$1") -name "AGENTS.md" | head -n 1)
if [ -z "$AGENTS_FILE" ]; then
  SYSTEM_PROMPT=""
else
  SYSTEM_PROMPT=$(cat "$AGENTS_FILE")
fi

# Lance le modèle avec le prompt système
ollama run dsm7xx-php-quality-expert-qwen2.5 --system "$SYSTEM_PROMPT" -p "$(cat $1)"
