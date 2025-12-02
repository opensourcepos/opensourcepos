<?php
/**
 * @var string $title
 * @var string $subtitle
 * @var array $summary_data
 * @var array $headers
 * @var array $data
 * @var array $config
 */
?>

<?= view('partial/header') ?>

<?php
$title_info['config_title'] = esc($title);
echo view('configs/config_header', $title_info);
?>

<div id="page_subtitle"><?= esc($subtitle) ?></div>

<div id="table_holder">
    <table id="table"></table>
</div>

<div id="report_summary">
    <?php
    foreach ($summary_data as $name => $value) {
        if ($name == "total_quantity") {
    ?>
            <div class="summary_row"><?= lang("Reports.$name") . ": $value" ?></div>
        <?php } else { ?>
            <div class="summary_row"><?= lang("Reports.$name") . ': ' . to_currency($value) ?></div>
    <?php
        }
    }
    ?>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        <?= view('partial/bootstrap_tables_locale') ?>

        $('#table')
            .addClass("table-striped")
            .addClass("table-bordered")
            .bootstrapTable({
                columns: <?= transform_headers(esc($headers), true, false) ?>,
                stickyHeader: true,
                pageSize: <?= $config['lines_per_page'] ?>,
                sortable: true,
                showExport: true,
                exportDataType: 'all',
                exportTypes: ['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'pdf'],
                pagination: true,
                showColumns: true,
                data: <?= json_encode($data) ?>,
                paginationVAlign: 'bottom',
                escape: true,
                search: true,
                loadingTemplate: function (loadingMessage) {
                    return '<div class="w-100 h-100 bg-body text-center pt-2"><div class="spinner-grow spinner-grow-sm"></div><span class="ps-1" role="status">' + loadingMessage + '</span></div>'
                },
                loadingFontSize: '1em'
            });
    });
</script>

<?= view('partial/footer') ?>
