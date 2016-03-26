<?php $this->load->view("partial/header"); ?>

<div id="page_title"><?php echo $title ?></div>

<div id="page_subtitle"><?php echo $subtitle ?></div>

<div style="text-align: center;">
	<script type="text/javascript">
		swfobject.embedSWF("<?php echo base_url(); ?>open-flash-chart.swf", "chart", "800", "400", "9.0.0", "expressInstall.swf", {"data-file": "<?php echo $data_file; ?>"} );
	</script>
</div>

<div id="chart_wrapper">
	<div id="chart"></div>
</div>

<div id="report_summary">
	<?php
	foreach($summary_data as $name=>$value)
	{
	?>
		<div class="summary_row"><?php echo $this->lang->line('reports_'.$name). ': ' . to_currency($value); ?></div>
	<?php
	}
	?>
</div>

<?php $this->load->view("partial/footer"); ?>