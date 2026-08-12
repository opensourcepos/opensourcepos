<?php
/**
 * @var string $title
 * @var string $subtitle
 * @var array  $overall_summary_data
 * @var array  $details_data
 * @var array  $headers
 * @var array  $summary_data
 * @var array  $config
 */
?>

<?= view('partial/header') ?>

<div id="page_title"><?= esc($title) ?></div>

<div id="page_subtitle"><?= esc($subtitle) ?></div>

<div id="table_holder">
    <table id="table"></table>
</div>

<div id="report_summary">
    <?php foreach ($overall_summary_data as $name => $value) { ?>
        <div class="summary_row"><?= lang("Reports.{$name}") . ': ' . esc($value) ?></div>
    <?php } ?>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        <?= view('partial/bootstrap_tables_locale') ?>

        const details_data = <?= json_encode(esc($details_data)) ?>;
        <?= view('partial/visibility_js') ?>

        $('#table')
            .addClass("table-striped")
            .addClass("table-bordered")
            .bootstrapTable({
                columns: applyColumnVisibility(<?= transform_headers(esc($headers['summary']), true) ?>),
                stickyHeader: true,
                stickyHeaderOffsetLeft: $('#table').offset().left + 'px',
                stickyHeaderOffsetRight: $('#table').offset().right + 'px',
                pageSize: <?= $config['lines_per_page'] ?>,
                pagination: true,
                sortable: true,
                showColumns: true,
                uniqueId: 'id',
                showExport: true,
                exportDataType: 'all',
                exportTypes: ['json', 'xml', 'csv', 'txt', 'sql', 'xlsx', 'pdf'],
                data: <?= json_encode($summary_data) ?>,
                iconSize: 'sm',
                paginationVAlign: 'bottom',
                detailView: true,
                escape: true,
                search: true,
                onPageChange: init_dialog,
                onPostBody: function () {
                    dialog_support.init("a.modal-dlg");
                },
                onExpandRow: function (index, row, $detail) {
                    $detail.html('<table></table>').find("table").bootstrapTable({
                        columns: <?= transform_headers_readonly(esc($headers['details'])) ?>,
                        data: details_data[(!isNaN(row.id) && row.id) || $(row[0] || row.id).text().replace(
                            /(POS|RECV|TRF)\s*/g, '')]
                    });

                    init_dialog();
                }
            });

        init_dialog();
    });
</script>

<?= view('partial/footer') ?>
