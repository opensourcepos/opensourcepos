<?php 
	// Check if for excel export process
	if($export_excel == 1)
	{
		ob_start();
		$this->load->view("partial/header_excel");
	}
	else
	{
		$this->load->view("partial/header");
	} 
?>

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

<?php 
if($export_excel == 1)
{
	$this->load->view("partial/footer_excel");
	$content = ob_end_flush();
	
	$filename = trim($filename);
	$filename = str_replace(array(' ', '/', '\\'), '', $title);
	$filename .= "_Export.xls";
	header('Content-type: application/ms-excel');
	header('Content-Disposition: attachment; filename='.$filename);
	echo $content;

	die();
}
else
{
	?>
	<script type="text/javascript" language="javascript">
		$(document).ready(function()
		{
			<?php $this->load->view('partial/bootstrap_tables_locale'); ?>

			$('#table').bootstrapTable({
				columns: <?php echo transform_headers_readonly($headers); ?>,
				pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
				striped: true,
				pagination: true,
				showColumns: true,
				data: <?php echo json_encode($data); ?>,
				iconSize: 'sm',
				paginationVAlign: 'bottom'
			});

		});
	</script>
	<?php
	$this->load->view("partial/footer"); 
?>
<?php
} // end if not is excel export 
?>