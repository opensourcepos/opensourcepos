# Agent Instructions

This document provides guidance for AI agents working on the Open Source Point of Sale (OSPOS) codebase.

## Code Style

- Follow PHP CodeIgniter 4 coding standards
- Run PHP-CS-Fixer before committing: `vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.no-header.php`
- Write PHP 8.2+ compatible code with proper type declarations
- Use PSR-12 naming conventions: `camelCase` for variables and functions, `PascalCase` for classes, `UPPER_CASE` for constants

## Development

- Create a new git worktree for each issue, based on the latest state of `origin/master`
- Commit fixes to the worktree and push to the remote

## Testing

- Run PHPUnit tests: `composer test`
- Tests must pass before submitting changes
- Minimum PHPUnit version: 10.5.16+. Default config: `phpunit.xml.dist` in project root

## Build

- Install dependencies: `composer install && npm install`
- Build assets: `npm run build` or `gulp`

## Conventions

- Controllers go in `app/Controllers/`
- Models go in `app/Models/`
- Views go in `app/Views/`
- Database migrations in `app/Database/Migrations/`
- Plugins go in `app/Plugins/` (see `app/Plugins/README.md` for plugin structure, event hooks, and LICENSE requirements)
- Use CodeIgniter 4 framework patterns and helpers
- Sanitize user input; escape output using `esc()` helper

## Security

- Never commit secrets, credentials, or `.env` files
- Use parameterized queries to prevent SQL injection
- Validate and sanitize all user input
