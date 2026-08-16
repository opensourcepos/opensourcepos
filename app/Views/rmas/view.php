<?php

use App\Models\Rma;

/**
 * @var array $rma
 * @var array $items
 */
$resolution_labels = [
    Rma::RESOLUTION_REPLACEMENT   => lang('Rmas.resolution_replacement'),
    Rma::RESOLUTION_CREDIT_MEMO   => lang('Rmas.resolution_credit_memo'),
    Rma::RESOLUTION_REPAIR        => lang('Rmas.resolution_repair'),
    Rma::RESOLUTION_VOID_WARRANTY => lang('Rmas.resolution_void_warranty'),
];
?>

<?= view('partial/header') ?>

<div id="title_bar" class="btn-toolbar">
    <a href="<?= site_url('rmas') ?>" class="btn btn-default btn-sm pull-left">
        <span class="glyphicon glyphicon-arrow-left">&nbsp;</span><?= lang('Rmas.back_to_list') ?>
    </a>
    <?php if ($rma['resolution'] === null) { ?>
        <?= form_open("rmas/deleteRMA/{$rma['rma_id']}", ['class' => 'form-horizontal pull-right', 'id' => 'delete_rma_form']) ?>
        <button type="submit" class="btn btn-sm btn-danger" id="delete_rma_button">
            <span class="glyphicon glyphicon-trash">&nbsp;</span><?= lang('Common.delete') ?>
        </button>
        <?= form_close() ?>
    <?php } ?>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            <?= lang('Rmas.rma') ?> #<?= $rma['rma_id'] ?>
            &nbsp;<span class="<?= $rma['resolution'] === null ? 'text-warning' : 'text-success' ?>"><strong><?= $rma['resolution'] === null ? lang('Rmas.unresolved') : esc($resolution_labels[$rma['resolution']]) ?></strong></span>
        </h3>
    </div>
    <div class="panel-body">
        <dl class="dl-horizontal">
            <dt><?= lang('Rmas.rma_time') ?></dt>
            <dd><?= to_datetime(strtotime($rma['rma_time'])) ?></dd>
<dt><?= lang('Rmas.rma_type') ?></dt>
<dd><?= (int) $rma['rma_type'] === Rma::TYPE_CLIENT ? lang('Rmas.type_client') : lang('Rmas.type_stock') ?></dd>
            <dt><?= lang('Rmas.location') ?></dt>
            <dd><?= esc($rma['location_name']) ?></dd>
            <dt><?= lang('Rmas.employee_name') ?></dt>
            <dd><?= esc($rma['employee_name']) ?></dd>
            <?php if ((int) $rma['rma_type'] === Rma::TYPE_STOCK) { ?>
                <dt><?= lang('Rmas.supplier') ?></dt>
                <dd><?= esc($rma['supplier_name'] ?? '') ?></dd>
            <?php } else { ?>
                <dt><?= lang('Rmas.customer') ?></dt>
                <dd><?= esc($rma['customer_name'] ?? '') ?></dd>
                <?php if (! empty($rma['sale_id'])) { ?>
                    <dt><?= lang('Rmas.sale') ?></dt>
                    <dd><?= anchor("sales/receipt/{$rma['sale_id']}", 'POS ' . (int) $rma['sale_id'], ['target' => '_blank']) ?></dd>
                <?php } ?>
            <?php } ?>
            <dt><?= lang('Common.comments') ?></dt>
            <dd><?= esc($rma['comment']) ?></dd>
            <?php if ($rma['resolution'] !== null) { ?>
                <dt><?= lang('Rmas.resolved_by') ?></dt>
                <dd><?= esc($rma['resolved_name']) ?> <?= lang('Rmas.on') ?> <?= to_datetime(strtotime($rma['resolved_time'])) ?></dd>
            <?php } ?>
        </dl>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= lang('Rmas.rma_items') ?></h3>
    </div>
    <div class="panel-body">
        <?php if (empty($items)) { ?>
            <div class="alert alert-dismissible alert-info"><?= lang('Sales.no_items_in_cart') ?></div>
        <?php } else { ?>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th><?= lang('Sales.item_number') ?></th>
                        <th><?= lang('Rmas.item_name') ?></th>
                        <th><?= lang('Sales.quantity') ?></th>
                        <th><?= lang('Rmas.serial_number') ?></th>
                        <th><?= lang('Rmas.item_issue') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item) { ?>
                        <tr>
                            <td><?= esc($item['item_number']) ?></td>
                            <td><?= esc($item['name']) ?></td>
                            <td><?= to_quantity_decimals($item['quantity']) ?></td>
                            <td><?= esc($item['serial_number'] ?? '') ?></td>
                            <td><?= esc($item['issue'] ?? '') ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</div>

<?php if ($rma['resolution'] === null) { ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><?= lang('Rmas.resolve_title') ?></h3>
        </div>
        <div class="panel-body">
            <?= form_open("rmas/resolve/{$rma['rma_id']}", ['class' => 'form-horizontal', 'id' => 'resolve_form']) ?>
            <div class="form-group">
                <label class="control-label col-xs-2"><?= lang('Rmas.resolution') ?></label>
                <div class="col-xs-4">
                    <?= form_dropdown('resolution', $rma['resolution_options'], null, ['class' => 'form-control input-sm']) ?>
                </div>
            </div>
            <?php if ((int) $rma['rma_type'] === Rma::TYPE_STOCK) { ?>
                <div class="alert alert-info">
                    <?= lang('Rmas.help_resolve_stock') ?>
                </div>
            <?php } else { ?>
                <div class="alert alert-info">
                    <?= lang('Rmas.help_resolve_client') ?>
                </div>
            <?php } ?>
            <div class="form-group">
                <button type="submit" class="btn btn-sm btn-success" id="resolve_button">
                    <span class="glyphicon glyphicon-ok">&nbsp;</span><?= lang('Rmas.resolve') ?>
                </button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
<?php } ?>

<script type="text/javascript">
    $(document).ready(function() {
        $("#resolve_button").click(function(e) {
            e.preventDefault();
            if (confirm("<?= lang('Rmas.confirm_resolve') ?>")) {
                $("#resolve_form").submit();
            }
        });
        $("#delete_rma_button").click(function(e) {
            e.preventDefault();
            if (confirm("<?= lang('Rmas.confirm_delete') ?>")) {
                $("#delete_rma_form").submit();
            }
        });
    });
</script>

<?= view('partial/footer') ?>