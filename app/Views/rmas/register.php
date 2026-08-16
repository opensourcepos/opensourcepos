<?php

use App\Models\Rma;

/**
 * @var string   $controller_name
 * @var int      $rma_type
 * @var array    $stock_locations
 * @var int      $location
 * @var int|null $supplier_id
 * @var int|null $customer_id
 * @var array    $suppliers
 * @var array    $customers
 * @var int|null $sale_id
 * @var array    $sale_items
 * @var array    $cart
 * @var string   $comment
 * @var array    $config
 */
?>

<?= view('partial/header') ?>

<?php
if (isset($error)) {
    echo '<div class="alert alert-dismissible alert-danger">' . esc($error) . '</div>';
}

if (! empty($warning)) {
    echo '<div class="alert alert-dismissible alert-warning">' . esc($warning) . '</div>';
}

if (isset($success)) {
    echo '<div class="alert alert-dismissible alert-success">' . esc($success) . '</div>';
}
?>

<div id="register_wrapper">

    <!-- Top register controls -->

    <?= form_open('rmas/changeType', ['id' => 'type_form', 'class' => 'form-horizontal panel panel-default']) ?>

    <div class="panel-body form-group">
        <ul>
            <li class="pull-left first_li">
                <label class="control-label"><?= lang('Rmas.rma_type') ?></label>
            </li>
            <li class="pull-left">
                <?= form_dropdown('rma_type', [
                    Rma::TYPE_STOCK  => lang('Rmas.type_stock'),
                    Rma::TYPE_CLIENT => lang('Rmas.type_client'),
                ], $rma_type, ['onchange' => "$('#type_form').submit();", 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit']) ?>
            </li>
            <li class="pull-left">
                <span class="help-inline text-muted" style="margin-left: 15px;">
                    <?= $rma_type === Rma::TYPE_CLIENT ? lang('Rmas.help_client_unit') : lang('Rmas.help_stock_unit') ?>
                </span>
            </li>
        </ul>
    </div>

    <?= form_close() ?>

    <?= form_open('rmas/changeLocation', ['id' => 'location_form', 'class' => 'form-horizontal panel panel-default']) ?>

    <div class="panel-body form-group">
        <ul>
            <li class="pull-left first_li">
                <label class="control-label"><?= lang('Rmas.location') ?></label>
            </li>
            <li class="pull-left">
                <?= form_dropdown('location', $stock_locations, $location, ['onchange' => "$('#location_form').submit();", 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit']) ?>
            </li>
        </ul>
    </div>

    <?= form_close() ?>

    <?php if ($rma_type === Rma::TYPE_STOCK) { ?>
        <?= form_open('rmas/selectSupplier', ['id' => 'supplier_form', 'class' => 'form-horizontal panel panel-default']) ?>
        <div class="panel-body form-group">
            <ul>
                <li class="pull-left first_li">
                    <label for="supplier" class="control-label"><?= lang('Rmas.supplier') ?></label>
                </li>
                <li class="pull-left">
                    <?= form_dropdown('supplier', $suppliers, $supplier_id, ['onchange' => "$('#supplier_form').submit();", 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit', 'data-live-search' => 'true']) ?>
                </li>
            </ul>
        </div>
        <?= form_close() ?>
    <?php } else { ?>
        <?= form_open('rmas/selectCustomer', ['id' => 'customer_form', 'class' => 'form-horizontal panel panel-default']) ?>
        <div class="panel-body form-group">
            <ul>
                <li class="pull-left first_li">
                    <label for="customer" class="control-label"><?= lang('Sales.select_customer') ?></label>
                </li>
                <li class="pull-left">
                    <?= form_dropdown('customer', $customers, $customer_id, ['onchange' => "$('#customer_form').submit();", 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit', 'data-live-search' => 'true']) ?>
                </li>
            </ul>
        </div>
        <?= form_close() ?>
    <?php } ?>

    <?php if ($rma_type === Rma::TYPE_CLIENT) { ?>
        <div class="alert alert-info">
            <?= lang('Rmas.scan_receipt_hint') ?>
        </div>

        <?php if (! empty($sale_id) && ! empty($sale_items)) { ?>
            <div class="panel panel-default" id="sale_items_panel">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <?= lang('Rmas.sale_id') ?>: <?= 'POS ' . esc($sale_id) ?>
                    </h3>
                </div>
                <div class="panel-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th><?= lang('Rmas.item_name') ?></th>
                                <th><?= lang('Rmas.description') ?></th>
                                <th><?= lang('Common.quantity') ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sale_items as $sale_item) { ?>
                                <tr>
                                    <td><?= esc($sale_item['name'] ?? '') ?></td>
                                    <td><?= esc($sale_item['description'] ?? '') ?></td>
                                    <td><?= to_quantity_decimals((float) $sale_item['quantity_purchased']) ?></td>
                                    <td>
                                        <?= anchor(
                                            "rmas/addSaleItem/{$sale_item['line']}",
                                            '<span class="glyphicon glyphicon-plus"></span> ' . lang('Rmas.add_to_rma'),
                                            ['class' => 'btn btn-sm btn-info'],
                                        ) ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php } ?>
    <?php } ?>

    <?= form_open('rmas/add', ['id' => 'add_item_form', 'class' => 'form-horizontal panel panel-default']) ?>

    <div class="panel-body form-group">
        <ul>
            <li class="pull-left first_li">
                <label for="item" class="control-label">
                    <?= lang('Rmas.find_or_scan_item') ?>
                </label>
            </li>

            <li class="pull-left">
                <?= form_input(['name' => 'item', 'id' => 'item', 'class' => 'form-control input-sm', 'size' => '50', 'tabindex' => '1', 'autocomplete' => 'off']) ?>
            </li>
        </ul>
    </div>

    <?= form_close() ?>

    <!-- RMA Items List -->

    <table class="sales_table_100" id="register">
        <thead>
            <tr>
                <th style="width: 5%;"><?= lang('Common.delete') ?></th>
                <th style="width: 10%;"><?= lang('Sales.item_number') ?></th>
                <th style="width: 30%;"><?= lang('Rmas.item_name') ?></th>
                <th style="width: 10%;"><?= lang('Rmas.serial_number') ?></th>
                <th style="width: 20%;"><?= lang('Rmas.item_issue') ?></th>
                <th style="width: 8%;"><?= lang('Sales.quantity') ?></th>
                <th style="width: 7%;"><?= lang('Sales.stock') ?></th>
                <th style="width: 10%;"><?= lang('Transfers.update') ?></th>
            </tr>
        </thead>

        <tbody id="cart_contents">
            <?php if (count($cart) === 0) { ?>
                <tr>
                    <td colspan="8">
                        <div class="alert alert-dismissible alert-info"><?= lang('Sales.no_items_in_cart') ?></div>
                    </td>
                </tr>
                <?php
            } else {
                foreach (array_reverse($cart, true) as $line => $item) {
                    ?>

                    <?= form_open("rmas/editItem/{$line}", ['class' => 'form-horizontal', 'id' => "cart_{$line}"]) ?>

                    <tr>
                        <td><?= anchor("rmas/deleteItem/{$line}", '<span class="glyphicon glyphicon-trash"></span>') ?></td>
                        <td><?= esc($item['item_number']) ?></td>
                        <td style="text-align: center;">
                            <?= esc($item['name']) ?><br>
                            <?= '[' . to_quantity_decimals($item['in_stock']) . ' in ' . esc($item['stock_name']) . ']' ?>
                            <?= form_hidden('location', (string) $item['item_location']) ?>
                        </td>

                        <td>
                            <?= form_input(['name' => 'serial_number', 'class' => 'form-control input-sm', 'value' => ! empty($item['serial_number']) ? $item['serial_number'] : '']) ?>
                        </td>
                        <td>
                            <?= form_input(['name' => 'issue', 'class' => 'form-control input-sm', 'value' => ! empty($item['issue']) ? $item['issue'] : '']) ?>
                        </td>
                        <td>
                            <?= form_input(['name' => 'quantity', 'class' => 'form-control input-sm', 'value' => to_quantity_decimals($item['quantity']), 'onClick' => 'this.select();']) ?>
                        </td>
                        <td>
                            <?= to_quantity_decimals($item['in_stock']) ?>
                        </td>
                        <td>
                            <a href="javascript:$('#<?= esc("cart_{$line}", 'js') ?>').submit();" title=<?= lang('Transfers.update') ?>>
                                <span class="glyphicon glyphicon-refresh"></span>
                            </a>
                        </td>
                    </tr>

                    <?= form_close() ?>

            <?php
                }
            }
?>
        </tbody>
    </table>
</div>

<!-- Overall RMA -->

<div id="overall_sale" class="panel panel-default">
    <div class="panel-body">
        <?php if (count($cart) > 0) { ?>
            <?= form_open('rmas/submit', ['id' => 'submit_rma_form', 'class' => 'form-horizontal']) ?>

            <div class="form-group form-group-sm">
                <label id="comment_label" for="comment"><?= lang('Common.comments') ?></label>
                <?= form_textarea([
                    'name'  => 'comment',
                    'id'    => 'comment',
                    'class' => 'form-control input-sm',
                    'value' => $comment,
                    'rows'  => '4',
                ]) ?>

                <div class="btn btn-sm btn-danger pull-left" id="cancel_rma_button">
                    <span class="glyphicon glyphicon-remove">&nbsp;</span><?= lang('Rmas.cancel_rma') ?>
                </div>
                <div class="btn btn-sm btn-success pull-right" id="submit_rma_button">
                    <span class="glyphicon glyphicon-ok">&nbsp;</span><?= lang('Rmas.submit_rma') ?>
                </div>
            </div>

            <?= form_close() ?>
        <?php } ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $("#item").autocomplete({
            source: '<?= esc('rmas/stockItemSearch') ?>',
            minChars: 0,
            delay: 10,
            autoFocus: false,
            select: function(a, ui) {
                $(this).val(ui.item.value);
                $("#add_item_form").submit();
                return false;
            }
        });

        $('#item').focus();

        $('#item').keydown(function(e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                $("#add_item_form").submit();
                return false;
            }
        });

        // A pasted sale id ("70", "POS 70", or an invoice number) may not
        // trigger Enter reliably, so submit when the field blurs with
        // content. Numeric/barcode values are safe to auto-submit.
        $('#item').change(function() {
            const value = $(this).val().trim();

            if (value !== '' && (/^\d+$/.test(value) || /^POS \d+/i.test(value))) {
                $("#add_item_form").submit();
            }
        });

        $("#submit_rma_button").click(function(e) {
            e.preventDefault();
            if (confirm("<?= lang('Rmas.confirm_submit_rma') ?>")) {
                $("#submit_rma_form").submit();
            }
        });

        $("#cancel_rma_button").click(function(e) {
            e.preventDefault();
            if (confirm("<?= lang('Rmas.confirm_cancel_rma') ?>")) {
                window.location = '<?= site_url('rmas') ?>';
            }
        });

        $("#comment").change(function() {
            $.post('<?= site_url('rmas/setComment') ?>', {
                comment: $('#comment').val()
            });
        });
    });
</script>

<?= view('partial/footer') ?>