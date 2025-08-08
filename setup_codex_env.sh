#!/usr/bin/env bash
set -euo pipefail

# Install Codex CLI if not already installed
if ! command -v codex-cli >/dev/null 2>&1; then
  npm install -g @openai/codex-cli
fi

# Set environment variables (replace placeholder values)
export OPENAI_API_KEY="your-openai-api-key"
export OTHER_REQUIRED_KEY="your-other-key"

# Optionally persist variables for future shells
cat <<'EOV' >> ~/.bashrc
export OPENAI_API_KEY="your-openai-api-key"
export OTHER_REQUIRED_KEY="your-other-key"
EOV

echo "Codex environment setup complete. Remember to replace placeholders with actual keys."
