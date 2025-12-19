<?php
/**
 * @var string $title
 * @var string $subtitle
 * @var string $chart_type
 * @var array $summary_data_1
 */
?>

<?= view('partial/header') ?>

<script type="text/javascript">
    dialog_support.init("a.modal-dlg");
</script>

<div id="page_title"><?= esc($title) ?></div>

<div id="page_subtitle"><?= esc($subtitle) ?></div>

<div class="ct-chart ct-golden-section" id="chart1"></div>

<div id="toolbar">
    <div class="pull-left form-inline" role="toolbar">
        <!-- Toggle Button -->
        <button id="toggleCostProfitButton" class="btn btn-default btn-sm print_hide">
            <?php echo lang('Reports.toggle_cost_and_profit'); ?>
        </button>
    </div>
</div>

<?= view($chart_type) ?>

<div id="chart_report_summary">
    <?php foreach ($summary_data_1 as $name => $value) { ?>
        <div class="summary_row"><?= lang("Reports.$name") . ': ' . to_currency($value) ?></div>
    <?php } ?>
</div>

<script src="<?= base_url('js/hide_cost_profit.js') ?>"></script>

<?= view('partial/footer') ?>
