#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")" && pwd)"
PARENT_DIR="$(dirname "$ROOT_DIR")"
ZIP_PATH="$PARENT_DIR/cars-handoff.zip"

cd "$PARENT_DIR"
rm -f "$ZIP_PATH"

zip -r "$ZIP_PATH" "cars" \
  -x "*/.DS_Store" \
  -x "*/._*" \
  -x "*/__MACOSX/*" \
  -x "*/.git/*"

echo "Created: $ZIP_PATH"
