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

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>

<div id="page_title"><?= esc($title) ?></div>

<div id="page_subtitle"><?= esc($subtitle) ?></div>

<div id="table_holder">
	<table id="table"></table>
</div>

<div id="report_summary">
	<?php
	foreach($summary_data as $name => $value)
	{
		if($name == "total_quantity")
		{
	?>
			<div class="summary_row"><?= lang("Reports.$name") . ": $value" ?></div>
	<?php
		}
		else
		{
	?>
			<div class="summary_row"><?= lang("Reports.$name") . ': ' . to_currency($value) ?></div>
	<?php
		}
	}
	?>
</div>

<script type="text/javascript">
	$(document).ready(function()
	{
		<?= view('partial/bootstrap_tables_locale') ?>

		$('#table')
			.addClass("table-striped")
			.addClass("table-bordered")
			.bootstrapTable({
				columns: <?= transform_headers(esc($headers, 'js'), true, false) ?>,
				stickyHeader: true,
				stickyHeaderOffsetLeft: $('#table').offset().left + 'px',
				stickyHeaderOffsetRight: $('#table').offset().right + 'px',
				pageSize: <?= $config['lines_per_page'] ?>,
				sortable: true,
				showExport: true,
				exportDataType: 'all',
				exportTypes: ['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'xlsx', 'pdf'],
				pagination: true,
				showColumns: true,
				data: <?= json_encode(esc($data, 'js')) ?>,
				iconSize: 'sm',
				paginationVAlign: 'bottom',
				escape: false,
				search: true
		});
	});
</script>

<?= view('partial/footer') ?>
