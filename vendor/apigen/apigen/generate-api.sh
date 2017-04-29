#!/usr/bin/env bash

# Build settings
REPOSITORY=${REPOSITORY:-"https://${GH_TOKEN}@github.com/ApiGen/api.git"}
BRANCH=${BRANCH:-"gh-pages"}
BUILD_DIR=${BUILD_DIR:-"/tmp/generate-api"}

# Git identity
GIT_AUTHOR_NAME=${GIT_AUTHOR_NAME:-"Travis"}
GIT_AUTHOR_EMAIL=${GIT_AUTHOR_EMAIL:-"travis@travis-ci.org"}

# Generate API
git clone "${REPOSITORY}" "${BUILD_DIR}" --branch "${BRANCH}" --depth 1
yes | bin/apigen generate -s src -d "${BUILD_DIR}"

# Commit & push
cd "${BUILD_DIR}" || exit 1
git add .
git commit -m "Generate API"
git push origin "${BRANCH}"
