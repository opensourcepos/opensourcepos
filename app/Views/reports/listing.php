<?php
/**
 * @var int   $person_id
 * @var array $permission_ids
 * @var array $grants
 */

$detailed_reports = [
    'reports_sales'      => 'detailed',
    'reports_receivings' => 'detailed',
    'reports_customers'  => 'specific',
    'reports_discounts'  => 'specific',
    'reports_employees'  => 'specific',
    'reports_suppliers'  => 'specific',
];
?>

<?= view('partial/header') ?>

<script type="text/javascript">
    dialog_support.init("a.modal-dlg");
</script>

<?php
if (isset($error)) {
    echo '<div class="alert alert-dismissible alert-danger">' . esc($error) . '</div>';
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="bi bi-bar-chart icon-spacing"></i><?= lang('Reports.graphical_reports') ?></h3>
            </div>
            <div class="list-group">
                <?php foreach ($permission_ids as $permission_id) {
                    if (can_show_report($permission_id, ['inventory', 'receiving'])) {
                        $link = get_report_link($permission_id, 'graphical_summary');
                ?>
                        <a class="list-group-item" href="<?= $link['path'] ?>"><?= $link['label'] ?></a>
                <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="bi bi-card-list icon-spacing"></i><?= lang('Reports.summary_reports') ?></h3>
            </div>
            <div class="list-group">
                <?php foreach ($permission_ids as $permission_id) {
                    if (can_show_report($permission_id, ['inventory', 'receiving'])) {
                        $link = get_report_link($permission_id, 'summary');
                ?>
                        <a class="list-group-item" href="<?= $link['path'] ?>"><?= $link['label'] ?></a>
                <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="bi bi-card-checklist icon-spacing"></i><?= lang('Reports.detailed_reports') ?></h3>
            </div>
            <div class="list-group">
                <?php foreach ($detailed_reports as $report_name => $prefix) {
                    if (in_array($report_name, $permission_ids, true)) {
                        $link = get_report_link($report_name, $prefix);
                ?>
                        <a class="list-group-item" href="<?= $link['path'] ?>"><?= $link['label'] ?></a>
                <?php
                    }
                }
                ?>
            </div>
        </div>

        <?php if (in_array('reports_inventory', $permission_ids, true)) { ?>
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="bi bi-box icon-spacing"></i><?= lang('Reports.inventory_reports') ?></h3>
                </div>
                <div class="list-group">
                    <?php
                    $inventory_low_report = get_report_link('reports_inventory_low');
                    $inventory_summary_report = get_report_link('reports_inventory_summary');
                    ?>
                    <a class="list-group-item" href="<?= $inventory_low_report['path'] ?>"><?= $inventory_low_report['label'] ?></a>
                    <a class="list-group-item" href="<?= $inventory_summary_report['path'] ?>"><?= $inventory_summary_report['label'] ?></a>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<?= view('partial/footer') ?>
