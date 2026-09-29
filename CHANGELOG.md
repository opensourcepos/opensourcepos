[unreleased]: https://github.com/opensourcepos/opensourcepos/compare/3.4.2...HEAD
[3.4.2]: https://github.com/opensourcepos/opensourcepos/compare/3.4.1...3.4.2
[3.4.1]: https://github.com/opensourcepos/opensourcepos/compare/3.4.0...3.4.1
[3.4.0]: https://github.com/opensourcepos/opensourcepos/compare/3.3.9...3.4.0
[3.3.9]: https://github.com/opensourcepos/opensourcepos/compare/3.3.8...3.3.9
[3.3.8]: https://github.com/opensourcepos/opensourcepos/compare/3.3.7...3.3.8
[3.3.7]: https://github.com/opensourcepos/opensourcepos/compare/3.3.6...3.3.7
[3.3.6]: https://github.com/opensourcepos/opensourcepos/compare/3.3.5...3.3.6
[3.3.5]: https://github.com/opensourcepos/opensourcepos/compare/3.3.4...3.3.5
[3.3.4]: https://github.com/opensourcepos/opensourcepos/compare/3.3.3...3.3.4
[3.3.3]: https://github.com/opensourcepos/opensourcepos/compare/3.3.2...3.3.3
[3.3.2]: https://github.com/opensourcepos/opensourcepos/compare/3.3.1...3.3.2
[3.3.1]: https://github.com/opensourcepos/opensourcepos/compare/3.3.0...3.3.1
[3.3.0]: https://github.com/opensourcepos/opensourcepos/compare/3.2.3...3.3.0
[3.2.3]: https://github.com/opensourcepos/opensourcepos/compare/3.2.2...3.2.3
[3.2.2]: https://github.com/opensourcepos/opensourcepos/compare/3.2.1...3.2.2
[3.2.1]: https://github.com/opensourcepos/opensourcepos/compare/3.2.0...3.2.1
[3.2.0]: https://github.com/opensourcepos/opensourcepos/compare/3.1.1...3.2.0
[3.1.1]: https://github.com/opensourcepos/opensourcepos/compare/3.1.0...3.1.1
[3.1.0]: https://github.com/opensourcepos/opensourcepos/compare/3.0.2...3.1.0
[3.0.2]: https://github.com/opensourcepos/opensourcepos/compare/3.0.1...3.0.2
[3.0.1]: https://github.com/opensourcepos/opensourcepos/compare/3.0.0...3.0.1
[3.0.0]: https://github.com/opensourcepos/opensourcepos/compare/2.4.0...3.0.0
[2.4.0]: https://github.com/opensourcepos/opensourcepos/compare/2.3.4...2.4.0
[2.3.4]: https://github.com/opensourcepos/opensourcepos/compare/2.3.3...2.3.4
[2.3.3]: https://github.com/opensourcepos/opensourcepos/compare/2.3.2...2.3.3
[2.3.2]: https://github.com/opensourcepos/opensourcepos/compare/2.3.1...2.3.2
[2.3.1]: https://github.com/opensourcepos/opensourcepos/compare/2.3...2.3.1
[2.3.0]: https://github.com/opensourcepos/opensourcepos/compare/2.2.2...2.3

# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

