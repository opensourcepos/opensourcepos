<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>

<div id="page_title"><?= $title ?></div>

<div id="page_subtitle"><?= $subtitle ?></div>

<div id="table_holder">
	<table id="table"></table>
</div>

<div id="report_summary">
	<?php foreach ($summary_data as $name => $value) {
		if ($name == "total_quantity") { ?>
			<div class="summary_row"><?= $this->lang->line('reports_' . $name) . ': ' . $value; ?></div>
		<?php } else { ?>
			<div class="summary_row"><?= $this->lang->line('reports_' . $name) . ': ' . to_currency($value); ?></div>
	<?php }
	} ?>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		<?php $this->load->view('partial/bootstrap_tables_locale'); ?>

		$('#table')
			.addClass("table-striped")
			.addClass("table-bordered")
			.bootstrapTable({
				columns: <?= transform_headers($headers, TRUE, FALSE); ?>,
				stickyHeader: true,
				stickyHeaderOffsetLeft: $('#table').offset().left + 'px',
				stickyHeaderOffsetRight: $('#table').offset().right + 'px',
				pageSize: <?= $this->config->item('lines_per_page'); ?>,
				sortable: true,
				showExport: true,
				exportDataType: 'all',
				exportTypes: ['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'pdf'],
				pagination: true,
				showColumns: true,
				data: <?= json_encode($data); ?>,
				iconSize: 'sm',
				paginationVAlign: 'bottom',
				escape: false
			});
	});
</script>

<?php $this->load->view("partial/footer"); ?>