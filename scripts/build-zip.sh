#!/usr/bin/env bash
# Builds the plugin zip WordPress installs: sitepulse-<version>.zip, with the plugin's files under a
# sitepulse/ folder and nothing from development (tests, tooling, caches, editor files).
#
#   composer build-zip              writes dist/sitepulse-<version>.zip
#   composer build-zip -- <folder>  writes it into that folder instead
#
# The version comes from SITEPULSE_VERSION in loader.php (raise it with composer bump-version first).
# Needs the zip command; with php on the PATH, every PHP file in the zip is also syntax-checked.
set -euo pipefail

PLUGIN="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUT="${1:-$PLUGIN/dist}"
SHIPPED=(loader.php uninstall.php readme.txt LICENSE class inc templates assets languages)

VERSION=$(sed -n "s/^define( 'SITEPULSE_VERSION', *'\([^']*\)' );/\1/p" "$PLUGIN/loader.php")
[ -n "$VERSION" ] || { echo "error: SITEPULSE_VERSION was not found in loader.php" >&2; exit 1; }
HEADER=$(sed -n 's/^ \* Version: *\([^ ]*\)$/\1/p' "$PLUGIN/loader.php")
STABLE=$(sed -n 's/^Stable tag: *\([^ ]*\)$/\1/p' "$PLUGIN/readme.txt")
if [ "$HEADER" != "$VERSION" ] || [ "$STABLE" != "$VERSION" ]; then
  echo "error: versions differ (header $HEADER, SITEPULSE_VERSION $VERSION, Stable tag $STABLE); run composer bump-version" >&2
  exit 1
fi
command -v zip >/dev/null || { echo "error: the zip command is required" >&2; exit 1; }

STAGE=$(mktemp -d)
trap 'rm -rf "$STAGE"' EXIT
mkdir -p "$STAGE/sitepulse" "$OUT"
for item in "${SHIPPED[@]}"; do
  [ -e "$PLUGIN/$item" ] || { echo "error: $item is missing" >&2; exit 1; }
  cp -R "$PLUGIN/$item" "$STAGE/sitepulse/"
done
find "$STAGE/sitepulse" \( -name '.DS_Store' -o -name 'Thumbs.db' -o -name '*.map' -o -name '.gitkeep' \) -delete

if command -v php >/dev/null; then
  while IFS= read -r -d '' file; do
    php -l "$file" >/dev/null || { echo "error: $file does not parse" >&2; exit 1; }
  done < <(find "$STAGE/sitepulse" -name '*.php' -print0)
fi

ZIP="$OUT/sitepulse-$VERSION.zip"
rm -f "$ZIP"
(cd "$STAGE" && zip -qr -X "$ZIP" sitepulse)
echo "Built $ZIP: version $VERSION, $(unzip -l "$ZIP" | tail -1 | awk '{print $2}') files, $(du -h "$ZIP" | cut -f1)."