## [3.4.2] - 2026-09-24
- Fix writable folder permission check (#4270) (#4273) by @jekkos
- Extended payment delete fix (#4274) by @jekkos
- Upgrade github workflow (#3708) (#4280) by @jekkos
- Fix typo in writeable (#4270) by @jekkos
- Fix migration 20250522000000 (#4284) by @jekkos
- Upgrade to ci 4.6.2 (#4296) (#4298) by @jekkos
- Fix barcode generation in items (#4270) by @jekkos
- Allow empty tax category id (#4285) (#4288) by @jekkos
- Fix security incident email address (#4298) by @jekkos
- Fix item kits update (#4294) by @jekkos
- Revert toast message sanitization (#4302) by @jekkos
- Fix for suspended sales (#4283) (#4303) by @jekkos
- Fix reference to uploads folder (#4270) (#4286) by @jekkos
- Add generic try/catch in import (#4302) by @jekkos
- Bump jspdf from 3.0.1 to 3.0.2 (#4309) by @dependabot[bot]
- Fix mount path for uploads (#4308) by @jekkos
- Add transactions to missing config keys migration. (#4318) by @Joe Williams
- [Feature] Add logging to migrations (#4327) by @Joe Williams
- Clean up docker compose setup (#4308) by @jekkos
- Fix tax configuration pages (#4331) by @jekkos
- Update SECURITY.md contact (#4335) by @jekkos
- Add server side validation for password (#4335) by @jekkos
- Add env variable to disallow pwd change (#4325) by @jekkos
- Add recent releases to issue template (#4317) by @jekkos
- Add DOMpurify + fix XSS (#4341) by @jekkos
- Fix attachment cid (#4314) by @jekkos
- Add DOMPurify to JS includes (#4341) by @jekkos
- Allow anonymous giftcard creation (#4278) by @jekkos
- Fix toast notifications in config (#4341) (#4343) by @jekkos
- Fix creation of date attribute value (#4310) (#4344) by @jekkos
- Fix wrong migration script location (#4285) by @jekkos
- Escape return_policy in receipt + invoice (#4349) by @jekkos
- Fix for detailed suppliers report (#4351) by @jekkos
- Add show/hide cost price & profit feature - in reports #4130 (#4350) by @BhojKamal
- Fix travis build after merge (#4130) by @jekkos
- Add equals as permitted URI character (#4329) by @Chathura Dilushanka
- Fix multiple XSS vulnerabilities (#3965) (#4356) by @jekkos
- Bump lodash from 4.17.21 to 4.17.23 (#4369) by @dependabot[bot]
- Bump jspdf and jspdf-autotable (#4373) by @dependabot[bot]
- Fix XSS vulnerabilities in invoices + receipts (#3965) (#4363) by @jekkos
- Fix XSS vulnerability in attributes (#3965) by @jekkos
- Fix XSS vulnerability in register (#3965) by @jekkos
- Fix XSS vulnerability in register (#3965) by @jekkos
- Fix XSS vulnerabilities in invoice_email.php view by @jekkos
- Fix permission bypass in Reports submodule access control (#4389) by @jekkos
- Use Content-Type application/json for AJAX responses (#4357) by @jekkos
- Language Array Key Typo Fix (#4371) by @Lucas Lyimo
- Fix: Refresh session language for employee after update. (#4245) by @jekkos
- Fix Docker image upload by replacing slashes in TAG by @jekkos
- Fix broken object-level authorization in Employees controller (CVE-worthy) (#4391) by @jekkos
- Bump dompurify from 3.3.1 to 3.3.2 (#4402) by @dependabot[bot]
- Fix incorrect argument types in migration round_number() methods (#4403) by @jekkos
- dd validation for invalid stock locations in CSV import (#4399) by @jekkos
- fix(security): whitelist and validate invoice template types (#4393) by @jekkos
- Fix second-order SQL injection in currency_symbol config (#4390) by @jekkos
- Add row-level authorization to password change endpoints (#4401) by @jekkos
- Fix: Handle image filenames with spaces in thumbnails by @jekkos
- Fix: Sanitize image filenames to prevent thumbnail display issues (#4372) by @jekkos
- Add migration to fix existing image filenames with spaces (#4372) by @jekkos
- Refactor: Move ADMIN_MODULES to constants, rename methods to camelCase by @jekkos
- Fix SQL injection in custom attribute search by @Ollama
- Fix stored XSS vulnerability in item descriptions by @Ollama
- Fix stored XSS vulnerabilities in employee permissions and customer data by @Ollama
- Fix: Preserve CHECKBOX attribute state when adding attributes (#4385) by @jekkos
- Fix payment type becoming null when editing sales by @Ollama
- Fix broken SQL injection fix - use havingLike() instead of having() with named params by @Ollama
- Fix mass assignment vulnerability in bulk edit (GHSA-49mq-h2g4-grr9) by @Ollama
- Sync language files (#3468) by @Ollama
- Add workflow to auto-update issue templates with releases by @Ollama
- Update SECURITY.md with published security advisories by @Ollama
- Bump jspdf from 4.1.0 to 4.2.0 (#4383) by @dependabot[bot]
- Add filter persistence for table views via URL query string (#4400) by @jekkos
- Fix filter persistence javascript issues (#4400) by @jekkos
- Fix PHPUnit test configuration for database connectivity (#4430) by @jekkos
- Fix IDOR vulnerability in password change (GHSA-mcc2-8rp2-q6ch) (#4427) by @jekkos
- Fix XSS vulnerability in tax invoice view (#4432) by @jekkos
- Fix permission bypass in Sales.getManage() access control (#4428) by @jekkos
- Update SECURITY.md with published security advisories (#4431) by @jekkos
- Fix SQL injection in suggestions column configuration (#4421) by @jekkos
- Fix PHPUnit environment variables not being set (#4434) by @jekkos
- Fix DECIMAL attribute not respecting locale format (#4422) by @jekkos
- Fix stored XSS vulnerability in Attribute Definitions (GHSA-rvfg-ww4r-rwqf) (#4429) by @jekkos
- Fix: Host Header Injection vulnerability (GHSA-jchf-7hr6-h4f3) by @Ollama
- Fix stored XSS in gcaptcha_site_key on login page by @Ollama
- Fix stored XSS via stock location name by @Ollama
- Fix Token_lib::render() for PHP 8.4 compatibility by @Ollama
- Use CIUnitTestCase for consistency with other tests by @Ollama
- Fix: Pass  parameter to generate() and add composite format tests by @Ollama
- Fix strftime directives handling and tighten test assertions by @Ollama
- Add AGENTS.md with coding guidelines for AI agents by @Ollama
- Fix: Add Debit Card filter to Daily Sales and Takings by @Ollama
- Fix Taxes Summary Report totals not matching row values by @Ollama
- Add unit tests for Taxes Summary Report calculations by @Ollama
- Fix rounding consistency and update tests per review feedback by @Ollama
- Rewrite tests to use database integration testing by @Ollama
- Add seed data to tests for proper integration testing by @Ollama
- Fix: Restrict employee selection in expenses and receivings forms by @Ollama
- Fix review comments: remove redundant loop and add XSS escaping by @Ollama
- Bump jspdf from 4.2.0 to 4.2.1 by @dependabot[bot]
- Bump picomatch from 2.3.1 to 2.3.2 (#4451) by @dependabot[bot]
- fix: Clear sale session after completing sale by @Ollama
- fix: Remove redundant clear_mode() calls by @Ollama
- Translate missing strings in multiple languages by @Ollama
- Fix translation issues from code review by @Ollama
- Remove English fallbacks from non-English translations by @Ollama
- Add Calendar.php translations for missing languages by @Ollama
- feat: migrate CI from Travis to GitHub Actions with enhancements by @Ollama
- refactor: remove tables.sql and constraints.sql (#4447) by @Ollama
- refactor: remove build-database gulp task (#4447) by @Ollama
- refactor: optimize Docker image size by @Ollama
- fix: remove duplicate phpunit.xml that prevented tests from running by @Ollama
- fix: Use file-based session until database is migrated by @Ollama
- feat: Improve migration UX on login page by @Ollama
- Disable opencode workflow + run docker build by @jekkos
- Fix negative price/quantity/discount validation (GHSA-wv3j-pp8r-7q43) (#4450) by @Nozomu Sasaki (Paul)
- fix(ci): replace / with _ in branch names for Docker tags by @Ollama
- fix(security): prevent command injection in sendmail path configuration by @Ollama
- fix(security): prevent SQL injection in tax controller sort columns by @Ollama
- feat: add release workflow with automated version bumping by @Ollama
- refactor: simplify release workflow to version bump only by @Ollama
- fix: address review comments by @Ollama
- fix: address all review comments and restore issue template version update by @Ollama
- fix: Tax Rate form not loading due to router service failure (#4479) by @jekkos
- fix: Handle empty database on fresh install (#4467) by @jekkos
- Fix: Improve allowedHostnames .env configuration and fail-fast in production (#4482) by @jekkos
- [Feature]:  Case-sensitive attribute updates and CSV Import attribute deletion capability (#4384) by @objecttothis
- fix: change docker image tag to master by @jekkos
- Update to CodeIgniter 4.7.2 (#4485) by @objecttothis
- Bump lodash from 4.17.23 to 4.18.1 (#4462) by @dependabot[bot]
- [Fix]: Add missing return statements to Sales Controller functions by @Ollama
- Encourage users to star the project by @objecttothis
- Bump dompurify from 3.3.2 to 3.4.0 (#4512) by @dependabot[bot]
- fix: propagate attribute definition failures in postSaveGeneral() (#4509) by @jekkos
- fix: Escape dynamic output and fix CSS property in barcode_sheet.php (#4501) by @jekkos
- Fix CRC currency reverting to EUR/LAK in locale config (#4511) by @jekkos
- fix: Add missing $img_tag variable in Sales::getSendPdf() (#4515) by @jekkos
- fix: Language dropdown not displaying saved language correctly (#4518) by @jekkos
- fix: Scope orWhere clauses in Item::exists() and Item::get_item_id() (#4520) by @jekkos
- fix: Update calendar translations (#4498) by @jekkos
- fix: Catch mysqli_sql_exception in DB fallback handlers for fresh Docker installs (#4525) by @jekkos
- fix(home): improve internal data type handling for user identification in auth process by @enricodelarosa
- Assignable Keyboard Shortcuts Updates (#4532) by @WShells
- chore: miscellaneous updates and improvements (#4530) by @BudsieBuds
- chore(deps): bump minimatch from 3.1.2 to 3.1.5 (#4536) by @dependabot[bot]
- chore: sync project files to match upstream templates (#4537) by @BudsieBuds
- fix(ci): include hidden files in Docker build context (#4543) by @jekkos
- feat: add ALLOWED_HOSTNAMES environment variable support for Docker/Compose (#4544) by @jekkos
- fix(docker): correct permissions and fix migration barcode_type error (#4546) by @jekkos
- docs: Update SECURITY.md with disclosure process (#4549) by @jekkos
- feat: Bank transfer and wallet payment option added #4540 (#4547) by @BhojKamal
- fix(security): Path traversal vulnerability in getPicThumb (#4545) by @jekkos
- fix(security): SQL injection and path traversal vulnerabilities (#4539) by @jekkos
- fix: Capture CSV import failures in save_tax_data and save_inventory_quantities (#4507) by @jekkos
- fix: validate attributeId > 0 in saveAttributeLink() (#4508) by @jekkos
- feat: Add deployment workflow with approval gates (#4522) by @jekkos
- Bugfixes to get Migration working on MySQL and MariaDB (#4551) by @objecttothis
- Bugfix: Sale search in register not handling trailing space properly (#4557) by @objecttothis
- fix: cast string returns to int in MY_Migration (#4560) by @jekkos
- Add fallback for allowedHostnames environment variable (#4565) by @objecttothis
- fix: Allow searching by Sale ID in Takings/Daily Sales view (#4569) by @jekkos
- Add Guards to Database Migration (#4571) by @objecttothis
- fix: tax rate inputs blank with comma-decimal locales (#4555) by @jekkos
- fix(security): Fix DOMPDF RCE and customer email sanitization (#4568) by @jekkos
- Fix overly lenient date validation (#4574) by @objecttothis
- fix(security): Escape attribute value in register by @jekkos
- chore(deps): bump dompurify from 3.4.0 to 3.4.11 (#4578) by @dependabot[bot]
- Bugfix: Fix problems with migration UI in login (#4589) by @objecttothis
- Forgotten commit from login migration branch (#4592) by @objecttothis
- Feature: Payment reference code (#4587) by @objecttothis
- fix(giftcard): correct return type and rename getGiftcardId method (#4600) by @objecttothis
- chore(deps): bump dompurify from 3.4.11 to 3.4.12 (#4602) by @dependabot[bot]
- bugfix(reports): crash on detailed sales report when sale has multiple payments with reference codes (#4599) by @objecttothis
- style(models): normalize quote style in SQL GROUP_CONCAT expression (#4608) by @objecttothis
- chore(deps): upgrade dompdf from v2.0.8 to v3.1.6 (#4610) by @objecttothis
- chore(deps): bump brace-expansion (#4614) by @dependabot[bot]
- chore(deps): add xlsx via SheetJS CDN and upgrade tableexport plugin (#4615) by @objecttothis
- chore(deps): bump lodash.template from 4.5.0 to 4.18.1 (#4616) by @objecttothis
- fix(login): skip auth validation on new install to allow migration (#4609) by @objecttothis
- fix: Wrap postSave() in single transaction for atomicity (#4506) by @jekkos
- refactor: Replace var with let/const in JavaScript files (#4503) by @jekkos
- fix: get_definition_by_name() returns single row instead of multi-dimensional array (#4452) (#4464) by @Jonathan Chang
- fix(config): validate theme param to prevent XSS via invalid theme (#4620) by @objecttothis
- fix(sales): enforce server-side authorization for price changes (#4631) by @objecttothis
- fix(sales): escape quote number in email template to prevent XSS (#4625) by @objecttothis
- refactor(migrations): rename execute_script to executeScript across all migrations (#4611) by @objecttothis
- fix(auth): validate gcaptcha before password to prevent bypass (#4618) by @objecttothis
- refactor: apply PSR-12 naming to Attribute definition methods (#4624) by @Rayan Abdul Cader
- fix(security): sanitize filenames and escape logo path in config (#4630) by @objecttothis
- fix: use db_connect() for item save transactions (#4636) by @richardmilles
- fix(xss): remove redundant escaping that double-encoded item attribute values (#4628) by @objecttothis
- chore(deps): bump codeigniter4/framework from 4.7.2 to 4.7.4 (#4638) by @dependabot[bot]
- chore(deps): bump dompurify from 3.4.12 to 3.4.13 (#4639) by @dependabot[bot]
- Reject item CSV imports whose header row is missing required columns (#4597) by @Sai Asish Y
- fix(items): validate item_number and skip receiving quantity default for temp items (#4621) by @objecttothis
- Feature: CodeIgniter Throttler (#4619) by @objecttothis
- Bugfix: Resolve Race Condition in Rewards and Gift Card Spending (#4640) by @objecttothis
- hotfix(auth): hash throttler keys to improve security (#4646) by @objecttothis
- fix(items): add explicit sentinel value for clearing supplier in bulk edit (#4617) by @objecttothis
- Codeigniter changes between 4.7.2 and 4.7.4 (#4650) by @objecttothis
- Hotfix: Fix CI3 database migration caused by regression (#4649) by @objecttothis
- fix(sales): gate per-record endpoints behind reports_sales grant (#4627) by @objecttothis
- fix(email): update method call to camelCase for PSR-12 compliance (#4659) by @objecttothis
- Feature admin account safeguards (#4657) by @objecttothis
- Ensure payload data is escaped to prevent XSS (#4664) by @objecttothis
- fix(sales): enforce reports_sales grant on search endpoint (#4673) by @objecttothis
- fix(reports, home): resolve double-URL-decoding bypass for method grants (#4660) (#4666) by @objecttothis
- Bugfix tax names (#4677) by @objecttothis
- feat(validation, tests): add `valid_path_strict` rule and integrate into mailpath validation (#4684) by @objecttothis
- fix(sales): harden payment validation and gift card handling by @objecttothis
- fix: prevent duplicate items when editing imported rows (#4634) by @richardmilles
- fix(barcode): resolve string interpolation issue in barcode display html (#4692) by @Vighnesh Nilajakar
- fix(sales): gate getSearch behind reports_sales grant by @jekkos
- bugfix(sales): reject non-negative gift-card amount_tendered (#4674) by @jekkos
- fix(sales): harden unsuspend with auth, status gating, and null safety by @objecttothis
- fix(tests): resolve all phpunit failures — clean-DB suite green (#4626) (#4691) by @jekkos
- fix(licenses): guard malformed data, parallelize gulp tasks, require Node 20 by @objecttothis
- fix(validation): broaden sendmail path regex, expand i18n, strip advisory IDs by @objecttothis
- fix(security): handle special characters in `.env` key values and improve insertion logic (#4656) by @objecttothis
- fix(locale): validate language_code against known locales to block path traversal (#4704) by @jekkos
- Fix GHSA-frx7-c5vv-m3mr: recompute cashup total server-side and force owner identity (#4706) by @jekkos
- feat(security): add THROTTLE_KEY env-var fallback for throttle.key (#4707) by @jekkos
- fix(i18n): translate remaining English labels in Swiss German Items.php (#4701) by @Rayan Abdul Cader
- fix(ci): stop stamping app version onto master and branch Docker tags (#4709) by @jekkos
- chore(deps): bump fflate from 0.8.2 to 0.8.3 (#4690) by @dependabot[bot]
- fix(i18n): swap print_delay_autoreturn number/required messages in 5 locales (#4699) by @Rayan Abdul Cader
- chore(release): unified git-cliff release workflow (changelog + tag + optional bump) (#4711) by @jekkos
- fix(release): push changelog/bump to master via admin PAT (GITHUB_TOKEN blocked by branch protection) by @jekkos

## [3.4.1] - 2025-06-05
- Feature: PSR-12 Compliant Indentation by @objecttothis in ([#4196](https://github.com/opensourcepos/opensourcepos/pull/4196))
- Add .env to dist zip by @jekkos in ([#4199](https://github.com/opensourcepos/opensourcepos/pull/4199))
- Add CI4 coding standards linter ([#3708](https://github.com/opensourcepos/opensourcepos/issues/3708)) by @jekkos in ([#4198](https://github.com/opensourcepos/opensourcepos/pull/4198))
- Bump canvg from 3.0.10 to 3.0.11 by @dependabot in ([#4189](https://github.com/opensourcepos/opensourcepos/pull/4189))
- Bump jspdf and jspdf-autotable by @dependabot in ([#4190](https://github.com/opensourcepos/opensourcepos/pull/4190))
- Feature bump ci to 4.6.0 by @objecttothis in ([#4197](https://github.com/opensourcepos/opensourcepos/pull/4197))
- Add Kurdish language option to UI by @BudsieBuds in ([#4210](https://github.com/opensourcepos/opensourcepos/pull/4210))
- Convert language ku to ckb by @BudsieBuds in ([#4211](https://github.com/opensourcepos/opensourcepos/pull/4211))
- Fix PHP 8.4 errors by @BudsieBuds in ([#4215](https://github.com/opensourcepos/opensourcepos/pull/4215))
- Add default bootstrap to themes by @BudsieBuds in ([#4219](https://github.com/opensourcepos/opensourcepos/pull/4219))
- Update language names by @BudsieBuds in ([#4218](https://github.com/opensourcepos/opensourcepos/pull/4218))
- Update install docs by @BudsieBuds in ([#4217](https://github.com/opensourcepos/opensourcepos/pull/4217))
- Convert menu icons to SVG by @BudsieBuds in ([#4220](https://github.com/opensourcepos/opensourcepos/pull/4220))
- Enhance license handling by @BudsieBuds in ([#4223](https://github.com/opensourcepos/opensourcepos/pull/4223))
- Fix datetime rendering ([#4226](https://github.com/opensourcepos/opensourcepos/issues/4226)) by @jekkos in ([#4227](https://github.com/opensourcepos/opensourcepos/pull/4227))
- Fix datetime rendering by @jekkos in ([#4228](https://github.com/opensourcepos/opensourcepos/pull/4228))
- Fix null error when sending by email a receipt of a sale that has no invoice by @diego-ramos in ([#4229](https://github.com/opensourcepos/opensourcepos/pull/4229))
- Update Receivings.php to save form. by @odiea in ([#4231](https://github.com/opensourcepos/opensourcepos/pull/4231))
- Update Cashups.php for ajax cashup total to work. by @odiea in ([#4238](https://github.com/opensourcepos/opensourcepos/pull/4238))
- Coding style updates for PSR-12 compliance & improved readability by @BudsieBuds in ([#4204](https://github.com/opensourcepos/opensourcepos/pull/4204))
- Fix Codeigniter disallowed characters error with payment types that have accents by @diego-ramos in ([#4232](https://github.com/opensourcepos/opensourcepos/pull/4232))
- Fixed broken escape string for success & warning messages by @Franchovy in ([#4253](https://github.com/opensourcepos/opensourcepos/pull/4253))
- Bugfix constraint migration fix by @objecttothis in ([#4230](https://github.com/opensourcepos/opensourcepos/pull/4230))
- Fix item number lookup in sales/receivings ([#4212](https://github.com/opensourcepos/opensourcepos/issues/4212)) by @jekkos in ([#4250](https://github.com/opensourcepos/opensourcepos/pull/4250))

## [3.4.0] - 2025-03-23

- Translation updates (Spanish, Indonesian, Swedish, Urdu, Chinese, Thai, French, Dutch)
- PHP `8.x` support
- Security fixes (XSS, SQLi)
- Migration to Gulp as buildsystem
- Decimal validation fix
- Sticky header fix
- Receipt sent as attachment
- Barcode generation library upgrade
- Bump framework to CodeIgniter `4.x.x`
- Improve security performance against bots

## [3.3.9] - 2023-11-06

- Translation updates (Arabic, Central Khmer, Croatian, Czech, Danish, English, French, Indonesian, Lao, Russian, Spanish, Thai)
- Fix logout race condition issue ([#3578](https://github.com/opensourcepos/opensourcepos/issues/3578))
- Fix docker compose file ([#3754](https://github.com/opensourcepos/opensourcepos/issues/3754))
- Minor report fixes

## [3.3.8] - 2022-08-03

- Translation updates (Azerbaijani, Flemish, French, Spanish, Thai, Vietnamese)
- Fix logo removal issue (CSRF regression) ([#3533](https://github.com/opensourcepos/opensourcepos/issues/3533))
- Substract refunds from total rewards as payment method ([#3536](https://github.com/opensourcepos/opensourcepos/issues/3536))

## [3.3.7] - 2022-03-29

- Translation updates (Chinese, French, Indonesian, Italian, Polish, Swedish, Thai)
- XSS fixes in bootstrap datatables
- Invoice numbering fixes
- Docker compose database scripts are now mounted from a container volume

## [3.3.6] - 2021-10-31

- Translation updates (Bosnian, Dutch, Indonesian, Polish, Russian, Spanish)
- Make footer revision clickable (ref to github)
- Minor reporting adjustments
- Introduced new global keyboard shortcuts (see overview below)

### Fixes

- reCaptcha issue fix
- Username verification bugfix
- Clickjacking security mitigations
- Fixes for the payment summary after refresh
- Hardening against XSS by introducing a CSP header in the HTTP headers
- Several CSRF and XSS fixes
- Type juggling password fix for old logins


## [3.3.5] - 2021-08-26 [YANKED]

- Translation updates (Arabic, Azerbaijani, Bulgarian, Chinese, Dutch, French, Indonesian, Polish, Portuguese, Romanian, Spanish, Swedish, Tamil, Thai, Turkish, Ukrainian, Vietnamese)
- New responsive login page based on Bootstrap `5`
- Translation fallback to English when a string is untranslated for the selected language
- Database and performance optimizations
- Grunt/CI updates
- CSV item import improvements

### Fixes

- Username verification fix on employee insert/update
- Minor report fixes
- Attribute encoding fix
- Decimal render fix
- Fixes for Docker to make it run on Windows
- Blind SQL injection fix

## [3.3.4] - 2021-04-20

- Translation updates (Hungarian, Indonesian, Bosnian, Ukrainian, Vietnamese, Spanish)
- Prevent data wipeout when calling GET directly on the save endpoint
- Cleanup `.htaccess`
- Docker compose usability improvements
- Cookie secure flag fix for Chrome (you can enable CSRF protection again now)
- Use LONGBLOB for session storage. This should fix issues preventing a user from adding a large number of items to register
- Cash rounding bugfixes
- Fix daily overview cash sale totals
- Show sale count in the transaction report
- Button disable to prevent double submission
- Add barcode field to item kits
- Fix discount register parsing in some specific locales

## [3.3.3] - 2021-01-01

- PHP `7.4` support
- Set PHP `7.2` to be the minimum level due to older version deprecations
- Added email CC and BCC (see `config/email.php`)
- Cash rounding to nearest 5 cents
- Updated composer packages and JS plugins
- Improved security (CSRF protection)
- Various small improvements and bug fixes

## [3.3.2] - 2020-09-03

- Fixed `only_full_group_by` issue with MySQL/MariaDB
- Fixed POS transaction return failure if items were deleted
- Various bug fixes

## [3.3.1] - 2019-12-14

- Various bug fixes (please disable `only_full_group_by` option from MySQL/MariaDB to avoid issues)

## [3.3.0] - 2019-09-29

- New logo
- Upgrade CodeIgniter to version `3.1.11`
- PHP `7.3` support
- Attributes feature (allows extensibility of items replacing old custom fields)
- India GST tax support + various tax support improvements
- Cash up feature
- Temporary items feature
- Fixed sales discount
- Supplier category feature
- Improved items import and CSV file generation (to contain additional attributes)
- Improved Docker installation with NGINX reverse proxy using Let's Encrypt TLS certificate
- Database performance improvements
- Added and udated translations
- Fixed various reports issues
- Fixed rounding issues
- Fixed CSRF issues
- Fixed database upgrade script issues
- Various bug fixes

## [3.2.3] - 2018-06-13

- Upgrade CodeIgniter to version `3.1.9`
- Further revert of CSRF change causing regression

## [3.2.2] - 2018-06-06

- Revert CSRF change causing regression

## [3.2.1] - 2018-06-04

- Support for GDPR
- CSRF simplifications
- Translation upgrades
- Various bug fixes

## [3.2.0] - 2018-04-14

- Upgrade CodeIgniter to version `3.1.8`
- PHP `7.2` support (use OpenSSL and not MCrypt)
- Automatic database upgrades from `3.0.0` at first login (no more SQL scripts)
- Home and (back)office menu switch (top menu can be organized in two views)
- Expenses feature
- Quote and work order features
- Improved invoice support
- Sale suspend, soft delete, complete as the state not as different tables or hard delete
- Restore deleted sales
- Improved item kits
- Export tables all records and export to PDF
- Table sticky header (headers visible during scrolling)
- Allow duplicate barcodes (config option)
- Search suggestion formatting (config option)
- Define print and email checkboxes behavior (config option)
- Edit customer from sales register
- Added and updated translations
- Various jQuery plugins upgrade
- Fixed permission issues (e.g. password change)
- Fixed various reports issues and renamed Sales to Transactions
- Various bug fixes (e.g. tax, rounding, library circular dependency)

## [3.1.1] - 2017-09-09

- Updated en-US and en-GB translations, better grammar, and consistency
- Fixed database migration issue with VAT tax included
- Fixed database backup bug
- Fixed gift card error
- Fixed database `upgrade to 3.1.x` script (now it's to `3.1.1` and there is no `3.1.0` anymore)
- Fixed old database upgrade scripts for people upgrading from `2.x` versions
- Fixed `.htaccess` file in OSPOS root dir (it was not forwarding to `public` subdir)
- Fixed few jQuery `2.0` upgrade issues

## [3.1.0] - 2017-09-02

- MySQL `5.7` and PHP `7.x` support
- Advanced tax support with customer tax categories and more
- Better horeca use case support with dinner table sale tagging
- Customer rewards support
- Added quote support and better invoice support
- Added integration with Mailchimp to connect customer list with Mailchimp list
- Prevent inserting two customers with the same email address
- Customer total spending and stats
- Added Google reCAPTCHA option for the login page to increase protection from brute force attacks
- Added due payment for credit sale support
- Gift card numbering with two options: series and random
- Extended item kits functionality
- Employees are allowed to change their own password by clicking their name in the top bar
- Cash rounding support, extended decimals
- Reworked item pictures, file names, and storing
- Financial year start date and selection from date range pickers
- Date time range filters can be date and time or date only
- Added two new Bootswatch themes
- Receipts font size support
- Fix automatically people's name first capital letter, emails in lower case only
- Fixes to Receiving
- Various amendments to database script updates from older versions
- Added dotenv support
- Updates to language translations (split English to American English and British English)
- Various Dockers support improvements
- Minor bugfixes

## [3.0.2] - 2016-12-31

- Fixed error when performing scans multiple times in a row
- Fixed summary reports
- Protect employee privacy by printing just the first letter of the family name
- Updates to language translations
- Various Dockers support improvements
- Minor bugfixes

## [3.0.1] - 2016-11-27

- Upgrade CodeIgniter to version `3.1.2`
- Substantial database performance improvements
- Improved security: email and SMS passwords encryption, removed `phpinfo.php`
- Set code to be production and not development in `index.php`
- Reports improvements, fixed table sorting, tax calculation and made profit to be net profit
- Better Apache `2.4` support in `.htaccess`
- Updates to language translations
- Fixed excel template download links
- Fixed employee name in sale receipt and invoice reprinting
- Fixed `2.3.2_to_2.3.3.sql` database upgrade script mistake
- Fixed `phppos to ospos` database migration script
- Minor bug fixes and some general code clean up

## [3.0.0] 2016-10-22

- Upgrade CodeIgniter to version `3.1.0`
- Major UI overhaul based on Bootstrap `3.0` and Bootswatch Themes
- New tabular views with advanced filtering using Bootstrap Tables
- New graphical reports with no more Adobe Flash dependency
- Redesign of all modal dialogs
- Updated Sales register with simplified payment flow
- Improved security: MySQL injection, XSS, CSFR, BCrypt password encryption, safer project layout
- Support for text messaging (interfacing to specific support required)
- Email configuration
- Improved Localisation support
- Improved Store Config page
- Docker container ready for cloud installation
- Composer PHP support
- More languages and integration with Weblate for continuous translation
- About 280 closed issues under `3.0.0` release label, too many to produce a meaningful list
- Various code cleanup, refactoring, optimization and etc.

## [2.4.0] - 2016-10-03

- Upgrade CodeIgniter to version `3.0.5`
- Fix for spurious logouts
- Apache `.htaccess` `mod_expiry` caching and security optimizations
- Bulk item edit fixes (category, tax, and supplier fields)
- Remove f-key shortcuts used for module navigation
- Allow using custom invoice numbers when suspending a sale
- PHP `7` fixes
- Specific warnings to distinguish between reorder level and out of stock situation in sales
- Fix malware detection issues due to usage of `base64` encoding for storing session variables
- Improve language generation scripts (use PHP builtin functionality)
- Add extra buttons for navigation and printing to receipt and invoice
- Improve print layout for invoices
- Make layout consistent for items between receipt and invoice templates
- Minor bugfixes

## [2.3.4] - 2016-02-08

- Migration script fixes
- Improved continuous integration setup
- More integration tests
- Virtualized container setup (`docker install`)
- Live clock functionality and favicon
- Improved PHP `7` compatibility
- Added de_CH (German) as language
- Minor code cleanup
- Removal of annoying backup prompt on logout

## [2.3.3] - 2016-01-06

- Item kit fixes (search, list, ...)
- Add date picker widgets in sale/receiving edit forms
- Add date filter in items module
- Add barcode generation logic for EAN8, EAN13
- Add barcode validation and fallback logic for EAN8, EAN13
- New config option to generate barcodes if `item_number` is empty
- Add cost and count to inventory reports
- Gift card fixes
- Refactor sales overview (added date filtering + search options)
- Better locale config support
- Improve PHP compatibility
- Fix invoice numbering bug on suspending a sale
- Add configurable locale-dependent date format
- Add grunt-cache-breaker plugin
- Suspend button appears before adding a payment
- Searching of deleted items, filtering part is removed
- Remove infamous `0` after leaving sale or receiving comments empty
- Add SQL script to clean zeroes in sales/receivings comments
- Numerous other bug fixes

## [2.3.2] - 2016-01-25

- Nominatim (OpenStreetMap) customer address autocompletion
- Sale invoice templating
- Configurable barcode generation for items
- Stock location filtering in detailed sales and receivings reports
- Gift cards fixes
- Proper pagination support for most modules
- Language updates
- Fix for decimal tax rates
- Add gender and company name attributes to customer
- Stock location config screen refactor
- Basic Travis CI and PhantomJS setup
- Database backup on admin logout
- Modifiable item thumbnails
- Email invoice PDF generation using DomPDF
- Modifiable company logo
- jQuery upgrade (`1.2` -> `1.8.3`)
- JavaScript minification (using Grunt)
- Numerous bugfixes

## [2.3.1] - 2015-02-11

- Extra report permissions (this includes a refactoring of the database model - new grants table)
- Tax inclusive/exclusive pricing
- Receivings amount multiplication (can be configured in items section)
- Customizable sale and receiving numbering
- Gift card improvements
- Fix item import through CSV
- Bug fixes for reports

## [2.3.0] - 2014-08-20

- Support for multiple stock locations

## 2.2.2 - 2014-08-19

- French language added
- Thai language added
- Upgrade CodeIgniter to version `2.2.0`
- Database types for amounts all changed to decimal types (this will fix rounding errors in the sales and receivings reports)
- Fix duplicated session cookies in HTTP headers (this broke the application when running on Nginx)

## 2.1.1

- Barcodes on the order receipt were not generated correctly
- Sales edit screen for detailed sales reports is now available with ThickBox as in the rest of the application
- Indonesian language files updated (Oktafianus)
- Default language set to `en` in `config.php`
- Fixed some CSS bugs in the suspended sales section
- Default cookie `sess_time_expire` set to `86400` (24h)

## 2.1.0

- Various upgrades, too numerous to list here
- Removed dependency on ofc upload library due to vulnerability found

## 2.0.2

- Fixed multiple gift cards issue per Bug #4 reported on Sourceforge where a second gift card added would have its balance set to `0` even if the sale did not require the total of the second gift card to pay the remaining amount due
- Small code cleanup

## 2.1.0

- Upgrade CodeIgniter to version `2.1.0`
- Various small improvements
