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

helper('url');

$secondary_currency_enabled = (($config['secondary_currency_enabled'] ?? false) == 1);
$show_secondary = $secondary_currency_enabled && !empty($rate) && $rate > 0;
$company_lines = preg_split("/\r\n|\r|\n/", (string)($config['company'] ?? '')) ?: [];
$company_name = array_shift($company_lines) ?? '';
$company_details = trim(implode("\n", $company_lines));
$secondary_currency_symbol = trim((string)($config['secondary_currency_symbol'] ?? ''));
$secondary_currency_code = trim((string)($config['secondary_currency_code'] ?? ''));
$original_currency_symbol = trim((string)($config['currency_symbol'] ?? ''));
$secondary_currency_label = $secondary_currency_code !== '' ? $secondary_currency_code : ($secondary_currency_symbol !== '' ? $secondary_currency_symbol : 'LBP');
$original_currency_label = $original_currency_symbol !== '' ? $original_currency_symbol : '$';
$cart_has_secondary = $show_secondary;
$cart_colspan = $cart_has_secondary ? 6 : 5;
$cart_item_width = $cart_has_secondary ? 32 : 44;
$cart_price_width = $cart_has_secondary ? 18 : 0;
$cart_original_width = $cart_has_secondary ? 18 : 26;
$cart_quantity_width = $cart_has_secondary ? 12 : 10;
$cart_discount_width = $cart_has_secondary ? 10 : 9;
$cart_total_width = $cart_has_secondary ? 10 : 11;
?>

