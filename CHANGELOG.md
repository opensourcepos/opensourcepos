# Change Log

Custom changes made to the upstream Open Source Point of Sale (OSPOS) codebase.
Organized by feature area; the most recent work is listed first.

## 1. Items screen — show quantities for multiple stock locations

- **Item list per-location quantity columns** (`app/Views/items/manage.php`, `app/Models/Item.php`, `app/Helpers/tabular_helper.php`, `app/Controllers/Items.php`, `app/Libraries/Item_lib.php`)
  - The Items table now has one quantity column **per stock location** (e.g. `1 LAOAG`, `2 WH BLDG`, ...) plus a **Total** column that sums all locations.
  - A checkbox list lets you show/hide each location's column instantly (no page reload) via bootstrap-table `showColumn`/`hideColumn`.
  - Search and row-refresh queries return every location column regardless of selection.
  - Selected locations are persisted in the session/URL.

## 2. Sales — customer required to complete a transaction

- `app/Controllers/Sales.php` (`postComplete`): completing a sale now requires a selected customer. If no customer is set, the register reloads with the error `"A customer must be selected to complete the transaction."`
- New `customer_required_error` language key added to all `app/Language/*/Sales.php` variants (English in `en`/`en-GB`, empty string elsewhere, per localization rules).

## 3. Stock Transfers module (new)

- **New module** to move stock between locations: `app/Controllers/Transfers.php`, `app/Models/Transfer.php`, `app/Libraries/Transfer_lib.php`, views `app/Views/transfers/register.php` and `receipt.php`.
- New migration `20260813000000_AddTransfers.php`.
- Permissions auto-created for new stock locations (`app/Models/Stock_location.php`), module registered in routes (`app/Config/Routes.php`), `Module` language keys for all languages.
- Lot/supplier attribution is preserved on transfer (the same `receiving_id` is moved).

## 4. Lot tracking by supplier (new)

- New `app/Models/Item_lot.php` and migration `20260810000000_AddSupplierLotTracking.php` — tracks received quantity per lot and ties lots to a supplier.
- `app/Models/Receiving.php` records received quantities as lots.
- `app/Models/Reports/Detailed_sales.php` and Items search include the supplier ("Supplied By") for each line.
- CLI smoke test: `app/Commands/LotTrackingSmokeTest.php`.

## 5. Suspended sale stock reservations

- New migration `20260811000000_AddSuspendedSaleReservations.php`.
- `app/Models/Sale.php`: stock is reserved when a sale is suspended and released when it is completed or cancelled; inventory remarks distinguish `SUSPENDED` vs `POS`.
- `app/Controllers/Sales.php`: new `postDeleteSuspended()` to cancel a suspended sale and release its stock.
- `app/Views/sales/suspended.php`: added cancel button and fixed customer-name display.

## 6. Reports

- **Inventory by Location** report (`app/Models/Reports/Inventory_by_location.php`, `app/Views/reports/inventory_by_location.php`) — pivot of stock quantity per product per location with a total.
- **Detailed Transfers** report (`app/Models/Reports/Detailed_transfers.php`, `app/Controllers/Reports.php` `date_input_transfers`, `app/Views/reports/tabular_details_transfers.php`) plus route and listing entry.
- Detailed Sales report gains a "Supplied By" column.
- `app/Views/reports/tabular_details.php`: removed the unused `init_dialog` block.

## 7. Services workflow (work orders, quotes, invoices)

- Services are modeled as **non-stock items** (Items screen → `Stock type = Non-stock`) so they ring up on the register without touching inventory. Create e.g. a "Services" category with items like "Desktop Reformat", "Cleaning", "CCTV Installation".
- **Work Orders**: new migration `20260814000000_EnableServicesWorkflow.php` inserts any missing work-order/quote/invoice config keys (`INSERT IGNORE`) and enables Work Order support (`work_order_enable = 1`). The keys were historically only seeded by older upgrade scripts; `INSERT IGNORE` keeps this safe on both fresh and existing databases.
- The register now offers **Receipt / Quote / Work Order / Invoice** modes for service jobs; work orders/invoices print and email with their own sequential numbering (`W%y{WSEQ:6}`, `Q%y{QSEQ:6}`, `$CO`).
- Uses OSPOS's built-in invoice system — no separate service module needed.

## 8. Bug fixes / hardening

- `app/Config/Validation/OSPOSRules.php`: `themeExists()` now resolves the theme dir from `FCPATH` instead of a relative path.
- `app/Controllers/Config.php` (`postSaveLocale`): `payment_reference_code_min/max` are sanitized before validation and no longer rejected when blank.
- `app/Helpers/locale_helper.php`: `parse_decimals()` uses safe defaults for missing config values.
- `app/Database/Migrations/20170501000000_initial_schema.php`: rollback no longer drops the framework `migrations` history table.
- `app/Views/partial/header.php` and `app/Views/login.php`: restored the injected CSS/JS resource includes (gulp output).
- Unit tests: session setup fixed for `ConfigTest`, `EmployeesControllerTest`, `HomeTest`, `SalesControllerTest`, `CustomersCsvImportTest`.

## 9. Development tooling

- `Dockerfile`: dev stage installs `composer`, `unzip`, `git` and the dev dependencies (PHPUnit, php-cs-fixer, faker) for running tests.
- `docker-compose.override.yml`: local dev overrides (development environment, xdebug, source/test mounts).
- `.dockerignore`: keeps `composer.json`/`composer.lock` in the build context.
- `.gitignore`: ignores `phpunit.xml`.
- `tests/run-tests.sh`: helper that runs the PHPUnit suite against a dedicated `ospos_test` database.
- `tests/Models/TransferReversalTest.php`: tests for transfer stock reversal.
