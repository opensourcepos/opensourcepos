# GitHub Actions

This document describes the CI/CD workflows for OSPOS.

## Build and Release Workflow (`.github/workflows/build-release.yml`)

### Build Process
- Setup PHP 8.2 with required extensions
- Setup Node.js 20
- Install composer dependencies
- Install npm dependencies
- Build frontend assets with Gulp

### Docker Images
- Build and push `opensourcepos` Docker image for multiple architectures (linux/amd64, linux/arm64)
- On master: tagged with version and `latest`
- On other branches: tagged with version only
- Pushed to Docker Hub

### Releases
- Create distribution archives (tar.gz, zip)
- Create/update GitHub "unstable" release on master branch only

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

- **Push to master** - Runs build, Docker push (with `latest` tag), and release
- **Push to other branches** - Runs build and Docker push (version tag only)
- **Push tags** - Runs build and Docker push (version tag only)
- **Pull requests** - Runs build only (PHPUnit tests run in parallel via phpunit.yml)

## Existing Workflows

This repository also has these workflows:
- `.github/workflows/main.yml` - PHP linting with PHP-CS-Fixer
- `.github/workflows/phpunit.yml` - PHPUnit tests (runs on all PHP versions 8.1-8.4)
- `.github/workflows/php-linter.yml` - PHP linting

## Testing

PHPUnit tests are run separately via `.github/workflows/phpunit.yml` on every push and pull request, testing against PHP 8.1, 8.2, 8.3, and 8.4.

To test the build workflow:
1. Add the required secrets
2. Push to master or create a PR
3. Monitor the Actions tab in GitHub