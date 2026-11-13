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
- Run fixer before committing: `vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.no-header.php`. Because `.php-cs-fixer.no-header.php` enables risky `strict_comparison` (`==`/`!=` → `===`/`!==`), always inspect `git diff` afterward and cast database, `app_config`, and POST values explicitly (`(int)`, `(float)`, `(bool)`) wherever strict comparison would mismatch runtime string/bool types
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

### Routing & Controllers (`autoRoutesImproved`)

- **HTTP verb prefixes are mandatory**: With CI4 `autoRoutesImproved` enabled, all routable controller actions must be prefixed with their HTTP verb (`getIndex()`, `getView()`, `postSave()`, `postDelete()`). Unprefixed public methods return 404 unless explicitly mapped in `app/Config/Routes.php`.
- **Internal controller helpers must not be `public`**: Helper methods on controllers must be `private` or `protected`. Never declare an internal helper as `public function get*()`, as `autoRoutesImproved` will expose it as a direct `GET` endpoint that returns raw values or crashes on missing arguments.
- **Filter configuration syntax**: In `app/Config/Filters.php`, always specify `except` and `before`/`after` URI patterns as arrays (`'except' => ['login', 'migrate']`), never as pipe-delimited strings (`'login|migrate'`).
- **Audit all callers when adding allowlists or changing route verbs**: Before restricting a `$type` / `$mode` parameter to an allowlist (e.g. PDF/email templates, report types), grep views and JS callers so every legitimate caller value is included.

### Database Queries, Transactions & Migrations

- **Never hardcode table prefixes**: `$this->db->table('items')` and migration helpers (`dropColumnIfExists()`, `indexExists()`, `$this->forge`) automatically prepend `$this->db->DBPrefix` (`ospos_`). Passing `'ospos_items'` produces `ospos_ospos_items`.
- **Balanced transactions**: Never mix `$this->db->transStart()` / `$this->db->transComplete()` with manual `$this->db->transRollback()` or early `return` statements before `transComplete()`. Returning before `transComplete()` leaves `transDepth` incremented on the shared DB connection and corrupts subsequent transactions in the request.
- **MySQL `ONLY_FULL_GROUP_BY` compliance & fan-out prevention**: When a query joins multiple one-to-many detail or payment tables (`sales_items`, `sales_payments`, `receivings_items`), always pre-aggregate each such table by its join key in a subquery before joining it to the main query and compute sums from the pre-aggregated results so rows from separate detail tables cannot multiply each other. Direct joins with `GROUP BY` are acceptable only when at most one one-to-many table is joined — and in every `GROUP BY` query, include all non-aggregated `SELECT` columns in `GROUP BY` (or wrap functionally dependent columns in `MAX()`).
- **Guard `getRow()` / `getRowArray()` and single-row endpoint lookups**: CI4's `getRow()` and `getRowArray()` return `null` (not `false` or `[]`) when no row matches. Always use nullsafe access (`?->`), null coalescing (`?? []`), or explicit `null` checks, ensure methods returning a dummy default object on empty IDs also return that fallback when an unfound ID is queried, and return HTTP `404` with `[]` JSON (`$this->response->setStatusCode(404)->setJSON([])`) in row-refresh controller endpoints (`getGet_detailed_*_row`) before indexing into empty model results.
- **Decide insert vs. update by primary key ID, not mutable fields**: In `save_value($data, $id)` methods, branch on `$id === NEW_ENTRY || !$this->existsById($id)` using the primary key — never on whether a user-editable unique field (`tax_code`, `item_number`, etc.) already exists, which inserts a duplicate when that field is edited. Always instantiate a fresh `$this->db->table(...)` builder for the subsequent `insert()` / `update()`.

### Locale-Aware Number Parsing & Financial Logic

- **Never use `FILTER_SANITIZE_NUMBER_FLOAT` before `parse_decimals()` / `parse_quantity()` / `parse_tax()`**: `FILTER_SANITIZE_NUMBER_FLOAT` (without `FILTER_FLAG_ALLOW_FRACTION`) strips commas `,`. In locales where `,` is the decimal separator (`de_DE`, `es_ES`, `fr_FR`, `it_IT`, `nl_NL`), stripping `,` before `NumberFormatter::parse()` multiplies values like `12,34` by 100 (`1234`). Pass the raw string directly to `parse_decimals()` / `parse_quantity()` and validate the parsed float/false result.
- **Parse once**: Never call `parse_decimals()` or `parse_quantity()` twice on the same value; once parsed to a canonical float/numeric string, re-parsing under a non-US locale can misinterpret `.` as a thousands separator.
- **Never use display formatters (`to_decimals()`, `to_quantity_decimals()`, `to_currency*()`) for DB storage or `bcmath`**: `to_*` helpers format numbers for UI display using `NumberFormatter` with locale decimal commas (`12,50`) and grouping separators (`1,234.56`), which corrupt `DECIMAL` database writes and fail `bcmath` validation. For database persistence and internal calculations, always use canonical `bcmath` strings or `number_format($value, $decimals, '.', '')`.
- **Guard division by zero**: In PHP 8+, both `/` and `bcdiv()` throw `DivisionByZeroError` when the divisor is `0` or `'0'`. Always check `if ((float) $denominator !== 0.0)` or `bccomp($denominator, '0', $scale) !== 0` — passing an explicit `$scale` large enough to cover `$denominator`'s fractional precision so valid sub-unit divisors (e.g. `'0.25'` when global `bcscale()` is `0`) are not truncated to zero — before dividing in margin, markup, discount, and tax calculations.
- **Centralized rounding**: Delegate tax and currency rounding to `Rounding_mode::round_number()` rather than ad-hoc `round()` or string-padding math.
- **Suspended vs. completed sale side effects**: Never apply irreversible financial mutations (deducting giftcard balances, awarding/deducting customer reward points or packages) when saving a `SUSPENDED` / quote / work-order sale — apply them only when `$saleStatus === COMPLETED`.

