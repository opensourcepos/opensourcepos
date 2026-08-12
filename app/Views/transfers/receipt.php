<?php
/**
 * @var string $transfer_id
 * @var string $transaction_time
 * @var string $employee
 * @var string $location_from_name
 * @var string $location_to_name
 * @var string $comment
 * @var array $cart
 * @var array $config
 */
?>

<?= view('partial/header') ?>

<?php
if (isset($error_message)) {
    echo '<div class="alert alert-dismissible alert-danger">' . esc($error_message) . '</div>';
    exit;
}

echo view('partial/print_receipt', ['print_after_sale' => $print_after_sale ?? false, 'selected_printer' => 'receipt_printer']) ?>

<div class="print_hide" id="control_buttons" style="text-align: right;">
    <a href="javascript:printdoc();">
        <div class="btn btn-info btn-sm" id="show_print_button"><?= '<span class="glyphicon glyphicon-print">&nbsp;</span>' . lang('Common.print') ?></div>
    </a>
    <?= anchor("transfers", '<span class="glyphicon glyphicon-save">&nbsp;</span>' . lang('Transfers.register'), ['class' => 'btn btn-info btn-sm', 'id' => 'show_transfers_button']) ?>
</div>

<div id="receipt_wrapper">
    <div id="receipt_header">
        <?php if ($config['company_logo'] != '') { ?>
            <div id="company_name">
                <img id="image" src="<?= base_url('uploads/' . esc($config['company_logo'], 'url')) ?>" alt="company_logo">
            </div>
        <?php } ?>

        <?php if ($config['receipt_show_company_name']) { ?>
            <div id="company_name"><?= esc($config['company']) ?></div>
        <?php } ?>

        <div id="company_address"><?= nl2br(esc($config['address'])) ?></div>
        <div id="company_phone"><?= esc($config['phone']) ?></div>
        <div id="sale_receipt"><?= lang('Transfers.receipt') ?></div>
        <div id="sale_time"><?= esc($transaction_time) ?></div>
    </div>

    <div id="receipt_general_info">
        <div id="sale_id"><?= lang('Transfers.id') . ": $transfer_id" ?></div>
        <div id="location_from"><?= lang('Transfers.stock_source') . esc(": $location_from_name") ?></div>
        <div id="location_to"><?= lang('Transfers.stock_destination') . esc(": $location_to_name") ?></div>
        <?php if (!empty($comment)) { ?>
            <div id="reference"><?= lang('Common.comments') . esc(": $comment") ?></div>
        <?php } ?>
        <div id="employee"><?= lang('Employees.employee') . esc(": $employee") ?></div>
    </div>

    <table id="receipt_items">
        <tr>
            <th style="width: 40%;"><?= lang('Items.item') ?></th>
            <th style="width: 20%;"><?= lang('Sales.item_number') ?></th>
            <th style="width: 20%;"><?= lang('Sales.quantity') ?></th>
        </tr>

        <?php foreach ($cart as $item) { ?>
            <tr>
                <td><?= esc($item['name']) ?></td>
                <td><?= esc($item['item_number']) ?></td>
                <td><?= to_quantity_decimals($item['quantity']) ?></td>
            </tr>
        <?php } ?>
    </table>

    <div id="sale_return_policy">
        <?= nl2br(esc($config['return_policy'])) ?>
    </div>

    <div id="barcode">
        <?= $transfer_id ?>
    </div>
</div>

<?= view('partial/footer') ?>
