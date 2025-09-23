#!/bin/bash

# Shared Composer bootstrap helper used by deployment and setup scripts.
#
# Scripts sourcing this library should define `composer_log` and
# `composer_warn` functions for messaging. If they are not defined we fall back
# to basic echo logging.

if ! declare -F composer_log >/dev/null 2>&1; then
  composer_log() {
    echo "$*"
  }
fi

if ! declare -F composer_warn >/dev/null 2>&1; then
  composer_warn() {
    echo "$*" >&2
  }
fi

ensure_composer() {
  if command -v composer >/dev/null 2>&1; then
    COMPOSER_BIN=(composer)
    return 0
  fi

  if [[ -f composer.phar ]]; then
    if command -v php >/dev/null 2>&1; then
      COMPOSER_BIN=(php composer.phar)
      return 0
    fi
    composer_warn "PHP is required to run composer.phar but was not found."
    return 1
  fi

  if ! command -v php >/dev/null 2>&1; then
    composer_warn "PHP is required to bootstrap Composer but was not found."
    return 1
  fi

  composer_log "Composer executable not found; downloading local composer.phar"

  local installer
  installer="$(mktemp)"

  local signature=""
  if command -v curl >/dev/null 2>&1; then
    if ! signature="$(curl -fsSL https://composer.github.io/installer.sig)"; then
      composer_warn "Failed to download Composer installer signature."
      rm -f "$installer"
      return 1
    fi
    if ! curl -fsSL https://getcomposer.org/installer -o "$installer"; then
      composer_warn "Failed to download Composer installer script."
      rm -f "$installer"
      return 1
    fi
  elif command -v wget >/dev/null 2>&1; then
    if ! signature="$(wget -qO- https://composer.github.io/installer.sig)"; then
      composer_warn "Failed to download Composer installer signature."
      rm -f "$installer"
      return 1
    fi
    if ! wget -qO "$installer" https://getcomposer.org/installer; then
      composer_warn "Failed to download Composer installer script."
      rm -f "$installer"
      return 1
    fi
  else
    if ! signature="$(php -r "echo trim(file_get_contents('https://composer.github.io/installer.sig'));" )"; then
      composer_warn "Failed to download Composer installer signature."
      rm -f "$installer"
      return 1
    fi
    if ! php -r "copy('https://getcomposer.org/installer', '$installer');"; then
      composer_warn "Failed to download Composer installer script."
      rm -f "$installer"
      return 1
    fi
  fi

  if [[ -z "$signature" ]]; then
    composer_warn "Composer installer signature was empty."
    rm -f "$installer"
    return 1
  fi

  local actual
  if ! actual="$(php -r "echo hash_file('sha384', '$installer');")"; then
    composer_warn "Unable to calculate Composer installer checksum."
    rm -f "$installer"
    return 1
  fi

  if [[ "$actual" != "$signature" ]]; then
    composer_warn "Composer installer signature mismatch; aborting."
    rm -f "$installer"
    return 1
  fi

  if ! php "$installer" --install-dir=. --filename=composer.phar >/dev/null; then
    composer_warn "Composer installer failed."
    rm -f "$installer"
    return 1
  fi

  rm -f "$installer"
  COMPOSER_BIN=(php composer.phar)
  return 0
}
