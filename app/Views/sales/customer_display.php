<?php
/**
 * @var array $cart
 * @var array $config
 * @var float $rate
 * @var float $total
 * @var float $subtotal
 * @var float $prediscount_subtotal
 * @var array $taxes
 * @var array $payments
 * @var float $amount_change
 */

$priceWithCurrencyLabel = lang('Sales.price_with_currency');

?>

<?= view('partial/customer_display_header') ?>

            <div class="customer-display-cart-column">
                <div class="register-wrap">
                    <div class="panel panel-default customer-display-items-panel">
                        <div class="panel-heading"><?= lang('Sales.items') ?></div>
                        <div class="panel-body table-responsive">
                            <table class="table table-striped table-condensed" id="register">
                                <thead>
                                    <tr>
                                        <th style="width: <?= (int) $cartItemWidth ?>%;"><?= lang('Sales.item_name') ?></th>
                                        <?php if ($cartHasCustomerDisplay) { ?>
                                            <th style="width: <?= (int) $cartPriceWidth ?>%;"><?= sprintf($priceWithCurrencyLabel, esc($customerDisplayCurrencyLabel)) ?></th>
                                        <?php } ?>
                                        <th style="width: <?= (int) $cartOriginalWidth ?>%;"><?= sprintf($priceWithCurrencyLabel, esc($originalCurrencyLabel)) ?></th>
                                        <th style="width: <?= (int) $cartQuantityWidth ?>%;"><?= lang('Sales.quantity') ?></th>
                                        <th style="width: <?= (int) $cartDiscountWidth ?>%;"><?= lang('Sales.discount') ?></th>
                                        <th style="width: <?= (int) $cartTotalWidth ?>%;"><?= lang('Sales.total') ?></th>
                                    </tr>
                                </thead>
                                <tbody id="cart_contents">
                                    <?php if (count($cart) == 0) { ?>
                                        <tr>
                                            <td colspan="<?= (int) $cartColspan ?>">
                                                <div class="alert alert-dismissible alert-info"><?= lang('Sales.no_items_in_cart') ?></div>
                                            </td>
                                        </tr>
                                    <?php } else { ?>
                                        <?php foreach (array_reverse($cart, true) as $line => $item) { ?>
                                            <tr>
                                                <td class="item-name-cell">
                                                    <?= esc($item['name']) ?><br>
                                                    <?= !empty($item['attribute_values']) ? esc($item['attribute_values']) : '' ?>
                                                </td>
                                                <?php if ($cartHasCustomerDisplay) { ?>
                                                    <td class="price-cell">
                                                        <?= to_secondary_currency((float)$item['price'], $secondaryCurrency) ?>
                                                    </td>
                                                <?php } ?>
                                                <td class="price-cell">
                                                    <?= to_currency($item['price']) ?>
                                                </td>
                                                <td class="price-cell">
                                                    <?= to_quantity_decimals($item['quantity']) ?>
                                                </td>
                                                <td class="price-cell">
                                                    <?= to_decimals($item['discount'], 0) ?>
                                                </td>
                                                <td class="price-cell">
                                                    <?= $item['item_type'] == ITEM_AMOUNT_ENTRY ? to_currency_no_money($item['discounted_total']) : to_currency($item['discounted_total']) ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="<?= $cartHasCustomerDisplay ? 3 : 2 ?>"></td>
                                                <td class="serial-cell">
                                                    <?= $item['is_serialized'] == 1 ? lang('Sales.serial') : '' ?>
                                                </td>
                                                <td colspan="2" class="serial-cell">
                                                    <?php if ($item['is_serialized'] == 1) {
                                                        echo esc($item['serialnumber']);
                                                    } ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="customer-display-summary-column">
                <div class="panel panel-primary customer-display-summary-panel">
                    <div class="panel-heading"><?= lang('Sales.summary') ?></div>
                    <div class="panel-body">
                        <table class="table table-condensed summary-subtable">
                            <tbody>
                                <tr>
                                    <th><?= lang('Sales.total') ?></th>
                                    <td><?= to_currency($total) ?></td>
                                </tr>
                                <?php if ($showCustomerDisplay): ?>
                                    <tr>
                                        <th><?= lang('Sales.total') ?> <?= esc($customerDisplayCurrencyLabel) ?></th>
                                        <td><?= to_secondary_currency((float)$total, $secondaryCurrency) ?></td>
                                    </tr>
                                    <tr class="rate-row">
                                        <th><?= lang('Sales.rate') ?></th>
                                        <td><?= number_format((float) $rate, 2) ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <table class="table table-condensed summary-subtable" style="margin-top: 10px;">
                            <tbody>
                                <tr class="summary-section-row">
                                    <th colspan="2"><?= lang('Sales.customer') ?></th>
                                </tr>
                                <tr>
                                    <th><?= lang('Sales.customer_name') ?></th>
                                    <td class="customer-name-value"><?= esc($customerName ?? lang('Sales.walk_in_customer')) ?></td>
                                </tr>
                                <tr>
                                    <th><?= lang('Sales.giftcard_balance') ?></th>
                                    <td class="giftcard-value"><?= to_currency((float) ($giftcardRemainder ?? 0)) ?></td>
                                </tr>
                                <tr>
                                    <th><?= lang('Sales.loyalty_reward_points') ?></th>
                                    <td class="reward-value"><?= esc((string)($customerRewardPoints ?? 0)) ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <table class="table table-condensed summary-subtable" style="margin-top: 10px;">
                            <tbody>
                                <tr class="summary-section-row">
                                    <th colspan="2"><?= lang('Sales.change') ?></th>
                                </tr>
                                <tr>
                                    <th><?= lang('Sales.payments_total') ?></th>
                                    <td><?= to_currency($payments_total) ?></td>
                                </tr>
                                <tr>
                                    <th><?= lang('Sales.amount_due') ?></th>
                                    <td><?= to_currency($amount_due) ?></td>
                                </tr>
                                <?php if ($showCustomerDisplay): ?>
                                    <tr>
                                        <th><?= lang('Sales.amount_due') ?> <?= esc($customerDisplayCurrencyLabel) ?></th>
                                        <td><?= to_secondary_currency((float)$amount_due, $secondaryCurrency) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <th><?= lang('Sales.change_due') ?></th>
                                    <td><?= to_currency($paymentChangeDue ?? 0) ?></td>
                                </tr>
                                <?php if ($showCustomerDisplay): ?>
                                    <tr>
                                        <th><?= lang('Sales.change_due') ?> <?= esc($customerDisplayCurrencyLabel) ?></th>
                                        <td><?= to_secondary_currency((float)($paymentChangeDue ?? 0), $secondaryCurrency) ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="customer-display-footer"></div>
    </div>

<script>
        const customerDisplayId = new URLSearchParams(window.location.search).get('displayId') || '';
        const customerDisplayStorageSuffix = customerDisplayId !== '' ? '_' + customerDisplayId : '';
        const customerDisplayStorageKeys = {
            open: 'customerDisplayOpen' + customerDisplayStorageSuffix,
            dirtyAt: 'customerDisplayDirtyAt' + customerDisplayStorageSuffix
        };

        localStorage.setItem(customerDisplayStorageKeys.open, '1');

        let lastDirtyAt = localStorage.getItem(customerDisplayStorageKeys.dirtyAt) || '';
        let refreshTimer = null;

        const scheduleRefresh = function(dirtyAt) {
            if (refreshTimer !== null) {
                clearTimeout(refreshTimer);
            }

            refreshTimer = setTimeout(function() {
                if (localStorage.getItem(customerDisplayStorageKeys.open) !== '1') {
                    return;
                }

                if (localStorage.getItem(customerDisplayStorageKeys.dirtyAt) === dirtyAt) {
                    window.location.reload();
                }
            }, 700);
        };

        const checkForRefresh = function() {
            const dirtyAt = localStorage.getItem(customerDisplayStorageKeys.dirtyAt) || '';
            if (dirtyAt !== '' && dirtyAt !== lastDirtyAt) {
                lastDirtyAt = dirtyAt;
                scheduleRefresh(dirtyAt);
            }
        };

        window.addEventListener('storage', function(event) {
            if (event.key === customerDisplayStorageKeys.dirtyAt) {
                checkForRefresh();
            }
        });

        setInterval(checkForRefresh, 500);

        window.addEventListener('beforeunload', function() {
            localStorage.removeItem(customerDisplayStorageKeys.open);
        });
    </script>

</body>
</html>


