# OSPOS sales customization: upgrade notes

The customization is based on upstream 3.4.3. It adds sale-specific item names and bill-level fixed and percentage discounts. The entered bill-discount setting and each line's allocation are stored separately; the item ID remains the inventory identity.

Before an upgrade, back up the application image, database, configuration and uploads. Reapply the customization to the new upstream release in a separate staging environment, review upstream changes to sales, tax, payments, receipts and migrations, and run the test suite. Restore a copy of the database and run the migrations there first. Check a sale with mixed tax rates and split payments, an inclusive-tax sale, a suspended sale, a return and a receipt reprint. Confirm the image runs on ARM64. Only upgrade the target after the staged checks pass, and retain the backup and old image until verification is complete.

The migrations add nullable sale-name and discount-setting fields, plus a zero-default line allocation. Historical sales without a stored sale name continue to use the inventory name. The migration down methods remove the added data; restoring the pre-upgrade backup is the safer rollback after sales have been recorded with these fields.

Reporting changes are deferred: sales reports and profit/discount summaries may not account for bill-level discounts. Do not rely on those reports for reconciliation until reporting is updated and tested. The sales takings and saved-sale balance calculations have been updated for the bill discount.

Applying a new bill discount is available for non-return sales, with up to four decimal places in a percentage and up to four currency decimal places. A return created from an existing receipt restores the stored line allocations with reversed signs. Manual application of a new bill discount to a return is not available. Changing a line's price, quantity or item discount clears the bill discount and existing payments; the register displays a warning so the cashier can reapply them.

The automated checks cover the basic inclusive- and exclusive-tax paths, multiple base tax rates, split payments, saved sale names, receipt reprints, suspension and receipt-based returns. Destination-based cascading taxes, every document template, unusual currency precision and all return combinations still require additional acceptance testing before production use.
