# Agent Instructions

This document provides guidance for AI agents working on the Open Source Point of Sale (OSPOS) codebase.

## Code Style

- **PSR-12** enforced via PHP-CS-Fixer (config: `.php-cs-fixer.no-header.php`)
- Follow PHP CodeIgniter 4 coding standards
- `camelCase` for variables and methods; `PascalCase` for classes; `UPPER_CASE` for constants
- PHP 8.2+ features acceptable (named arguments, enums, readonly properties)
- Write PHP 8.2+ compatible code with proper type declarations
- Views in `app/Views/errors/html/` are excluded from the fixer
- Run fixer before committing: `vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.no-header.php`
- **JavaScript**: use `const` for variables that are never reassigned, `let` for variables that are. Never use `var`.

## Development

- Create a new git worktree for each issue, based on the latest state of `origin/master`
- Commit fixes to the worktree and push to the remote

## Testing

- Run PHPUnit tests: `composer test`
- Tests must pass before submitting changes

## Build

- Install dependencies: `composer install && npm install`
- Build assets: `npm run build` or `gulp`

## Conventions

- Controllers go in `app/Controllers/`
- Models go in `app/Models/`
- Views go in `app/Views/`
- Database migrations in `app/Database/Migrations/`
- Use CodeIgniter 4 framework patterns and helpers
- Sanitize user input; escape output using `esc()` helper

## Localization

- When adding new keys to language files, add the key to all `app/Language/*/` variants
- **New keys must be inserted in alphabetical order** within the language array
- Non-English files must use an empty string (`''`) as the value when no translation is provided — CodeIgniter automatically falls back to the default (`en`) language
- Only `app/Language/en/` and `app/Language/en-GB/` should contain English strings
- Plugin language files (`app/Plugins/*/Language/`) follow the same localization rules as `app/Language/`
- Use `'` to encapsulate key and string values. If the value contains `'` then it should be escaped as `\'`

## Security

- Never commit secrets, credentials, or `.env` files
- Use parameterized queries to prevent SQL injection
- Validate and sanitize all user input
