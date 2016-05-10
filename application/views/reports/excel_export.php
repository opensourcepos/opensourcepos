<?php $this->load->view("partial/header"); ?>

<div id="page_title"><?php echo $this->lang->line('reports_report_input'); ?></div>

<?php
if(isset($error))
{
	echo "<div class='alert alert-dismissible alert-danger'>".$error."</div>";
}
?>

<?php echo form_open('#', array('id'=>'item_form', 'enctype'=>'multipart/form-data', 'class'=>'form-horizontal')); ?>
	<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('common_export_excel'), 'export_excel', !empty($basic_version) ? array('class'=>'control-label required col-xs-3') : array('class'=>'control-label col-xs-2')); ?>
		<div class="col-xs-4">
			<label class="radio-inline">
				<input type="radio" name="export_excel" id="export_excel_yes" value='1' /> <?php echo $this->lang->line('common_export_excel_yes'); ?>
			</label>
			<label class="radio-inline">
				<input type="radio" name="export_excel" id="export_excel_no" value='0' checked='checked' /> <?php echo $this->lang->line('common_export_excel_no'); ?>
			</label>
		</div>
	</div>

<?php
	echo form_button(array(
		'name'=>'generate_report',
		'id'=>'generate_report',
		'content'=>$this->lang->line('common_submit'),
		'class'=>'btn btn-primary btn-sm')
	);
?>
<?php echo form_close(); ?>

<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript" language="javascript">
$(document).ready(function()
{
	$("#generate_report").click(function()
	{
		window.location = [window.location, $("input[name='export_excel']").val()].join("/");
	});	
});
</script>