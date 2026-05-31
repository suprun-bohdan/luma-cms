#!/usr/bin/env bash
# Ensure Laravel writable paths exist and are world-writable for shared-hosting unzip (BrainyCP, cPanel, etc.).
set -euo pipefail

TARGET="${1:?Usage: ensure-writable-paths.sh /path/to/staging-or-site-root}"

API="$TARGET/apps/api"

if [[ ! -d "$API" ]]; then
  echo "ERROR: apps/api not found under $TARGET" >&2
  exit 1
fi

mkdir -p \
  "$API/storage/app/public" \
  "$API/storage/app/private" \
  "$API/storage/framework/cache/data" \
  "$API/storage/framework/sessions" \
  "$API/storage/framework/views" \
  "$API/storage/framework/testing" \
  "$API/storage/logs" \
  "$API/bootstrap/cache"

# Drop dev artifacts from local builds — keep skeleton only.
find "$API/storage/framework/views" -type f -name '*.php' -delete 2>/dev/null || true
find "$API/storage/logs" -type f -name '*.log' -delete 2>/dev/null || true
rm -rf "$API/storage/app/public/media/"* 2>/dev/null || true
mkdir -p "$API/storage/app/public/media"

# 0777 survives unzip on most Linux hosts; PHP-FPM user often differs from FTP owner.
chmod -R 0777 "$API/storage" "$API/bootstrap/cache"

echo "Writable paths prepared under $API (storage, bootstrap/cache → mode 0777)"
