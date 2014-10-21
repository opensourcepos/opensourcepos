<?php 
//OJB: Check if for excel export process
if($export_excel == 1){
	ob_start();
	$this->load->view("partial/header_excel");
}else{
	$this->load->view("partial/header");
} 
?>
<div id="page_title" style="margin-bottom:8px;"><?php echo $title ?></div>
<div id="page_subtitle" style="margin-bottom:8px;"><?php echo $subtitle ?></div>
<div id="table_holder">
	<table class="tablesorter report" id="sortable_table">
		<thead>
			<tr>
				<th><a href="#" class="expand_all">+</a></th>
				<?php foreach ($headers['summary'] as $header) { ?>
				<th><?php echo $header; ?></th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($summary_data as $key=>$row) { ?>
			<tr>
				<td><a href="#" class="expand">+</a></td>
				<?php foreach ($row as $cell) { ?>
				<td><?php echo $cell; ?></td>
				<?php } ?>
			</tr>
			<tr>
				<td colspan="100">
				<table class="innertable">
					<thead>
						<tr>
							<?php foreach ($headers['details'] as $header) { ?>
							<th><?php echo $header; ?></th>
							<?php } ?>
						</tr>
					</thead>
				
					<tbody>
						<?php foreach ($details_data[$key] as $row2) { ?>
						
							<tr>
								<?php foreach ($row2 as $cell) { ?>
								<td><?php echo $cell; ?></td>
								<?php } ?>
							</tr>
						<?php } ?>
					</tbody>
				</table>
				
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<div id="report_summary">
<?php foreach($overall_summary_data as $name=>$value) { ?>
	<div class="summary_row"><?php echo $this->lang->line('reports_'.$name). ': '.to_currency($value); ?></div>
<?php }?>
</div>

<?php if (isset($editable)): ?>
<div id="feedback_bar"></div>
<?php endif; ?>

<?php 
if($export_excel == 1){
	$this->load->view("partial/footer_excel");
	$content = ob_end_flush();
	
	$filename = trim($filename);
	$filename = str_replace(array(' ', '/', '\\'), '', $title);
	$filename .= "_Export.xls";
	header('Content-type: application/ms-excel');
	header('Content-Disposition: attachment; filename='.$filename);
	echo $content;
	die();
	
}else{
	$this->load->view("partial/footer"); 
?>
<script type="text/javascript" language="javascript">

<?php if (isset($editable)): ?>

function post_form_submit(response, row_id)
{
	if(!response.success)
	{
		set_feedback(response.message,'error_message',true);
	}
	else
	{
		var row_id = response.id
		$.get('<?php echo site_url("reports/get_detailed_" . $editable . "_row")?>/'+row_id, function(response)
		{
			//Replace previous row
			var row = get_table_row(row_id).parent().parent();
			var sign = row.find("a.expand").text();
			row.replaceWith(response);	
			row = get_table_row(row_id).parent().parent();
			update_sortable_table();
			animate_row(row);
			row.find("a.expand").click(expand_handler).text(sign);
			tb_init(row.find("a.thickbox"));
		});
		set_feedback(response.message,'success_message',false);
	}
}

<?php endif; ?>

function expand_handler(event)
{
	$(event.target).parent().parent().next().find('.innertable').toggle();

	if ($(event.target).text() == '+')
	{
		
		$(event.target).text('-');
	}
	else
	{
		$(event.target).text('+');
	}
	return false;
};

$(document).ready(function()
{
	
	$(".tablesorter a.expand_all").click(function(event)
	{
		var $inner_elements = $(".tablesorter .innertable");
		if ($inner_elements.is(":visible")) 
		{
			$inner_elements.hide();
			$("a.expand, a.expand_all").text('+');
		} 
		else 
		{
			$inner_elements.show();
			$("a.expand, a.expand_all").text('-');
		} 
		return false;
	});
	
	$(".tablesorter a.expand").click(expand_handler);
	
});
</script>
<?php 
} // end if not is excel export 
?>