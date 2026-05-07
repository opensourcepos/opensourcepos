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

?>

<?= view('partial/customer_display_header') ?>

            <div class="customer-display-cart-column">
                <div class="register-wrap">
                    <div class="panel panel-default customer-display-items-panel">
                        <div class="panel-heading">Items</div>
                        <div class="panel-body table-responsive">
                            <table class="table table-striped table-condensed" id="register">
                                <thead>
                                    <tr>
                                        <th style="width: <?= (int) $cartItemWidth ?>%;"><?= lang('Sales.item_name') ?></th>
                                        <?php if ($cartHasCustomerDisplay) { ?>
                                            <th style="width: <?= (int) $cartPriceWidth ?>%;"><?= 'Price (' . esc($customerDisplayCurrencyLabel) . ')' ?></th>
                                        <?php } ?>
                                        <th style="width: <?= (int) $cartOriginalWidth ?>%;"><?= 'Price (' . esc($originalCurrencyLabel) . ')' ?></th>
                                        <th style="width: <?= (int) $cartQuantityWidth ?>%;"><?= lang('Sales.quantity') ?></th>
                                        <th style="width: <?= (int) $cartDiscountWidth ?>%;"><?= lang('Sales.discount') ?></th>
                                        <th style="width: <?= (int) $cartTotalWidth ?>%;"><?= lang('Sales.total') ?></th>
                                    </tr>
                                </thead>
                                <tbody id="cart_contents">
                                    <?php if (count($cart) == 0) { ?>
                                        <tr>
                                            <td colspan="<?= $cartColspan ?>">
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
                                                <td colspan="<?= $cartHasCustomerDisplay ? 2 : 2 ?>" class="serial-cell">
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
                    <div class="panel-heading">Summary</div>
                    <div class="panel-body">
                        <table class="table table-condensed summary-subtable">
                            <tbody>
                                <tr>
                                    <th>Total</th>
                                    <td><?= to_currency($total) ?></td>
                                </tr>
                                <?php if ($showCustomerDisplay): ?>
                                    <tr>
                                        <th>Total <?= esc($customerDisplayCurrencyLabel) ?></th>
                                        <td><?= to_secondary_currency((float)$total, $secondaryCurrency) ?></td>
                                    </tr>
                                    <tr class="rate-row">
                                        <th>Rate</th>
                                        <td><?= number_format($rate) ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <table class="table table-condensed summary-subtable" style="margin-top: 10px;">
                            <tbody>
                                <tr class="summary-section-row">
                                    <th colspan="2">Customer</th>
                                </tr>
                                <tr>
                                    <th>Customer Name</th>
                                    <td class="customer-name-value"><?= esc($customerName ?? lang('Sales.walk_in_customer')) ?></td>
                                </tr>
                                <tr>
                                    <th>Gift Card Balance</th>
                                    <td class="giftcard-value"><?= esc((string)($giftcardRemainder ?? '0')) ?></td>
                                </tr>
                                <tr>
                                    <th>Loyalty Reward Points</th>
                                    <td class="reward-value"><?= esc((string)($customerRewardPoints ?? 0)) ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <table class="table table-condensed summary-subtable" style="margin-top: 10px;">
                            <tbody>
                                <tr class="summary-section-row">
                                    <th colspan="2">Change</th>
                                </tr>
                                <tr>
                                    <th>Payments Total</th>
                                    <td><?= to_currency($payments_total) ?></td>
                                </tr>
                                <tr>
                                    <th>Amount Due</th>
                                    <td><?= to_currency($amount_due) ?></td>
                                </tr>
                                <?php if ($showCustomerDisplay): ?>
                                    <tr>
                                        <th>Amount Due <?= esc($customerDisplayCurrencyLabel) ?></th>
                                        <td><?= to_secondary_currency((float)$amount_due, $secondaryCurrency) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <th>Change Due</th>
                                    <td><?= to_currency($paymentChangeDue ?? 0) ?></td>
                                </tr>
                                <?php if ($showCustomerDisplay): ?>
                                    <tr>
                                        <th>Change Due <?= esc($customerDisplayCurrencyLabel) ?></th>
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
        localStorage.setItem('customerDisplayOpen', '1');

        let lastDirtyAt = localStorage.getItem('customerDisplayDirtyAt') || '';
        let refreshTimer = null;

        const scheduleRefresh = function(dirtyAt) {
            if (refreshTimer !== null) {
                clearTimeout(refreshTimer);
            }

            refreshTimer = setTimeout(function() {
                if (localStorage.getItem('customerDisplayOpen') !== '1') {
                    return;
                }

                if (localStorage.getItem('customerDisplayDirtyAt') === dirtyAt) {
                    window.location.reload();
                }
            }, 700);
        };

        const checkForRefresh = function() {
            const dirtyAt = localStorage.getItem('customerDisplayDirtyAt') || '';
            if (dirtyAt !== '' && dirtyAt !== lastDirtyAt) {
                lastDirtyAt = dirtyAt;
                scheduleRefresh(dirtyAt);
            }
        };

        window.addEventListener('storage', function(event) {
            if (event.key === 'customerDisplayDirtyAt') {
                checkForRefresh();
            }
        });

        setInterval(checkForRefresh, 500);

        window.addEventListener('beforeunload', function() {
            localStorage.removeItem('customerDisplayOpen');
        });
    </script>

</body>
</html>


