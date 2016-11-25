<?php $this->load->view("partial/header"); ?>

<div id="page_title"><?php echo $title ?></div>

<div id="page_subtitle"><?php echo $subtitle ?></div>

<div id="table_holder">
	<table id="table"></table>
</div>

<div id="report_summary">
	<?php
	foreach($overall_summary_data as $name=>$value)
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

		var detail_data = <?php echo json_encode($details_data); ?>;

		var init_dialog = function()
		{
			<?php if (isset($editable)): ?>
			table_support.submit_handler('<?php echo site_url("reports/get_detailed_" . $editable . "_row")?>');
			dialog_support.init("a.modal-dlg");
			<?php endif; ?>
		};

		$('#table').bootstrapTable({
			columns: <?php echo transform_headers_readonly($headers['summary']); ?>,
			pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
			striped: true,
			pagination: true,
			sortable: true,
			showColumns: true,
			uniqueId: 'id',
			showExport: true,
			data: <?php echo json_encode($summary_data); ?>,
			iconSize: 'sm',
			paginationVAlign: 'bottom',
			detailView: true,
			uniqueId: 'id',
			escape: false,
			onPageChange: init_dialog,
			onPostBody: function() {
				dialog_support.init("a.modal-dlg");
			},
			onExpandRow: function (index, row, $detail) {
				$detail.html('<table></table>').find("table").bootstrapTable({
					columns: <?php echo transform_headers_readonly($headers['details']); ?>,
					data: detail_data[row.id || $(row[0]).text().replace(/(POS|RECV)\s*/g, '')]
				});
			}
		});

		init_dialog();
	});
</script>

<?php $this->load->view("partial/footer"); ?>