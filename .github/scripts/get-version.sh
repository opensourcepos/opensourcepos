#!/bin/bash
set -euo pipefail

# Shared version tag generation script for GitHub Actions workflows
# Usage: ./get-version.sh [FORMAT] [SHA_LENGTH]
#
# Formats:
#   docker-tag  - Docker image tag (default)
#   archive     - Archive filename suffix
#   all         - Output all version variables for GITHUB_OUTPUT
#
# Environment variables:
#   GITHUB_REF        - Git ref (e.g., refs/heads/master, refs/pull/123/merge)
#   GITHUB_SHA        - Git commit SHA
#   GITHUB_EVENT_NAME - Event that triggered workflow (push, pull_request, etc.)
#   GITHUB_EVENT_PATH - Path to event JSON (for PR number extraction)
#   GITHUB_OUTPUT     - Path to GITHUB_OUTPUT file (when format=all)

# Ensure we're in a git repository with source files
cd "${GITHUB_WORKSPACE:-.}"

# Get version from App.php
VERSION=$(grep "application_version" app/Config/App.php | sed "s/.*= '\(.*\)';/\1/g")

# Standardize SHA length (default: 7 chars)
SHA_LENGTH="${2:-7}"
SHA="${GITHUB_SHA:0:$SHA_LENGTH}"

# Initialize variables
IMAGE_TAG=""
BRANCH=""

# Detect event type and generate appropriate tag
if [[ "$GITHUB_EVENT_NAME" == "pull_request" || "$GITHUB_EVENT_NAME" == "pull_request_review" ]]; then
  # Extract PR number from event JSON
  if [[ -f "${GITHUB_EVENT_PATH:-}" ]]; then
    PR_NUMBER=$(jq -r '.pull_request.number // .number // empty' < "$GITHUB_EVENT_PATH" 2>/dev/null || true)
    
    if [[ -n "$PR_NUMBER" ]]; then
      # PR-based tag (for PR deployments)
      IMAGE_TAG="pr-${PR_NUMBER}-${SHA}"
      BRANCH="pr-${PR_NUMBER}"
    fi
  fi
  
  # Fallback if we couldn't extract PR number
  if [[ -z "$IMAGE_TAG" ]]; then
    # Try to extract from GITHUB_REF
    PR_NUMBER=$(echo "$GITHUB_REF" | grep -oP 'pull/\K[0-9]+' || true)
    if [[ -n "$PR_NUMBER" ]]; then
      IMAGE_TAG="pr-${PR_NUMBER}-${SHA}"
      BRANCH="pr-${PR_NUMBER}"
    else
      # Last resort: use SHA only
      IMAGE_TAG="${SHA}"
      BRANCH="unknown"
    fi
  fi
else
  # Branch-based tag (for push events)
  BRANCH="${GITHUB_REF#refs/heads/}"
  BRANCH=$(echo "$BRANCH" | sed 's/feature\///' | tr '/' '_')
  
  if [[ "$BRANCH" == "master" ]]; then
    # Master builds: use version as tag
    IMAGE_TAG="${VERSION}"
  else
    # Feature branch builds: version + branch + sha
    IMAGE_TAG="${VERSION}-${BRANCH}-${SHA}"
  fi
fi

# Output format based on first argument
case "${1:-docker-tag}" in
  docker-tag)
    echo "$IMAGE_TAG"
    ;;
  archive)
    echo "${VERSION}.${SHA}"
    ;;
  all)
    {
      echo "version=${VERSION}"
      echo "version-tag=${IMAGE_TAG}"
      echo "short-sha=${SHA}"
      echo "branch=${BRANCH}"
    } >> "$GITHUB_OUTPUT"
    echo "::debug::version=${VERSION}, version-tag=${IMAGE_TAG}, short-sha=${SHA}, branch=${BRANCH}"
    ;;
  *)
    echo "::error::Unknown format: $1" >&2
    exit 1
    ;;
esac