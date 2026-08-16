<?php

use App\Models\Rma;

/**
 * @var array $rmas
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
    <a class="btn btn-info btn-sm pull-right" href="<?= site_url('rmas/new') ?>" title="<?= lang('Rmas.new_rma') ?>">
        <span class="glyphicon glyphicon-share-alt">&nbsp;</span><?= lang('Rmas.new_rma') ?>
    </a>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= lang('Rmas.list_title') ?></h3>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="rma_table">
                <thead>
                    <tr>
                        <th><?= lang('Rmas.rma_id') ?></th>
                        <th><?= lang('Rmas.rma_time') ?></th>
                        <th><?= lang('Rmas.rma_type') ?></th>
                        <th><?= lang('Rmas.location') ?></th>
                        <th><?= lang('Rmas.supplier') ?></th>
                        <th><?= lang('Rmas.customer') ?></th>
                        <th><?= lang('Rmas.sale') ?></th>
                        <th><?= lang('Rmas.resolution') ?></th>
                        <th><?= lang('Common.comments') ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($rmas)) { ?>
                    <tr>
                        <td colspan="10">
                            <div class="alert alert-dismissible alert-info"><?= lang('Rmas.no_rmas') ?></div>
                        </td>
                    </tr>
                <?php } else { ?>
                    <?php foreach ($rmas as $rma) {
                        $is_client = (int) $rma['rma_type'] === Rma::TYPE_CLIENT;
                        $is_stock  = (int) $rma['rma_type'] === Rma::TYPE_STOCK;
                        ?>
                        <tr>
                            <td><?= $rma['rma_id'] ?></td>
                            <td><?= to_datetime(strtotime($rma['rma_time'])) ?></td>
                            <td><?= $is_client ? lang('Rmas.type_client') : lang('Rmas.type_stock') ?></td>
                            <td><?= esc($rma['location_name']) ?></td>
                            <td><?= $is_stock ? esc($rma['supplier_name'] ?? '') : '' ?></td>
                            <td><?= $is_client ? esc($rma['customer_name'] ?? '') : '' ?></td>
                            <td><?= $is_client && ! empty($rma['sale_id']) ? anchor("sales/receipt/{$rma['sale_id']}", 'POS ' . (int) $rma['sale_id'], ['target' => '_blank']) : '' ?></td>
                            <td>
                                <?php if ($rma['resolution'] === null) { ?>
                                    <span class="text-warning"><strong><?= lang('Rmas.unresolved') ?></strong></span>
                                <?php } else { ?>
                                    <span class="text-success"><strong><?= esc($resolution_labels[$rma['resolution']] ?? $rma['resolution']) ?></strong></span>
                                <?php } ?>
                            </td>
                            <td><?= esc($rma['comment']) ?></td>
                            <td>
                                <?= anchor("rmas/detail/{$rma['rma_id']}", lang('Rmas.open'), ['class' => 'btn btn-default btn-xs']) ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= view('partial/footer') ?>