### PHP 8.2+ Type Safety & Operator Pitfalls

- **Never use word operators `or` / `and` in expressions**: `or` and `and` have lower precedence than `=` and `return`. `return $a or $b;` evaluates as `(return $a) or $b;`, making `$b` dead code. Always use `||` and `&&`.
- **Beware of `!` precedence in comparisons**: `!$id == NEW_ENTRY` evaluates `(!$id)` as a `bool` before comparing against `-1`. Always write `$id === NEW_ENTRY` or `$id != NEW_ENTRY`.
- **Never test optional string/numeric inputs with `!= null`**: In PHP 8+, `'0' != null` evaluates `(bool) '0' != (bool) null` (`false`), silently ignoring valid `'0'` inputs (e.g. switching a discount or toggle from `1` back to `'0'`). Always check `$value !== null && $value !== ''`.
- **Cast database, `app_config`, and POST values before `===` / `!==`**:
  - MySQL `mysqli` returns `INT`, `TINYINT`, and `DECIMAL` columns as strings (`'0'`, `'1'`, `'0.000'`), `app_config` values are strings (`'0'`, `'1'`), and `$_POST` values are strings, whereas domain constants (`PERCENT`, `FIXED`, `ITEM_TEMP`, `ITEM_AMOUNT_ENTRY`, `PRICE_ALL`, `PRINT_ALL`, `HAS_STOCK`, `COMPLETED`, `SUSPENDED`, `CANCELED`) are `int` and helpers like `is_print_after_sale()` return `bool`.
  - Normalize integer flags at write time when storing into session carts/arrays (e.g. `(int) $discountType`), and cast operands explicitly before strict comparisons in views and controllers: `(int) $config['flag'] === 1`, `(int) $item->item_type === ITEM_TEMP`, `(float) $item['receiving_quantity'] !== 0.0`, `(bool) $print_after_sale`.
- **Match parameter and return types to runtime data**:
  - Item and receiving quantities can be fractional (`decimal(15,3)`); type-hint quantities as `float|int|string` or `float`, never `int`.
  - Check `date_create_from_format()` for `false` before calling `->format()`, and verify `count($headers) === count($row)` before calling `array_combine()` (which throws `ValueError` in PHP 8+ on mismatched counts).

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

### Authentication, Authorization & Access Control

- **Derive identity exclusively from the session**: Never trust client-supplied `person_id`, `employee_id`, or `username` fields in POST/GET data to identify the acting user (e.g., password changes, cashup close/open employee attribution, audit logging). Always read `$this->session->get('person_id')` and load the corresponding employee record from the database.
- **Enforce sub-permission and cross-module grants**: Inheriting `Secure_Controller` only verifies top-level access to that controller's `$module_id`. Whenever a controller action mutates another module's data (e.g., `Sales::postChangeItem()` modifying item catalog fields) or performs a restricted sub-action (`sales_delete`, `receivings_delete`, discount overrides), explicitly verify `$this->Employee->has_grant(...)` and fail closed if unauthorized.
- **Stock location authorization (BOLA/IDOR)**: Every controller, library, or report endpoint that accepts a stock location ID (`stock_location`, `sale_location`, `stock_source`, `stock_destination`, `location_id`) must verify that the logged-in employee is permitted to access that location via `$this->Stock_location->is_allowed_location($locationId, $moduleId)` before reading or mutating stock or switching session state.
- **Normalize URI segments in permission checks**: Any permission check inspecting request URIs or action names must normalize case (`strtolower()`) and URL encoding (`rawurldecode()`) and default-deny when a target action is unrecognized.

### CSRF Protection & HTTP Verbs

