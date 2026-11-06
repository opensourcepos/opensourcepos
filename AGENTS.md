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

- **Key Parity**: All catalogs must maintain the exact same keys as `app/Language/en/` (no extra or missing keys). Adding a key requires adding it to `en/` with the English source text, and to all other `app/Language/*/` files (using `''` if untranslated, including `en-GB`).
- **Alphabetical Ordering**: All catalogs must be sorted alphabetically by key, **except `Calendar.php`** (ordered chronologically by day and month sequences).
- **Direct Fallback to `en`**: Non-English files use `''` for untranslated strings to trigger dynamic fallback to `en` (`nl-NL > en`, `nl-BE > en`, `hr > en`, `en-GB > en`).
- **British English (`en-GB`)**: Only include strings differing in UK spelling/vocabulary (*Cheque*, *Centre*, *Post Code*). Keep identical strings as `''` to inherit updates from `en`.
- **Locale Naming (BCP 47)**:
  - **General languages**: Use bare ISO language codes (`hr` not `hr-HR`, `fr`, `it`, `da`, `ckb`). Do not add redundant country tags when a language has no regional distinction.
  - **Regional / dialect variants**: Use `{lang}-{REGION}` when targeting a specific regional dialect (`nl-NL` & `nl-BE`, `de-DE` & `de-CH`, `es-ES` & `es-MX`, `en-GB`, `pt-BR`). Chinese uses script subtags (`zh-Hans`, `zh-Hant`).
- **Locale Registration**: Register new locales in both `app/Config/App.php` (`$supportedLocales`) and `app/Helpers/locale_helper.php` (`get_languages()`, format: `'code:name' => 'English Name'`).
- **Calendar Convention (`may` vs `mayl`)**: `may` is the 3-letter abbreviation; `mayl` is the full month name (*May* long). Datepickers and full-month arrays must reference `lang('Calendar.mayl')`, while short-month arrays reference `lang('Calendar.may')`.
- **Translation Integrity**:
  - When deprecating or renaming keys, always migrate existing non-English human translations to the new key/module. If a legacy translation references the wrong entity (e.g. 'item' in Attributes), reset it to `''`.
  - When an English string in `app/Language/en/` is changed or rewritten, reset the corresponding non-English strings in `app/Language/*/` to `''` so they dynamically fall back to the updated English text and signal to translators that a new translation is required.
- **Placeholders & ICU Formatting**:
  - Interpolate user-facing strings with `lang()` and **named** placeholders (e.g. `{company}`) rather than string concatenation or positional placeholders (`{0}`).
  - Translations must preserve all placeholders (`{0}`, `{name}`) and literal keys/filenames untranslated (reordering within the sentence to fit natural grammar is encouraged); never omit them.
  - Placeholder-bearing translations use ICU `MessageFormatter`: double literal braces (`{{`/`}}`) and apostrophes (`''`) to avoid syntax warnings.
- **Punctuation & Wrappers**:
  - No leading or trailing whitespace in strings; never rely on trailing spaces for concatenation.
  - If an English string is enclosed in brackets `[...]` or parentheses `(...)`, translations must preserve that enclosure (or localized fullwidth equivalents like `【...】` / `（...）`).
  - Watch for duplicate punctuation typos (e.g. `::` or `..` instead of ellipsis `...`).
  - Indicator flags (`taxed_ind`, `nontaxed_ind`) and report badges (`Reports.code_*`) must be kept compact (single characters or short abbreviations, never full sentences or punctuation placeholders like `'-'`, `'.'`). Untranslated indicators must remain `''` to inherit from `en`.
- **Language File Syntax & Formatting**:
  - Only `app/Language/en/` and `app/Language/en-GB/` may contain English prose. Universal brand names and acronyms (`ID`, `SMS`, `POS`, `Mailchimp`, `CSV`) are acceptable in non-English files, but never copy English sentences or phrases; leave as `''` to inherit from `en`.
  - `Datepicker.weekstart` is a numeric calendar setting (`0` = Sunday, `1` = Monday, `6` = Saturday), not English text — never wipe it to `''`.
  - Enclose keys and strings in `'` (escape internal quotes as `\'`). Array keys must be unique.
  - Align `=>` to match the file's standard column padding (e.g. column 40). Avoid unnecessarily re-padding entire catalogs for a single long key.
  - Plugin language files (`app/Plugins/*/Language/`) follow the same rules.
- **Validation**: Run `vendor/bin/phpunit tests/LanguageFilesTest.php` to verify syntax, key parity, sorting, duplicate rejection, and locale registration.

## Security

- Never commit secrets, credentials, or `.env` files
- Use parameterized queries to prevent SQL injection
- Validate and sanitize all user input
- Never reference security advisory IDs (CVE, GHSA, etc.) in code, comments, commit messages, docblocks, documentation, or URLs — treat them the same as secrets. They act as a roadmap for attackers researching the exact exploit a fix addresses.
