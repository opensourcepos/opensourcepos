<?php
/**
 * @var string $customer_info
 * @var string $company_info
 * @var string $work_order_number
 * @var string $transaction_date
 * @var string $amount_due
 * @var float $total
 * @var array $cart
 * @var float $tax_exclusive_subtotal
 * @var array $taxes
 * @var array $config
 */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="<?= $this->request->getLocale() ?>">
<head title="<?= lang('Sales.work_order') ?>">
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="<?= base_url('css/invoice_email.css')  ?>"/>
</head>

<body>
<?php
    if(isset($error_message))
    {
        echo "<div class='alert alert-dismissible alert-danger'>$error_message</div>";
        exit;
    }
?>

<div id="page-wrap">
    <div id="header"><?= lang('Sales.work_order') ?></div>
    <table id="info">
        <tr>
            <td id="logo">
                <?php
                if($config['company_logo'] != '')
                {
                ?>
                    <img id="image" src="<?= esc('uploads/' . $config['company_logo'], 'url') ?>" alt="company_logo" />
                <?php
                }
                ?>
            </td>
            <td id="customer-title">
                <pre><?php if(isset($customer)) { echo esc($customer_info); } ?></pre>
            </td>
        </tr>
        <tr>
            <td id="company-title">
                <pre><?= esc($config['company']) ?></pre>
                <pre><?= esc($company_info) ?></pre>
            </td>
            <td id="meta">
                <table align="right">
                    <tr>
                        <td class="meta-head"><?= lang('Sales.work_order_number') ?> </td>
                        <td><?= esc($work_order_number) ?></td>
                    </tr>
                    <tr>
                        <td class="meta-head"><?= lang('Common.date') ?></td>
                        <td><?= esc($transaction_date) ?></td>
                    </tr>
                    <?php
                    if($amount_due > 0)
                    {
                    ?>
                        <tr>
                            <td class="meta-head"><?= lang('Sales.amount_due') ?></td>
                            <td class="due"><?= to_currency($total) ?></td>
                        </tr>
                    <?php
                    }
                    ?>
                </table>
            </td>
        </tr>
    </table>

    <table id="items">
        <tr>
            <th><?= lang('Sales.item_number') ?></th>
            <th><?= lang('Sales.item_name') ?></th>
            <th><?= lang('Sales.quantity') ?></th>
            <th><?= lang('Sales.price') ?></th>
            <th><?= lang('Sales.discount') ?></th>
            <th><?= lang('Sales.total') ?></th>
        </tr>

        <?php
        foreach($cart as $line => $item)
        {
            if($item['print_option'] == PRINT_YES)
            {
            ?>
                <tr class="item-row">
                    <td><?= esc($item['item_number']) ?></td>
                    <td class="item-name"><?= esc($item['name']) ?></td>
                    <td><?= to_quantity_decimals($item['quantity']) ?></td>
                    <td><?= to_currency($item['price']) ?></td>
                    <td><?= ($item['discount_type'] == FIXED) ? to_currency($item['discount']) : to_decimals($item['discount']) . '%' ?></td>
                    <td class="total-line"><?= to_currency($item['discounted_total']) ?></td>
                </tr>
            <?php
            }
        }
        ?>

        <tr>
            <td colspan="6" align="center"><?= '&nbsp;' ?></td>
        </tr>

        <tr>
            <td colspan="3" class="blank"> </td>
            <td colspan="2" class="total-line"><?= lang('Sales.sub_total') ?></td>
            <td id="subtotal" class="total-value"><?= to_currency($tax_exclusive_subtotal) ?></td>
        </tr>

        <?php
        foreach($taxes as $name => $value)
        {
        ?>
            <tr>
                <td colspan="3" class="blank"> </td>
                <td colspan="2" class="total-line"><?= esc($name) ?></td>
                <td id="taxes" class="total-value"><?= to_currency_tax($value) ?></td>
            </tr>
        <?php
        }
        ?>

        <tr>
            <td colspan="3" class="blank"> </td>
            <td colspan="2" class="total-line"><?= lang('Sales.total') ?></td>
            <td id="total" class="total-value"><?= to_currency($total) ?></td>
        </tr>
    </table>

    <div id="terms">
        <div id="sale_return_policy">
            <h5>
                <span style='padding:4%;'><?= empty($comments) ? '' : lang('Sales.comments') . esc(": $comments") ?></span>
            </h5>
        </div>
    </div>
</div>

</body>
</html>
