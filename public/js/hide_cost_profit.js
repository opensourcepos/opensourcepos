/**
 * public/js/hide_cost_profit.js
 * toggle cost and profit in graphical report.
 */
$(function () {
    const safeSetItem = (key, value) => {
        try {
            localStorage.setItem(key, value);
        } catch (e) {
            console.error("Storage error", e);
        }
    };

    const safeGetItem = (key) => {
        try {
            return localStorage.getItem(key);
        } catch (e) {
            return null;
        }
    };

    let summaryVisibility = JSON.parse(safeGetItem("summaryVisibility")) || {
        cost: false,
        profit: false,
    };

    function applySummaryVisibility() {
        const rows = $("#chart_report_summary .summary_row");
        if (rows.length < 2) return; // Prevent errors if data is missing

        const costRow = rows.eq(rows.length - 2);
        const profitRow = rows.eq(rows.length - 1);

        summaryVisibility.cost ? costRow.show() : costRow.hide();
        summaryVisibility.profit ? profitRow.show() : profitRow.hide();
    }

    $("#toggleCostProfitButton").on("click", function () {
        summaryVisibility.cost = !summaryVisibility.cost;
        summaryVisibility.profit = !summaryVisibility.profit;
        safeSetItem("summaryVisibility", JSON.stringify(summaryVisibility));
        applySummaryVisibility();
    });

    applySummaryVisibility();
});
