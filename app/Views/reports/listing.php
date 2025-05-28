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

<?php
$title_info['config_title'] = 'Reports';
echo view('configs/config_header', $title_info);
?>

<?php if (isset($error)) { ?>
    <div class="alert alert-danger alert-dismissible" role="alert">
        <?= esc($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php } ?>

<div class="row">
    <div class="col-12 col-md-6 col-lg-4 mb-3">
        <div class="card bg-primary">
            <div class="card-header text-light text-truncate">
                <i class="bi bi-bar-chart me-2"></i><?= lang('Reports.graphical_reports'); ?>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($permission_ids as $permission_id) {
                    if (can_show_report($permission_id, ['inventory', 'receiving'])) {
                        $link = get_report_link($permission_id, 'graphical_summary'); ?>
                    <a class="list-group-item list-group-item-action text-truncate" href="<?= $link['path'] ?>"><?= $link['label'] ?></a>
                <?php }} ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-4 mb-3">
        <div class="card bg-primary">
            <div class="card-header text-light text-truncate">
                <i class="bi bi-card-list me-2"></i><?= lang('Reports.summary_reports'); ?>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($permission_ids as $permission_id) {
                    if (can_show_report($permission_id, ['inventory', 'receiving'])) {
                        $link = get_report_link($permission_id, 'summary'); ?>
                    <a class="list-group-item list-group-item-action text-truncate" href="<?= $link['path'] ?>"><?= $link['label'] ?></a>
                <?php }} ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-4 mb-3">
        <div class="card bg-primary mb-3">
            <div class="card-header text-light text-truncate">
                <i class="bi bi-card-checklist me-2"></i><?= lang('Reports.detailed_reports') ?>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($detailed_reports as $report_name => $prefix) {
                    if (in_array($report_name, $permission_ids, true)) {
                        $link = get_report_link($report_name, $prefix); ?>
                    <a class="list-group-item list-group-item-action text-truncate" href="<?= $link['path'] ?>"><?= $link['label'] ?></a>
                <?php }} ?>
            </div>
        </div>

        <?php if (in_array('reports_inventory', $permission_ids, true)) { ?>
            <div class="card bg-primary">
                <div class="card-header text-light text-truncate">
                    <i class="bi bi-box me-2"></i><?= lang('Reports.inventory_reports') ?>
                </div>
                <div class="list-group list-group-flush">
                    <?php
                    $inventory_low_report = get_report_link('reports_inventory_low');
                    $inventory_summary_report = get_report_link('reports_inventory_summary');
                    ?>
                    <a class="list-group-item list-group-item-action text-truncate" href="<?= $inventory_low_report['path'] ?>"><?= $inventory_low_report['label'] ?></a>
                    <a class="list-group-item list-group-item-action text-truncate" href="<?= $inventory_summary_report['path'] ?>"><?= $inventory_summary_report['label'] ?></a>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<?= view('partial/footer') ?>
