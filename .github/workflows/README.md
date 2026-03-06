# GitHub Actions Migration

This document describes the migration from Travis CI to GitHub Actions.

## What was migrated

The following Travis CI functionality has been converted to GitHub Actions:

### Build and Test Workflow (`.github/workflows/build-release.yml`)

1. **Build Process**
   - Setup PHP 8.2 with required extensions
   - Setup Node.js 20
   - Install composer dependencies
   - Install npm dependencies
   - Build frontend assets with Gulp

2. **Testing**
   - Run PHPUnit tests with MariaDB container
   - Run Docker container tests

3. **Docker Images**
   - Build `ospos:latest` Docker image
   - Build `ospos_test:latest` Docker image
   - Push to Docker Hub on master branch

4. **Releases**
   - Create distribution archives (tar.gz, zip)
   - Create/update GitHub "unstable" release on master

## Required Secrets

To use this workflow, you need to add the following secrets to your repository:

1. **DOCKER_USERNAME** - Docker Hub username for pushing images
2. **DOCKER_PASSWORD** - Docker Hub password/token for pushing images

### How to add secrets

1. Go to your repository on GitHub
2. Click **Settings** → **Secrets and variables** → **Actions**
3. Click **New repository secret**
4. Add `DOCKER_USERNAME` and `DOCKER_PASSWORD`

The `GITHUB_TOKEN` is automatically provided by GitHub Actions.

## Workflow Triggers

- **Push to master** - Runs full build, test, Docker push, and release
- **Push tags** - Runs build and test
- **Pull requests** - Runs build and test only

## Differences from Travis CI

1. **No SQL Docker image** - The Travis build created a `jekkos/opensourcepos:sql-$TAG` image. This workflow focuses on the main application image. If needed, a separate job can be added.

2. **Branch filtering** - GitHub Actions uses workflow triggers instead of Travis's `branches.except`

3. **Concurrency** - Added concurrency group to cancel in-progress runs on the same branch

## Existing Workflows

This repository also has these workflows:
- `.github/workflows/main.yml` - PHP linting with PHP-CS-Fixer
- `.github/workflows/phpunit.yml` - PHPUnit tests (already existed)
- `.github/workflows/php-linter.yml` - PHP linting
- `.github/workflows/codeql-analysis.yml` - Security analysis

## Testing

To test the workflow:
1. Add the required secrets
2. Push to master or create a PR
3. Monitor the Actions tab in GitHub