<?php $this->load->view("partial/header"); ?>
<div id="page_title" style="margin-bottom:8px;"><?php echo $this->lang->line('reports_report_input'); ?></div>
<?php
if(isset($error))
{
	echo "<div class='error_message'>".$error."</div>";
}
?>
	<div>
		Export to Excel: <input type="radio" name="export_excel" id="export_excel_yes" value='1' /> Yes
		<input type="radio" name="export_excel" id="export_excel_no" value='0' checked='checked' /> No
	</div>
	
	<?php echo form_label($this->lang->line('reports_stock_location'), 'reports_stock_location_label', array('class'=>'required')); ?>
	<div id='report_stock_location'>
		<?php echo form_dropdown('stock_location',$stock_locations,'all','id="location_id"'); ?>
	</div>

	<?php echo form_label($this->lang->line('reports_item_count'), 'reports_item_count_label', array('class'=>'required')); ?>
	<div id='report_item_count'>
		<?php echo form_dropdown('item_count',$item_count,'all','id="item_count"'); ?>
	</div>

<?php
echo form_button(array(
	'name'=>'generate_report',
	'id'=>'generate_report',
	'content'=>$this->lang->line('common_submit'),
	'class'=>'submit_button')
);
?>

<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript" language="javascript">
$(document).ready(function()
{
	$("#generate_report").click(function()
	{
		var export_excel = 0;
		if ($("#export_excel_yes").attr('checked'))
		{
			export_excel = 1;
		}
		
		window.location = window.location+'/' + export_excel + '/' + $("#location_id").val() + '/' + $("#item_count").val();
	});	
});
</script>