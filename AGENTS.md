# Agent Instructions

This document provides guidance for AI agents working on the Open Source Point of Sale (OSPOS) codebase.

## Code Style

- **PSR-12** enforced via PHP-CS-Fixer (config: `.php-cs-fixer.no-header.php`)
- Follow PHP CodeIgniter 4 coding standards
- `camelCase` for variables and methods; `PascalCase` for classes; `UPPER_CASE` for constants
- When editing existing code containing non-PSR-compliant local variable names, refactor those variable names to `camelCase` as part of the edit
- All newly written code (variables, classes, functions) must use PSR-compliant naming, regardless of surrounding code style
- PHP 8.2+ features acceptable (named arguments, enums, readonly properties)
- Write PHP 8.2+ compatible code with proper type declarations
- Always import classes, functions, and constants with a `use` statement at the top of the file instead of referencing them inline via fully-qualified name (e.g. `use Config\Database;` then `Database::connect()`, not `\Config\Database::connect()`)
- Do not add comments or docblocks that merely restate what the code already makes clear — only comment on non-obvious rationale, constraints, or behavior
- Views in `app/Views/errors/html/` are excluded from the fixer
- Run fixer before committing: `vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.no-header.php`
- **JavaScript**: use `const` for variables that are never reassigned, `let` for variables that are. Never use `var`.

## Development

- Create a new git worktree for each issue, based on the latest state of `origin/master`
- Commit fixes to the worktree and push to the remote

## Testing

- Run PHPUnit tests: `composer test`
- Tests must pass before submitting changes
- **One test file per class under test.** A controller, model, library, or helper gets exactly one test file covering all of its behavior — `Item_kits.php` → `tests/Controllers/Item_kitsTest.php` (or `Item_kitsControllerTest.php`, matching this codebase's existing `*ControllerTest.php` suffix for controllers), `Sale_lib.php` → `tests/Libraries/Sale_libTest.php`, etc. Do not create feature- or endpoint-scoped test files alongside a class's main test file (e.g. no `Item_kitsBarcodeTest.php` next to `Item_kitsControllerTest.php`) — add the new test methods to the existing file for that class instead. If no test file exists yet for the class, create the one canonical file rather than a narrowly-scoped one.

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
- Non-English files must use an empty string (`''`) as the value when no translation is provided — CodeIgniter automatically falls back to the default (`en`) language. This applies only when a translation genuinely isn't available yet.
- **When explicitly asked to translate a phrase for a non-English language file, always provide the actual translation** — never leave the value as an empty string, and never leave source English text in a non-English language file
- Never copy English text from a neighboring key as a value for a non-English language file, even if that neighboring key is already untranslated — evaluate each key independently
- Only `app/Language/en/` and `app/Language/en-GB/` should contain English strings
- Plugin language files (`app/Plugins/*/Language/`) follow the same localization rules as `app/Language/`
- Use `'` to encapsulate key and string values. If the value contains `'` then it should be escaped as `\'`

## Security

- Never commit secrets, credentials, or `.env` files
- Use parameterized queries to prevent SQL injection
- Validate and sanitize all user input
