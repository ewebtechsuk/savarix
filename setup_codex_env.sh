#!/usr/bin/env bash
set -euo pipefail

# Install Codex CLI if not already installed
if ! command -v codex-cli >/dev/null 2>&1; then
  if command -v npm >/dev/null 2>&1; then
    npm install -g @openai/codex-cli
  else
    echo "npm is required to install codex-cli but was not found." >&2
  fi

fi

# Set environment variables (use placeholder values if unset)
OPENAI_API_KEY="${OPENAI_API_KEY:-your-openai-api-key}"
OTHER_REQUIRED_KEY="${OTHER_REQUIRED_KEY:-your-other-key}"
export OPENAI_API_KEY OTHER_REQUIRED_KEY

# Optionally persist variables for future shells without duplicates
{
  BASHRC="$HOME/.bashrc"
  touch "$BASHRC"
  for var in OPENAI_API_KEY OTHER_REQUIRED_KEY; do
    grep -q "^export ${var}=" "$BASHRC" || printf 'export %s="%s"\n' "$var" "${!var}" >> "$BASHRC"
  done
}

echo "Codex environment setup complete. Remember to replace placeholder values with actual keys."