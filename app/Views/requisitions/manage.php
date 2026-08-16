<?php

use App\Models\Requisition;

/**
 * @var array $requisitions
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
    <a class="btn btn-info btn-sm pull-right" href="<?= site_url('requisitions/new') ?>" title="<?= lang('Requisitions.new_request') ?>">
        <span class="glyphicon glyphicon-shopping-cart">&nbsp;</span><?= lang('Requisitions.new_request') ?>
    </a>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= lang('Requisitions.list_title') ?></h3>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="requisition_table">
                <thead>
                    <tr>
                        <th><?= lang('Requisitions.requisition_id') ?></th>
                        <th><?= lang('Requisitions.requisition_time') ?></th>
                        <th><?= lang('Requisitions.requested_by') ?></th>
                        <th><?= lang('Requisitions.location_from') ?></th>
                        <th><?= lang('Requisitions.location_to') ?></th>
                        <th><?= lang('Requisitions.status') ?></th>
                        <th><?= lang('Common.comments') ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($requisitions)) { ?>
                    <tr>
                        <td colspan="8">
                            <div class="alert alert-dismissible alert-info"><?= lang('Requisitions.no_requisitions') ?></div>
                        </td>
                    </tr>
                <?php } else { ?>
                    <?php
                    foreach ($requisitions as $requisition) {
                        $status_class = [
                            Requisition::STATUS_PENDING         => 'text-warning',
                            Requisition::STATUS_SOURCE_APPROVED => 'text-primary',
                            Requisition::STATUS_APPROVED        => 'text-success',
                            Requisition::STATUS_REJECTED        => 'text-danger',
                        ][$requisition['status']];
                        ?>
                        <tr>
                            <td><?= $requisition['requisition_id'] ?></td>
                            <td><?= to_datetime(strtotime($requisition['requisition_time'])) ?></td>
                            <td><?= esc($requisition['requested_name']) ?></td>
                            <td><?= esc($requisition['location_from_name']) ?></td>
                            <td><?= esc($requisition['location_to_name']) ?></td>
                            <td><span class="<?= $status_class ?>"><strong><?= esc($status_labels[$requisition['status']]) ?></strong></span></td>
                            <td><?= esc($requisition['comment']) ?></td>
                            <td>
                                <?= anchor("requisitions/detail/{$requisition['requisition_id']}", lang('Requisitions.open'), ['class' => 'btn btn-default btn-xs']) ?>
                            </td>
                        </tr>
                <?php
                    }
                }
?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= view('partial/footer') ?>