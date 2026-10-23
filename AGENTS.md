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
- Always import classes, functions, and constants with a `use` statement at the top of the file instead of referencing them inline via qualified or fully-qualified name — this applies to every reference, including global-namespace classes like `ReflectionClass`/`ReflectionProperty`/`Exception` and type-hints in method signatures, not just namespaced application classes. Use `Database::connect()` with `use Config\Database;`, not `\Config\Database::connect()`; use `new ReflectionProperty(...)` with `use ReflectionProperty;`, not `new \ReflectionProperty(...)`; type-hint `ResponseInterface $response` with `use CodeIgniter\HTTP\ResponseInterface;`, not `\CodeIgniter\HTTP\ResponseInterface $response`
- No useless comments or docblocks: if the code speaks for itself, explanatory comments are unnecessary. Do not add comments or docblocks that merely restate what the code already makes clear (e.g. `@param array $items` next to `array $items`, or a comment repeating a method's name) — only comment on non-obvious rationale, constraints, or behavior
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
- **Name test methods as `test{MethodUnderTest}_{Behavior}`.** `PascalCase` the method under test, then an underscore, then a short behavior phrase — `testPostSave_RejectsNegativeTaxPercent`, `testSaveLocale_AcceptsValidReferenceCodeMinMax`. Start the behavior with a verb: `Accepts`/`Requires` for valid or boundary cases, `Rejects` for invalid ones. The name should read as a self-documenting sentence and needs no docblock.
- **Resetting the test database (full migration reset, not just `$refresh`).** When a test class needs the `ospos_tests` schema fully dropped and re-migrated from scratch (e.g. so `ospos_permissions`/`app_config` get freshly seeded), `use Tests\Support\DatabaseBootstrapTrait;` and call `$this->resetDatabaseOnce()` as the first line of `setUp()`, before `parent::setUp()`:
  ```php
  use Tests\Support\DatabaseBootstrapTrait;

  class FooTest extends CIUnitTestCase
  {
      use DatabaseTestTrait;
      use DatabaseBootstrapTrait;

      protected $migrate     = true;
      protected $migrateOnce = true;
      protected $seedOnce    = true;
      protected $refresh     = false;
      protected $namespace   = null;

      protected function setUp(): void
      {
          $this->resetDatabaseOnce();

          parent::setUp();
          // ...
      }
  }
  ```
  `DatabaseBootstrapTrait` (`tests/Support/DatabaseBootstrapTrait.php`) wraps `TestDatabaseBootstrapSeeder::reset()` with a per-class static guard so it only runs once per test class. Do **not** reinvent this with a local `private static bool $doneBootstrap` flag, `setUpBeforeClass()`, or `Config::seeder($this->DBGroup)->call('App\Database\Seeds\TestDatabaseBootstrapSeeder')` followed by a manual `Config::connect($this->DBGroup)->close()`/`resetDataCache()` in the test file — those are the old patterns the trait replaces, and the manual version reintroduces the exact stale-connection bug `TestDatabaseBootstrapSeeder::reset()` already fixes internally. Only add this trait to test classes that actually touch the real test database (`DatabaseTestTrait` users) — pure unit tests that mock everything don't need it.
  If a test class reads `config(OSPOS::class)->settings` for real (not mocking it out), also call `config(OSPOS::class)->update_settings();` right after `parent::setUp();` to force a fresh settings read post-reset. See `tests/Controllers/ReportsControllerTest.php` for that shape.
- **Mocking OSPOS config settings.** When a test needs `config(OSPOS::class)->settings` to return fixed values instead of reading from the database, `use Tests\Support\OsposConfigFixtureTrait;` and call `$this->injectOsposSettings([...])` instead of manually constructing `new OSPOS()` + `Factories::injectMock('config', OSPOS::class, $ospos)`:
  ```php
  use Tests\Support\OsposConfigFixtureTrait;

  class FooTest extends CIUnitTestCase
  {
      use OsposConfigFixtureTrait;

      protected function setUp(): void
      {
          parent::setUp();

          $this->injectOsposSettings([
              'number_locale' => 'en_US',
              'currency_decimals' => 2,
          ]);
      }
  }
  ```
  `OSPOS::getDefaultSettings()` is `private` and only covers a handful of keys, so it is not a usable base to merge onto — each test must supply the exact settings it depends on. Use this trait regardless of whether the test also uses `DatabaseBootstrapTrait`.

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
- When translating a string containing placeholders (e.g. `{filePath}`, `{reason}`) or literal filenames/keys (e.g. `throttle.key`, `.env`), keep that placeholder or filename text unchanged and untranslated — move it to whatever position is grammatically correct in the translated sentence
- Plugin language files (`app/Plugins/*/Language/`) follow the same localization rules as `app/Language/`
- Use `'` to encapsulate key and string values. If the value contains `'` then it should be escaped as `\'`
- Align the `=>` of a newly inserted key with the `=>` column already used by the rest of the file, if that file pads keys to a fixed column (not all do — some files have no padding at all). If the new key is longer than the widest existing key and would push the alignment column further right, reformat the whole file to the new wider column rather than leaving only the new line at a different width

## Security

- Never commit secrets, credentials, or `.env` files
- Use parameterized queries to prevent SQL injection
- Validate and sanitize all user input
- Never reference security advisory IDs (CVE, GHSA, etc.) in code, comments, commit messages, docblocks, documentation, or URLs — treat them the same as secrets. They act as a roadmap for attackers researching the exact exploit a fix addresses.
