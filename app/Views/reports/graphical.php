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

<?= view($chart_type) ?>

<div id="chart_report_summary">
    <?php foreach ($summary_data_1 as $name => $value) { ?>
        <div class="summary_row"><?= lang("Reports.$name") . ': ' . to_currency($value) ?></div>
    <?php } ?>
</div>

<button id="toggleCostProfitButton" style="font-size: 10px; padding: 2px 5px; cursor: pointer; border: 1px solid #ddd; position: relative; bottom: 10px; left: 10px; opacity: 0.5;">
    <?php echo lang('Reports.toggle_cost_and_profit'); ?>
</button>

<script>
    <?php // used in reports ?>
    // Utility functions for safe localStorage access
    function safeSetItem(key, value) {
        try {
            localStorage.setItem(key, value);
        } catch (e) {
            console.error(`Failed to set item in localStorage: ${e.message}`);
        }
    }

    function safeGetItem(key) {
        try {
            return localStorage.getItem(key);
        } catch (e) {
            console.error(`Failed to get item from localStorage: ${e.message}`);
            return null; // Default fallback
        }
    }

    // Initialize visibility settings from localStorage
    var summaryVisibility = JSON.parse(safeGetItem('summaryVisibility')) || { cost: false, profit: false };

    // Function to apply visibility for cost and profit rows
    function applySummaryVisibility() {
        var rows = $('#chart_report_summary .summary_row');
        var costRow = rows.eq(rows.length - 2); // Second-to-last row
        var profitRow = rows.eq(rows.length - 1); // Last row

        if (summaryVisibility.cost === false) {
            costRow.hide(); // Hide the cost row
        } else {
            costRow.show(); // Show the cost row
        }

        if (summaryVisibility.profit === false) {
            profitRow.hide(); // Hide the profit row
        } else {
            profitRow.show(); // Show the profit row
        }
    }

    // Toggle visibility when the button is clicked
    $('#toggleCostProfitButton').click(function () {
        summaryVisibility.cost = !summaryVisibility.cost;
        summaryVisibility.profit = !summaryVisibility.profit;

        safeSetItem('summaryVisibility', JSON.stringify(summaryVisibility));
        applySummaryVisibility();
    });

    // Apply saved visibility state on page load
    applySummaryVisibility();

</script>

<?= view('partial/footer') ?>
