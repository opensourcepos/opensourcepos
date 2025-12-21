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

function safeRemoveItem(key) {
    try {
        localStorage.removeItem(key);
    } catch (e) {
        console.error(`Failed to remove item from localStorage: ${e.message}`);
    }
}

// Load saved column visibility from localStorage
var savedVisibility = JSON.parse(safeGetItem('columnVisibility')) || { cost: false, profit: false };
var visibleColumns = savedVisibility;

// Function to save column visibility to localStorage
function saveColumnVisibility(visibility) {
    safeSetItem('columnVisibility', JSON.stringify(visibility));
}

// Apply column visibility on table initialization
function applyColumnVisibility(columns) {
    return columns.map(function (col) {
        if (visibleColumns[col.field] !== undefined) {
            col.visible = visibleColumns[col.field]; // Apply visibility from localStorage
        }
        return col;
    });
}

// Event listener for column visibility toggle
$('#table').on('column-switch.bs.table', function (e, field, checked) {
    visibleColumns[field] = checked; // Save the visibility of this column
    saveColumnVisibility(visibleColumns); // Store it in localStorage
});

// Ensure that saved column visibility is applied immediately after table load
$('#table').bootstrapTable('refreshOptions', {
    columns: $('#table').bootstrapTable('getOptions').columns // Force refresh to apply column visibility
});

// Initialize visibility settings from localStorage
var summaryVisibility = JSON.parse(safeGetItem('summaryVisibility')) || { cost: false, profit: false };

// Function to apply visibility for cost and profit rows
function applySummaryVisibility() {
    var rows = $('#report_summary .summary_row');
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

// Initialize dialog (if editable)
var init_dialog = function () {
<?php if (isset($editable)): ?>
        table_support.submit_handler('<?php echo site_url("reports/get_detailed_{$editable}_row") ?>');
    dialog_support.init("a.modal-dlg");
<?php endif; ?>
};
