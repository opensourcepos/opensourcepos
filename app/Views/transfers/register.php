<?php
/**
 * @var string $controller_name
 * @var array $stock_locations
 * @var bool $show_stock_locations
 * @var int $stock_source
 * @var int $stock_destination
 * @var array $cart
 * @var float $total
 * @var string $comment
 * @var array $config
 */
?>

<?= view('partial/header') ?>

<?php
if (isset($error)) {
    echo '<div class="alert alert-dismissible alert-danger">' . esc($error) . '</div>';
}

if (!empty($warning)) {
    echo '<div class="alert alert-dismissible alert-warning">' . esc($warning) . '</div>';
}

if (isset($success)) {
    echo '<div class="alert alert-dismissible alert-success">' . esc($success) . '</div>';
}
?>

<div id="register_wrapper">

    <!-- Top register controls -->

    <?= form_open("$controller_name/changeLocation", ['id' => 'location_form', 'class' => 'form-horizontal panel panel-default']) ?>

    <div class="panel-body form-group">
        <ul>
            <li class="pull-left first_li">
                <label class="control-label"><?= lang('Transfers.stock_source') ?></label>
            </li>
            <li class="pull-left">
                <?= form_dropdown('stock_source', $stock_locations, $stock_source, ['onchange' => "$('#location_form').submit();", 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit']) ?>
            </li>
            <li class="pull-left">
                <label class="control-label"><?= lang('Transfers.stock_destination') ?></label>
            </li>
            <li class="pull-left">
                <?= form_dropdown('stock_destination', $stock_locations, $stock_destination, ['onchange' => "$('#location_form').submit();", 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit']) ?>
            </li>
        </ul>
    </div>

    <?= form_close() ?>

    <?= form_open("$controller_name/add", ['id' => 'add_item_form', 'class' => 'form-horizontal panel panel-default']) ?>

    <div class="panel-body form-group">
        <ul>
            <li class="pull-left first_li">
                <label for="item" class="control-label">
                    <?= lang('Transfers.find_or_scan_item') ?>
                </label>
            </li>

            <li class="pull-left">
                <?= form_input(['name' => 'item', 'id' => 'item', 'class' => 'form-control input-sm', 'size' => '50', 'tabindex' => '1']) ?>
            </li>

            <li class="pull-right">
                <button id="new_item_button" class="btn btn-info btn-sm pull-right modal-dlg" data-btn-submit="<?= lang('Common.submit') ?>" data-btn-new="<?= lang('Common.new') ?>" data-href="<?= "items/view" ?>" title="<?= lang('Sales.new_item') ?>">
                    <span class="glyphicon glyphicon-tag">&nbsp;</span><?= lang('Sales.new_item') ?>
                </button>
            </li>
        </ul>
    </div>

    <?= form_close() ?>

    <!-- Transfer Items List -->

    <table class="sales_table_100" id="register">
        <thead>
            <tr>
                <th style="width: 5%;"><?= lang('Common.delete') ?></th>
                <th style="width: 15%;"><?= lang('Sales.item_number') ?></th>
                <th style="width: 45%;"><?= lang('Transfers.item_name') ?></th>
                <th style="width: 15%;"><?= lang('Sales.quantity') ?></th>
                <th style="width: 10%;"><?= lang('Sales.stock') ?></th>
                <th style="width: 10%;"><?= lang('Transfers.update') ?></th>
            </tr>
        </thead>

        <tbody id="cart_contents">
            <?php if (count($cart) == 0) { ?>
                <tr>
                    <td colspan="6">
                        <div class="alert alert-dismissible alert-info"><?= lang('Sales.no_items_in_cart') ?></div>
                    </td>
                </tr>
                <?php
            } else {
                foreach (array_reverse($cart, true) as $line => $item) {
                ?>

                    <?= form_open("$controller_name/editItem/$line", ['class' => 'form-horizontal', 'id' => "cart_$line"]) ?>

                    <tr>
                        <td><?= anchor("$controller_name/deleteItem/$line", '<span class="glyphicon glyphicon-trash"></span>') ?></td>
                        <td><?= esc($item['item_number']) ?></td>
                        <td style="text-align: center;">
                            <?= esc($item['name']) ?><br>
                            <?= '[' . to_quantity_decimals($item['in_stock']) . ' in ' . esc($item['stock_name']) . ']' ?>
                            <?= form_hidden('location', (string)$item['item_location']) ?>
                        </td>

                        <td>
                            <?= form_input(['name' => 'quantity', 'class' => 'form-control input-sm', 'value' => to_quantity_decimals($item['quantity']), 'onClick' => 'this.select();']) ?>
                        </td>
                        <td>
                            <?= to_quantity_decimals($item['in_stock']) ?>
                        </td>
                        <td>
                            <a href="javascript:$('#<?= esc("cart_$line", 'js') ?>').submit();" title=<?= lang('Transfers.update') ?>>
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

<!-- Overall Transfer -->

<div id="overall_sale" class="panel panel-default">
    <div class="panel-body">
        <?php if (count($cart) > 0) { ?>
            <?= form_open("$controller_name/complete", ['id' => 'finish_transfer_form', 'class' => 'form-horizontal']) ?>

            <div class="form-group form-group-sm">
                <label id="comment_label" for="comment"><?= lang('Common.comments') ?></label>
                <?= form_textarea([
                    'name'  => 'comment',
                    'id'    => 'comment',
                    'class' => 'form-control input-sm',
                    'value' => $comment,
                    'rows'  => '4'
                ]) ?>

                <div class="btn btn-sm btn-danger pull-left" id="cancel_transfer_button">
                    <span class="glyphicon glyphicon-remove">&nbsp;</span><?= lang('Transfers.cancel_transfer') ?>
                </div>
                <div class="btn btn-sm btn-success pull-right" id="finish_transfer_button">
                    <span class="glyphicon glyphicon-ok">&nbsp;</span><?= lang('Transfers.complete_transfer') ?>
                </div>
            </div>

            <?= form_close() ?>
        <?php } ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $("#item").autocomplete({
            source: '<?= esc("$controller_name/stockItemSearch") ?>',
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

        $('#item').keypress(function(e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                $("#add_item_form").submit();
            }
        });

        $("#finish_transfer_button").click(function(e) {
            e.preventDefault();
            if (confirm("<?= lang('Transfers.confirm_finish_transfer') ?>")) {
                $("#finish_transfer_form").submit();
            }
        });

        $("#cancel_transfer_button").click(function(e) {
            e.preventDefault();
            if (confirm("<?= lang('Transfers.confirm_cancel_transfer') ?>")) {
                window.location = '<?= site_url("$controller_name") ?>';
            }
        });

        $("#comment").change(function() {
            $.post('<?= site_url("$controller_name/setComment") ?>', {
                comment: $('#comment').val()
            });
        });
    });
</script>

<?= view('partial/footer') ?>