<!doctype html>
<html lang="<?= esc(service('request')->getLocale()) ?>">
<head>
    <meta charset="utf-8">
    <title><?= lang('Sales.second_display') ?></title>
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('images/favicon.ico') ?>">
    <link rel="stylesheet" href="<?= base_url('resources/bootswatch/' . (empty($config['theme']) ? 'flatly' : esc($config['theme'])) . '/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('resources/opensourcepos-8e34d6a398.min.css') ?>">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            background: #f8f8f8;
            color: #333;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        }

        body {
            width: 100%;
            overflow: hidden;
        }

        .second-display-header {
            background: #1f3143;
            color: #fff;
            text-align: center;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.02em;
            padding: 6px 12px;
            border-bottom: 1px solid #102131;
        }

        .second-display-shell {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 12px 18px 18px;
            box-sizing: border-box;
        }

        .second-display-company {
            text-align: center;
            margin-bottom: 18px;
        }

        .second-display-company img {
            display: block;
            margin: 0 auto 6px;
            max-height: 84px;
            max-width: 240px;
        }

        .second-display-company .company-name {
            font-size: 20px;
            font-weight: 600;
            line-height: 1.2;
            margin-top: 12px;
        }

        .second-display-company .company-details {
            font-size: 13px;
            line-height: 1.35;
            white-space: pre-line;
        }

        .second-display-company .company-phone {
            font-size: 13px;
            line-height: 1.35;
            margin-top: 4px;
        }

        .second-display-main-row {
            display: flex;
            gap: 14px;
            align-items: flex-start;
            margin-top: 6px;
        }

        .second-display-cart-column {
            flex: 1 1 auto;
            min-width: 0;
        }

        .second-display-summary-column {
            flex: 0 0 320px;
            width: 320px;
        }

        .second-display-summary-panel,
        .second-display-info-panel,
        .second-display-items-panel {
            margin-bottom: 0;
        }

        .second-display-summary-panel .panel-heading,
        .second-display-info-panel .panel-heading,
        .second-display-items-panel .panel-heading {
            font-weight: 600;
        }

        .second-display-summary-panel .table,
        .second-display-info-table {
            margin-bottom: 0;
            font-size: 13px;
        }

        .second-display-summary-panel .table > tbody > tr > th,
        .second-display-info-table > tbody > tr > th {
            background: #f8fbfd;
            width: 56%;
            font-weight: 700;
        }

        .second-display-summary-panel .table > tbody > tr > td,
        .second-display-info-table > tbody > tr > td {
            width: 44%;
            text-align: right;
            white-space: nowrap;
            font-weight: 600;
        }

        .second-display-summary-panel .rate-row th,
        .second-display-summary-panel .rate-row td {
            color: #c00000;
        }

        .second-display-summary-panel .summary-section-row th {
            background: #eaf2f8;
            color: #1f3b5b;
            font-weight: 700;
        }

        .second-display-summary-panel .summary-subtable {
            width: 100%;
        }

        .second-display-summary-panel .summary-subtable > tbody > tr > th {
            background: #fdfefe;
            font-weight: 600;
        }

        .second-display-summary-panel .summary-subtable > tbody > tr > td {
            font-weight: 600;
        }

        .register-wrap {
            width: 100%;
        }

        #register {
            width: 100%;
            margin: 0;
            table-layout: fixed;
            background: #fff;
        }

        #register th,
        #register td {
            text-align: center;
            vertical-align: middle;
            padding: 6px 5px;
            word-wrap: break-word;
        }

        #register thead th {
            font-size: 12px;
            font-weight: 600;
            color: #333;
        }

        #register tbody td {
            font-size: 15px;
        }

        #register tbody td.item-name-cell {
            font-size: 16px;
            text-align: left;
        }

        #register tbody td.price-cell {
            font-size: 15px;
        }

        #register tbody td.serial-cell {
            font-size: 12px;
            color: #2F4F4F;
        }

        .second-display-summary-panel .table > tbody > tr > th,
        .second-display-info-table > tbody > tr > th {
            border-top: 1px solid #e5e5e5;
        }

        .second-display-summary-panel .table > tbody > tr > td,
        .second-display-info-table > tbody > tr > td {
            border-top: 1px solid #e5e5e5;
        }

        .second-display-summary-panel .panel-body,
        .second-display-info-panel .panel-body,
        .second-display-items-panel .panel-body {
            padding: 12px 15px;
        }

        .second-display-summary-column .panel-body {
            padding-top: 8px;
        }

        .second-display-summary-column .customer-name-value,
        .second-display-summary-column .giftcard-value,
        .second-display-summary-column .reward-value {
            text-align: right;
        }

        .second-display-footer {
            margin-top: 14px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="second-display-header">Open Source Point of Sale</div>
    <div class="second-display-shell">
        <div class="second-display-company">
            <?php if (!empty($config['company_logo'])) { ?>
                <img src="<?= base_url('uploads/' . esc($config['company_logo'], 'url')) ?>" alt="company_logo">
            <?php } ?>
            <div class="company-name"><?= esc($company_name) ?></div>
            <div class="company-phone">Phone: <?= esc((string)($config['phone'] ?? '')) ?></div>
            <?php if ($company_details !== '') { ?>
                <div class="company-details"><?= nl2br(esc($company_details)) ?></div>
            <?php } ?>
        </div>

        <div class="second-display-main-row">
            <div class="second-display-cart-column">
                <div class="register-wrap">
                    <div class="panel panel-default second-display-items-panel">
                        <div class="panel-heading">Items</div>
                        <div class="panel-body table-responsive">
                            <table class="table table-striped table-condensed" id="register">
                                <thead>
                                    <tr>
                                        <th style="width: <?= $cart_item_width ?>%;"><?= lang('Sales.item_name') ?></th>
                                        <?php if ($cart_has_secondary) { ?>
                                            <th style="width: <?= $cart_price_width ?>%;"><?= 'Price (' . esc($secondary_currency_label) . ')' ?></th>
                                        <?php } ?>
                                        <th style="width: <?= $cart_original_width ?>%;"><?= 'Price (' . esc($original_currency_label) . ')' ?></th>
                                        <th style="width: <?= $cart_quantity_width ?>%;"><?= lang('Sales.quantity') ?></th>
                                        <th style="width: <?= $cart_discount_width ?>%;"><?= lang('Sales.discount') ?></th>
                                        <th style="width: <?= $cart_total_width ?>%;"><?= lang('Sales.total') ?></th>
                                    </tr>
                                </thead>
                                <tbody id="cart_contents">
                                    <?php if (count($cart) == 0) { ?>
                                        <tr>
                                            <td colspan="<?= $cart_colspan ?>">
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
                                                <?php if ($cart_has_secondary) { ?>
                                                    <td class="price-cell">
                                                        <?= to_scnd_currency((float)$item['price'], $rate, 0, $secondary_currency_symbol, $secondary_currency_code) ?>
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
                                                <td colspan="<?= $cart_has_secondary ? 3 : 2 ?>"></td>
                                                <td class="serial-cell">
                                                    <?= $item['is_serialized'] == 1 ? lang('Sales.serial') : '' ?>
                                                </td>
                                                <td colspan="<?= $cart_has_secondary ? 2 : 2 ?>" class="serial-cell">
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
            <div class="second-display-summary-column">
                <div class="panel panel-primary second-display-summary-panel">
                    <div class="panel-heading">Summary</div>
                    <div class="panel-body">
                        <table class="table table-condensed summary-subtable">
                            <tbody>
                                <tr>
                                    <th>Total</th>
                                    <td><?= to_currency($total) ?></td>
                                </tr>
                                <?php if ($show_secondary): ?>
                                    <tr>
                                        <th>Total <?= esc($secondary_currency_label) ?></th>
                                        <td><?= to_scnd_currency((float)$total, $rate, 0, $secondary_currency_symbol, $secondary_currency_code) ?></td>
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
                                    <td class="customer-name-value"><?= esc($customer_name ?? lang('Sales.walk_in_customer')) ?></td>
                                </tr>
                                <tr>
                                    <th>Gift Card Balance</th>
                                    <td class="giftcard-value"><?= esc((string)($giftcard_remainder ?? '0')) ?></td>
                                </tr>
                                <tr>
                                    <th>Loyalty Reward Points</th>
                                    <td class="reward-value"><?= esc((string)($customer_reward_points ?? 0)) ?></td>
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
                                <?php if ($show_secondary): ?>
                                    <tr>
                                        <th>Amount Due <?= esc($secondary_currency_label) ?></th>
                                        <td><?= to_scnd_currency((float)$amount_due, $rate, 0, $secondary_currency_symbol, $secondary_currency_code) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <th>Change Due</th>
                                    <td><?= to_currency($payment_change_due ?? 0) ?></td>
                                </tr>
                                <?php if ($show_secondary): ?>
                                    <tr>
                                        <th>Change Due <?= esc($secondary_currency_label) ?></th>
                                        <td><?= to_scnd_currency((float)($payment_change_due ?? 0), $rate, 0, $secondary_currency_symbol, $secondary_currency_code) ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="second-display-footer"></div>
    </div>

    <script>
        sessionStorage.setItem('secondDisplayOpen', '1');
        window.addEventListener('beforeunload', function() {
            sessionStorage.removeItem('secondDisplayOpen');
        });
    </script>

</body>
</html>
