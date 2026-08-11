<?php
/**
 * @var string $title
 * @var string $subtitle
 * @var string $chart_type
 * @var array $summary_data_1
 */
?>

<?= view('partial/header') ?>

<?php
$title_info['config_title'] = esc($title);
echo view('configs/config_header', $title_info);
?>

<h5><?= esc($subtitle) ?></h5>

<div class="ct-chart ct-golden-section" id="chart1"></div>

<button type="button" class="btn btn-secondary d-print-none" id="toggleCostProfitButton">
    <i class="bi bi-toggles"></i><span class="d-none d-sm-inline ms-2"><?= lang('Reports.toggle_cost_and_profit') ?></span>
</button>

<?= view($chart_type) ?>

<div id="chart_report_summary">
    <?php foreach ($summary_data_1 as $name => $value) { ?>
        <div class="summary_row"><?= lang("Reports.$name") . ': ' . esc(to_currency($value)) ?></div>
    <?php } ?>
</div>

<script src="<?= base_url('js/hide_cost_profit.js') ?>"></script>

<?= view('partial/footer') ?>
