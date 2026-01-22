#!/bin/bash
#
# Build script for AI Share Buttons plugin
# Creates a distributable zip file
#

set -e

PLUGIN_SLUG="ai-share-buttons"
VERSION=$(grep "Version:" ai-share-buttons.php | sed 's/.*Version: //' | tr -d ' ')
BUILD_DIR="./build"
DIST_DIR="./dist"

echo "Building ${PLUGIN_SLUG} v${VERSION}..."

# Clean previous builds
rm -rf "${BUILD_DIR}" "${DIST_DIR}"
mkdir -p "${BUILD_DIR}/${PLUGIN_SLUG}" "${DIST_DIR}"

# Copy plugin files (only what's needed to run)
cp ai-share-buttons.php "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp uninstall.php "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp readme.txt "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp README.md "${BUILD_DIR}/${PLUGIN_SLUG}/"

# Copy directories
cp -r includes "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp -r assets "${BUILD_DIR}/${PLUGIN_SLUG}/"

# Create languages directory (empty but needed for i18n)
mkdir -p "${BUILD_DIR}/${PLUGIN_SLUG}/languages"

# Remove any .DS_Store files
find "${BUILD_DIR}" -name '.DS_Store' -delete

# Create zip for manual installation
cd "${BUILD_DIR}"
zip -r "../${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip" "${PLUGIN_SLUG}"
cd ..

# Also create a latest.zip for convenience
cp "${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip" "${DIST_DIR}/${PLUGIN_SLUG}-latest.zip"

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
