# GitHub Actions

This document describes the CI/CD workflows for OSPOS.

## Build and Release Workflow (`.github/workflows/build-release.yml`)

### Build Process
- Setup PHP 8.2 with required extensions
- Setup Node.js 20
- Install composer dependencies
- Install npm dependencies
- Build frontend assets with Gulp

### Testing
- Run PHPUnit tests with MariaDB started via Docker

### Docker Images
- Build and push `opensourcepos` Docker image for multiple architectures (linux/amd64, linux/arm64)
- Image tagged with version and `latest`, pushed to Docker Hub on master branch

### Releases
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

## Existing Workflows

This repository also has these workflows:
- `.github/workflows/main.yml` - PHP linting with PHP-CS-Fixer
- `.github/workflows/phpunit.yml` - PHPUnit tests
- `.github/workflows/php-linter.yml` - PHP linting

## Testing

To test the workflow:
1. Add the required secrets
2. Push to master or create a PR
3. Monitor the Actions tab in GitHub