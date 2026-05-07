<?php
/**
 * @var string $title
 * @var string $subtitle
 * @var string $chart_type
 * @var array $summary_data_1
 * @var array $summary_secondary_data_1
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
    <?php $secondaryCurrency = $secondaryCurrency ?? secondary_currency_context(config(\Config\OSPOS::class)->settings, $secondary_currency_rate ?? null); ?>
    <?php $currencySummaryPattern = '/(amount|subtotal|tax|total|cost|profit|retail|value)$/'; ?>
    <?php foreach ($summary_data_1 as $name => $value) { ?>
        <?php $label = lang("Reports.$name"); ?>
        <?php if (is_numeric($value) && preg_match($currencySummaryPattern, $name)) { ?>
            <div class="summary_row"><?= esc($label) . ': ' . esc(to_currency($value)) ?></div>
            <?php if (!empty($summary_secondary_data_1[$name])) { ?>
                <div class="summary_row"><?= esc(secondary_currency_display_label($label, $secondaryCurrency)) . ': ' . esc($summary_secondary_data_1[$name]) ?></div>
            <?php } elseif ($secondaryCurrency['show']) { ?>
                <div class="summary_row"><?= esc(secondary_currency_display_label($label, $secondaryCurrency)) . ': ' . esc(secondary_currency_render_amount((float) $value, $secondaryCurrency)) ?></div>
            <?php } ?>
            <div class="summary_row" style="height: 0.9em;"></div>
        <?php } elseif ($name == "total_quantity") { ?>
            <div class="summary_row"><?= esc($label) . ": " . esc($value) ?></div>
        <?php } else { ?>
            <div class="summary_row"><?= esc($label) . ': ' . esc(is_string($value) ? $value : (string) $value) ?></div>
        <?php } ?>
    <?php } ?>
</div>

<script src="<?= base_url('js/hide_cost_profit.js') ?>"></script>

<?= view('partial/footer') ?>