- **State-changing actions must be `POST`-only**: Never expose state-changing operations (creating, updating, deleting, restoring, discarding suspended sales, sending emails/SMS, logging out, or mutating session cart state via AJAX) on `get*()` endpoints. Use `post*()` controller methods and `$.post()` / `method="post"` in views so CI4's CSRF filter protects the endpoint automatically.
- **Update controllers, views, guards, and tests together when enforcing `POST`**: Whenever an action is added to `app/Events/Method.php` (`$postRequired`) or renamed from `get*()` to `post*()`, update every controller exposing that action (e.g. both `Home::postLogout()` and `Office::postLogout()`), replace `anchor()` / `<a href>` triggers in views with `form_open()` (which automatically adds `method="post"` and `csrf_field()` when CSRF protection is active) or `$.post()`, and update existing feature tests.

### SQL Injection Prevention

- **Never interpolate raw variables or arrays into SQL strings**:
  - Do not concatenate arrays into `IN (' . implode(',', $ids) . ')` inside `join()` or `where(..., null, false)`. Guard with `!empty($ids)`, normalize elements with `array_map('intval', $ids)`, or use `$builder->whereIn()`.
  - Escape translated strings (`lang(...)`) with `$this->db->escape()` whenever embedding them into raw SQL expressions (`CASE WHEN ...`, `CONCAT(...)`), as translations may contain single quotes (e.g., French `l'article`).
- **Whitelist `ORDER BY` sort columns and directions**: Before passing `$sort` and `$order` to `$builder->orderBy()`, validate `$sort` against an explicit allowlist of column names (or strict identifier regex) and normalize `$order` to `'ASC'` or `'DESC'`.

### Cross-Site Scripting (XSS) & Output Encoding

- **Always escape dynamic output with the right context**:
  - Use `esc($value)` (`'html'` context) for element body text and `esc($value, 'attr')` inside HTML attributes.
  - CI4 helpers `anchor($uri, $title)` and `form_label($text)` do **not** automatically escape `$title` or `$text` — always pass `esc($title)` / `esc($text)`.
  - `bootstrap-toggle` decodes `data-on` and `data-off` attribute values and renders them via `.html()` in the browser: always HTML-escape/sanitize dynamic values for the HTML sink first (so the decoded attribute string cannot introduce executable markup when passed to `.html()`) **in addition to** escaping the final value for the HTML attribute context (`esc($htmlLabel, 'attr')` or `form_checkbox()` attribute encoding).
- **Escape entity names in JSON `message` responses**: The frontend renders controller JSON `response.message` strings via `$.notify()`, which injects the message as raw HTML. Always wrap user-controlled names, codes, or addresses interpolated into `message` with `esc()`.
- **Safe PHP-to-JavaScript serialization in `<script>` blocks**:
  - To embed a PHP array, object, or string as a JS literal inside `<script>`, use `json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)`.
  - **Never** wrap `json_encode(...)` inside `esc(..., 'js')` — `esc(..., 'js')` is designed for contexts *inside* an already-quoted JS string literal and will backslash-escape the structural quotes of a JSON array/object (`[\"a\"]`), causing a JavaScript `SyntaxError`.
  - **Never** run `esc($array, 'js')` on chart/series data arrays prior to `json_encode()`, as it converts numeric values into strings and double-escapes labels.

### File Handling, Uploads, Exports & Outbound Requests

- **Sanitize filenames and prevent path traversal / `glob()` injection**:
  - Always strip directory components with `basename()` and remove `glob()` metacharacters (`str_replace(['*', '?', '[', ']'], '', $filename)`) before passing user-supplied filenames to `glob()`, file readers, or database storage (including CSV imports).
  - Check optional file uploads with `if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE)` before invoking `$this->validate(['file' => 'uploaded[...]'])` or calling `$file->getTempName()`.
  - Never allow `.svg` / `image/svg+xml` in user-accessible image uploads unless sanitized by a dedicated SVG purifier, as SVG files can execute embedded `<script>` payloads.
- **Fail loudly on malformed CSV rows and preserve original line numbers**: Never silently drop CSV data rows whose column count does not match `$headers` — record them in the import's failure list (`$rowIndex + 2` for 1-based CSV line numbers after the header) so the transaction rolls back instead of reporting false success, and retain original row indices (`$csvRows[$rowIndex]`) across skipped blank/malformed lines so caller error messages report accurate file line numbers.
- **Preserve newlines in HTML passed to Dompdf**: Pass rendered HTML directly to `$dompdf->loadHtml($html)` without stripping `\n` or `\r`, so `<pre>` blocks, `white-space: pre-line` styles, and multi-line comments in PDFs retain their line breaks.
- **Prevent Server-Side Request Forgery (SSRF)**: Limit alphanumeric regex validation (`/^[a-z0-9]+$/i`) strictly to a single subdomain/datacenter label interpolated within a fixed, trusted host template (e.g. Mailchimp datacenter suffixes in `https://{$dc}.api.mailchimp.com/...`). Full outbound URLs or configurable hosts must be validated against an explicit destination allowlist rather than that regex.
