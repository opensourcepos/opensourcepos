<?php
/**
 * @var string $title
 * @var string $subtitle
 * @var array  $summary_data
 * @var array  $headers
 * @var array  $data
 * @var array  $config
 */
?>

<?= view('partial/header') ?>

<script type="text/javascript">
    dialog_support.init("a.modal-dlg");
</script>

<div id="page_title"><?= esc($title) ?></div>

<div id="page_subtitle"><?= esc($subtitle) ?></div>

<div id="toolbar">
    <div class="pull-left form-inline" role="toolbar">
        <span class="help-block"><?= lang('Reports.inventory_by_location_legend') ?></span>
    </div>
</div>

<div id="table_holder">
    <table id="table"></table>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        <?= view('partial/bootstrap_tables_locale') ?>
        <?= view('partial/visibility_js') ?>

        $('#table')
            .addClass("table-striped")
            .addClass("table-bordered")
            .bootstrapTable({
                columns: <?= transform_headers(esc($headers), true, false) ?>,
                stickyHeader: true,
                stickyHeaderOffsetLeft: $('#table').offset().left + 'px',
                stickyHeaderOffsetRight: $('#table').offset().right + 'px',
                pageSize: <?= $config['lines_per_page'] ?>,
                sortable: true,
                showExport: true,
                exportDataType: 'all',
                exportTypes: ['json', 'xml', 'csv', 'txt', 'sql', 'xlsx', 'pdf'],
                pagination: true,
                showColumns: true,
                data: <?= json_encode($data) ?>,
                iconSize: 'sm',
                paginationVAlign: 'bottom',
                escape: true,
                search: true
            });

        // Highlight cells whose quantity is at or below the reorder level
        // so stock can be reallocated from branches with surplus.
        const $table = $('#table');
        const reorderIndex = <?= count($headers) - 1 ?>;

        $table.on('load-success.bs.table', function () {
            $table.find('tbody tr').each(function () {
                const $cells = $(this).find('td');
                const reorderLevel = parseFloat($cells.eq(reorderIndex).text().replace(/,/g, ''));
                if (isNaN(reorderLevel)) {
                    return;
                }

                // Location columns come after the three fixed columns
                $cells.each(function (index) {
                    if (index < 3 || index >= reorderIndex) {
                        return;
                    }
                    const qty = parseFloat($(this).text().replace(/,/g, ''));
                    if (!isNaN(qty) && qty <= reorderLevel) {
                        $(this).css('background-color', '#fcf8e3');
                    }
                });
            });
        });
    });
</script>

<?= view('partial/footer') ?>
