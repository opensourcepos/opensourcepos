<?php $this->load->view("partial/header"); ?>

<div id="page_title"><?php echo $title ?></div>

<div id="page_subtitle"><?php echo $subtitle ?></div>

<div id="table_holder">
	<table id="table"></table>
</div>

<div id="report_summary">
	<?php
	foreach($summary_data as $name=>$value)
	{
	?>
		<div class="summary_row"><?php echo $this->lang->line('reports_'.$name). ': '.to_currency($value); ?></div>
	<?php
	}
	?>
</div>

<script type="text/javascript">
	$(document).ready(function()
	{
		<?php $this->load->view('partial/bootstrap_tables_locale'); ?>

		$('#table').bootstrapTable({
			columns: <?php echo transform_headers_readonly($headers); ?>,
			pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
			striped: true,
			sortable: true,
			showExport: true,
			pagination: true,
			showColumns: true,
			showExport: true,
			data: <?php echo json_encode($data); ?>,
			iconSize: 'sm',
			paginationVAlign: 'bottom',
			escape: false
		});

	});
</script>

<?php $this->load->view("partial/footer"); ?>