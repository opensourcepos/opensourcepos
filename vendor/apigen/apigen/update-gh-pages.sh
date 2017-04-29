#!/usr/bin/env sh

# Variables
VENDOR=${VENDOR:-"deizel"}
VERSION=${VERSION:-"0.1.4"}
PACKAGE_NAME=${PACKAGE_NAME:-"ApiGen/ApiGen"}
TARGET_NAME=${TARGET_NAME:-"ApiGen/ApiGen.github.io"}

# Publish manifest
curl -O -L "https://github.com/${VENDOR}/manifest-publisher/releases/download/${VERSION}/manifest.phar"
php manifest.phar publish:gh-pages "${PACKAGE_NAME}" "${TARGET_NAME}"
