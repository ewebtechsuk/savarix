#!/usr/bin/env bash
set -euo pipefail

# Install Codex CLI if not already installed
if ! command -v codex-cli >/dev/null 2>&1; then
  npm install -g @openai/codex-cli
fi

# Set environment variables (use placeholder values if unset)
OPENAI_API_KEY="${OPENAI_API_KEY:-your-openai-api-key}"
OTHER_REQUIRED_KEY="${OTHER_REQUIRED_KEY:-your-other-key}"
export OPENAI_API_KEY OTHER_REQUIRED_KEY

# Optionally persist variables for future shells without duplicates
for var in OPENAI_API_KEY OTHER_REQUIRED_KEY; do
  grep -q "^export ${var}=" ~/.bashrc || echo "export ${var}=\"${!var}\"" >> ~/.bashrc
done

echo "Codex environment setup complete. Remember to replace placeholder values with actual keys."