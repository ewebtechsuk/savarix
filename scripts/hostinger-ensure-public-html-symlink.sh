#!/usr/bin/env bash
set -euo pipefail

info() {
  printf "[hostinger-public-html] %s\n" "$1"
}

BASE_DIR="${BASE_DIR:-$(pwd)}"
APP_DIR="$BASE_DIR"
DOMAINS_ROOT="$(dirname "$BASE_DIR")"
PUBLIC_HTML="$DOMAINS_ROOT/public_html"
TARGET_PUBLIC="$APP_DIR/public"

if [[ ! -d "$TARGET_PUBLIC" ]]; then
  info "Target public directory does not exist: $TARGET_PUBLIC"
  exit 1
fi

if [[ -L "$PUBLIC_HTML" ]]; then
  current_target="$(readlink "$PUBLIC_HTML")"
  if [[ "$current_target" == "$TARGET_PUBLIC" ]]; then
    info "public_html already points to $TARGET_PUBLIC; nothing to change."
    exit 0
  fi

  info "public_html is a symlink to $current_target; replacing with $TARGET_PUBLIC."
  rm -rf "$PUBLIC_HTML"
elif [[ -e "$PUBLIC_HTML" ]]; then
  info "public_html exists and is not a symlink; removing it before recreating."
  rm -rf "$PUBLIC_HTML"
else
  info "public_html does not exist; creating symlink."
fi

ln -s "$TARGET_PUBLIC" "$PUBLIC_HTML"
info "public_html now points to $TARGET_PUBLIC"
