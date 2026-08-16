<?php

use App\Models\Requisition;

/**
 * @var array $requisition
 * @var array $items
 * @var bool  $can_approve_source
 * @var bool  $is_admin
 */
$status_labels = [
    Requisition::STATUS_PENDING         => lang('Requisitions.status_pending'),
    Requisition::STATUS_SOURCE_APPROVED => lang('Requisitions.status_source_approved'),
    Requisition::STATUS_APPROVED        => lang('Requisitions.status_approved'),
    Requisition::STATUS_REJECTED        => lang('Requisitions.status_rejected'),
];
?>

<?= view('partial/header') ?>

<div id="title_bar" class="btn-toolbar">
    <a href="<?= site_url('requisitions') ?>" class="btn btn-default btn-sm pull-left">
        <span class="glyphicon glyphicon-arrow-left">&nbsp;</span><?= lang('Requisitions.back_to_list') ?>
    </a>
    <a href="<?= site_url('transfers') ?>" class="btn btn-default btn-sm pull-right">
        <span class="glyphicon glyphicon-transfer">&nbsp;</span><?= lang('Requisitions.go_to_transfers') ?>
    </a>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            <?= lang('Requisitions.requisition') ?> #<?= $requisition['requisition_id'] ?>
            &nbsp;<span class="<?= [
                Requisition::STATUS_PENDING         => 'text-warning',
                Requisition::STATUS_SOURCE_APPROVED => 'text-primary',
                Requisition::STATUS_APPROVED        => 'text-success',
                Requisition::STATUS_REJECTED        => 'text-danger',
            ][$requisition['status']] ?>"><strong><?= esc($status_labels[$requisition['status']]) ?></strong></span>
        </h3>
    </div>
    <div class="panel-body">
        <dl class="dl-horizontal">
            <dt><?= lang('Requisitions.requisition_time') ?></dt>
            <dd><?= to_datetime(strtotime($requisition['requisition_time'])) ?></dd>
            <dt><?= lang('Requisitions.requested_by') ?></dt>
            <dd><?= esc($requisition['requested_name']) ?></dd>
            <dt><?= lang('Requisitions.location_from') ?></dt>
            <dd><?= esc($requisition['location_from_name']) ?></dd>
            <dt><?= lang('Requisitions.location_to') ?></dt>
            <dd><?= esc($requisition['location_to_name']) ?></dd>
            <dt><?= lang('Common.comments') ?></dt>
            <dd><?= esc($requisition['comment']) ?></dd>
            <?php if ($requisition['approved_by'] !== null) { ?>
                <dt><?= lang('Requisitions.approved_by') ?></dt>
                <dd><?= esc($requisition['approved_name']) ?> <?= lang('Requisitions.on') ?> <?= to_datetime(strtotime($requisition['approved_time'])) ?></dd>
            <?php } ?>
        </dl>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= lang('Requisitions.requisition_items') ?></h3>
    </div>
    <div class="panel-body">
        <?php if (empty($items)) { ?>
            <div class="alert alert-dismissible alert-info"><?= lang('Sales.no_items_in_cart') ?></div>
        <?php } else { ?>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th><?= lang('Sales.item_number') ?></th>
                        <th><?= lang('Requisitions.item_name') ?></th>
                        <th><?= lang('Sales.quantity') ?></th>
                        <th><?= lang('Requisitions.description') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item) { ?>
                        <tr>
                            <td><?= esc($item['item_number']) ?></td>
                            <td><?= esc($item['name']) ?></td>
                            <td><?= to_quantity_decimals($item['quantity']) ?></td>
                            <td><?= esc($item['description']) ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</div>

<?php if ($requisition['status'] < Requisition::STATUS_APPROVED) { ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="form-group">
                <?php if ($can_approve_source && $requisition['status'] === Requisition::STATUS_PENDING) { ?>
                    <?= form_open("requisitions/approveSource/{$requisition['requisition_id']}", ['class' => 'form-horizontal pull-left', 'id' => 'approve_source_form']) ?>
                    <button type="submit" class="btn btn-sm btn-primary" id="approve_source_button">
                        <span class="glyphicon glyphicon-ok">&nbsp;</span><?= lang('Requisitions.approve_source') ?>
                    </button>
                    <?= form_close() ?>
                <?php } ?>
                <?php if ($is_admin && $requisition['status'] === Requisition::STATUS_SOURCE_APPROVED) { ?>
                    <?= form_open("requisitions/approve/{$requisition['requisition_id']}", ['class' => 'form-horizontal pull-left', 'id' => 'approve_form']) ?>
                    <button type="submit" class="btn btn-sm btn-success" id="approve_button">
                        <span class="glyphicon glyphicon-ok-sign">&nbsp;</span><?= lang('Requisitions.approve') ?>
                    </button>
                    <?= form_close() ?>
                <?php } ?>
                <?= form_open("requisitions/reject/{$requisition['requisition_id']}", ['class' => 'form-horizontal pull-left', 'style' => 'margin-left: 10px;', 'id' => 'reject_form']) ?>
                <button type="submit" class="btn btn-sm btn-danger" id="reject_button">
                    <span class="glyphicon glyphicon-remove">&nbsp;</span><?= lang('Requisitions.reject') ?>
                </button>
                <?= form_close() ?>
            </div>
        </div>
    </div>
<?php } ?>

<script type="text/javascript">
    $(document).ready(function() {
        <?php if ($can_approve_source && $requisition['status'] === Requisition::STATUS_PENDING) { ?>
            $("#approve_source_button").click(function(e) {
                e.preventDefault();
                if (confirm("<?= lang('Requisitions.confirm_approve_source') ?>")) {
                    $("#approve_source_form").submit();
                }
            });
        <?php } ?>
        <?php if ($is_admin && $requisition['status'] === Requisition::STATUS_SOURCE_APPROVED) { ?>
            $("#approve_button").click(function(e) {
                e.preventDefault();
                if (confirm("<?= lang('Requisitions.confirm_approve') ?>")) {
                    $("#approve_form").submit();
                }
            });
        <?php } ?>
        $("#reject_button").click(function(e) {
            e.preventDefault();
            if (confirm("<?= lang('Requisitions.confirm_reject') ?>")) {
                $("#reject_form").submit();
            }
        });
    });
</script>

<?= view('partial/footer') ?>