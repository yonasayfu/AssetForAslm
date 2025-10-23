#!/usr/bin/env bash
set -euo pipefail

HOST="${HOST:-127.0.0.1}"
SMTP_PORT="${SMTP_PORT:-1025}"
HTTP_PORT="${HTTP_PORT:-8025}"
MAILPIT_BIN="${MAILPIT_BIN:-mailpit}"

if [[ ! -x "$MAILPIT_BIN" ]]; then
  if command -v mailpit >/dev/null 2>&1; then
    MAILPIT_BIN="$(command -v mailpit)"
  elif [[ -x "$MAILPIT_BIN" ]]; then
    :
  elif [[ -x "./mailpit" ]]; then
    MAILPIT_BIN="./mailpit"
  elif [[ -x "./mailpit.exe" ]]; then
    MAILPIT_BIN="./mailpit.exe"
  else
    echo "Mailpit executable not found. Set MAILPIT_BIN to the binary path or add it to PATH." >&2
    exit 1
  fi
fi

echo "Starting Mailpit from $MAILPIT_BIN"
echo "SMTP endpoint: ${HOST}:${SMTP_PORT}"
echo "HTTP endpoint: ${HOST}:${HTTP_PORT}"

exec "$MAILPIT_BIN" \
  --smtp "${HOST}:${SMTP_PORT}" \
  --listen "${HOST}:${HTTP_PORT}" \
  --label assetmanagement
