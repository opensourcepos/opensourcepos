<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>


<div id="page_title"><?php echo $this->lang->line('reports_report_input'); ?></div>

<?php
if(isset($error))
{
	echo "<div class='alert alert-dismissible alert-danger'>".$error."</div>";
}
?>

<?php echo form_open('#', array('id'=>'item_form', 'enctype'=>'multipart/form-data', 'class'=>'form-horizontal')); ?>
	<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('reports_date_range'), 'report_date_range_label', array('class'=>'control-label col-xs-2 required')); ?>
		<div class="col-xs-3">
				<?php echo form_input(array('name'=>'daterangepicker', 'class'=>'form-control input-sm', 'id'=>'daterangepicker')); ?>
		</div>
	</div>

	<?php	
	if (isset($discount_type_options))
	{
	?>
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('reports_discount_type'), 'reports_discount_type_label', array('class'=>'required control-label col-xs-2')); ?>
			<div id='report_discount_type' class="col-xs-3">
				<?php echo form_dropdown('discount_type', $discount_type_options, $this->config->item('default_sales_discount_type'), array('id'=>'discount_type_id', 'class'=>'form-control')); ?>
			</div>
		</div>
	<?php
	}
	?>

	<div class="form-group form-group-sm" id="report_specific_input_data">
		<?php echo form_label($specific_input_name, 'specific_input_name_label', array('class'=>'required control-label col-xs-2')); ?>
		<div class="col-xs-3 discount_percent">
			<?php echo form_dropdown('specific_input_data', $specific_input_data, '', 'id="specific_input_data" class="form-control"'); ?>
		</div>

		<?php	
		if (isset($discount_type_options))
		{
		?>
		<div class="col-xs-3 discount_fixed">
			<?php echo form_input(array(
				'name' => 'discount_fixed',
				'id' => 'discount_fixed',
				'class' => 'form-control input-sm required',
				'type' => 'number',
				'min' => 0,
				'value' => $this->config->item('default_sales_discount'))); ?>
		</div>
		<?php
		}
		?>
	</div>

	<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('reports_sale_type'), 'reports_sale_type_label', array('class'=>'required control-label col-xs-2')); ?>
		<div id='report_sale_type' class="col-xs-3">
			<?php echo form_dropdown('sale_type',$sale_type_options, 'complete', 'id="input_type" class="form-control"'); ?>
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

<script type="text/javascript">
$(document).ready(function()
{
	<?php	
	if (isset($discount_type_options))
	{
	?>
		$("#discount_type_id").change(check_discount_type).ready(check_discount_type);
	<?php
	}
	?>

	<?php $this->load->view('partial/daterangepicker'); ?>

	$("#generate_report").click(function()
	{
		var specific_input_data = $('#specific_input_data').val();
		if(!$(".discount_percent").is(":visible")){
			specific_input_data = $('#discount_fixed').val();
		}

		window.location = [window.location, start_date, end_date, specific_input_data, $("#input_type").val() || 0, $("#discount_type_id").val() || 0].join("/");
	});
});

function check_discount_type()
{
	var discount_type = $("#discount_type_id").val();

	if(discount_type==1){
		$(".discount_percent").hide();
		$(".discount_fixed").show();
	}else{
		$(".discount_percent").show();
		$(".discount_fixed").hide();
	}
}
</script>
