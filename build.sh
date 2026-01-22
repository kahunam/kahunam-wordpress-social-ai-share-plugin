#!/bin/bash
#
# Build script for AI Share Buttons plugin
# Creates a distributable zip file
#

set -e

PLUGIN_SLUG="ai-share-buttons"
VERSION=$(grep "Version:" ai-share-buttons.php | sed 's/.*Version: //' | tr -d ' ')
STAGING_DIR="./.build-staging"
DIST_DIR="./dist"

echo "Building ${PLUGIN_SLUG} v${VERSION}..."

# Build the Gutenberg block
echo "  Compiling block..."
if [ -f "package.json" ]; then
    npm install --silent
    npm run build --silent
fi

# Clean previous staging
rm -rf "${STAGING_DIR}" "${DIST_DIR}"
mkdir -p "${STAGING_DIR}/${PLUGIN_SLUG}" "${DIST_DIR}"

# Copy plugin files (only what's needed to run)
cp ai-share-buttons.php "${STAGING_DIR}/${PLUGIN_SLUG}/"
cp uninstall.php "${STAGING_DIR}/${PLUGIN_SLUG}/"
cp readme.txt "${STAGING_DIR}/${PLUGIN_SLUG}/"
cp README.md "${STAGING_DIR}/${PLUGIN_SLUG}/"

# Copy directories
cp -r includes "${STAGING_DIR}/${PLUGIN_SLUG}/"
cp -r assets "${STAGING_DIR}/${PLUGIN_SLUG}/"

# Copy built block if exists
if [ -d "build/blocks" ]; then
    mkdir -p "${STAGING_DIR}/${PLUGIN_SLUG}/build"
    cp -r build/blocks "${STAGING_DIR}/${PLUGIN_SLUG}/build/"
fi

# Create languages directory (empty but needed for i18n)
mkdir -p "${STAGING_DIR}/${PLUGIN_SLUG}/languages"

# Remove any .DS_Store files
find "${STAGING_DIR}" -name '.DS_Store' -delete

# Create zip for manual installation
cd "${STAGING_DIR}"
zip -rq "../${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip" "${PLUGIN_SLUG}"
cd ..

# Also create a latest.zip for convenience
cp "${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip" "${DIST_DIR}/${PLUGIN_SLUG}-latest.zip"

# Clean up staging
rm -rf "${STAGING_DIR}"

echo ""
echo "Build complete!"
echo ""
echo "Distribution files:"
echo "  ${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip  (versioned)"
echo "  ${DIST_DIR}/${PLUGIN_SLUG}-latest.zip      (latest)"
echo ""
echo "To install:"
echo "  1. Go to WordPress Admin → Plugins → Add New → Upload Plugin"
echo "  2. Choose the zip file and click Install Now"
echo ""
