#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
LOG_FILE="$ROOT_DIR/storage/logs/laravel.log"
DEFAULT_LINES=200

usage() {
  cat <<'USAGE'
Usage: scripts/tail_laravel_log.sh [line_count]

Display the most recent lines from storage/logs/laravel.log. Pass the optional
line_count argument to control how many lines are shown (defaults to 200).
USAGE
}

if [[ "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
  usage
  exit 0
fi

LINES=${1:-$DEFAULT_LINES}

if ! [[ $LINES =~ ^[0-9]+$ ]]; then
  echo "error: line_count must be a positive integer" >&2
  usage
  exit 1
fi

if [[ ! -f $LOG_FILE ]]; then
  cat <<'EOF2' >&2
error: storage/logs/laravel.log does not exist.
Run the application once (or reproduce the failure) so Laravel writes a log entry,
then rerun this script. If you are on the production server, confirm that logging is
enabled and that PHP can write to storage/logs.
EOF2
  exit 2
fi

exec tail -n "$LINES" "$LOG_FILE"
