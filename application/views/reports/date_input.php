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
	if(!empty($mode))
	{
	?>
		<div class="form-group form-group-sm">
			<?php
			if($mode == 'sale')
			{
			?>
				<?php echo form_label($this->lang->line('reports_sale_type'), 'reports_sale_type_label', array('class'=>'required control-label col-xs-2')); ?>
				<div id='report_sale_type' class="col-xs-3">
					<?php echo form_dropdown('sale_type', $sale_type_options, 'complete', array('id'=>'input_type', 'class'=>'form-control')); ?>
				</div>
			<?php
			}
			elseif($mode == 'receiving')
			{
			?>
				<?php echo form_label($this->lang->line('reports_receiving_type'), 'reports_receiving_type_label', array('class'=>'required control-label col-xs-2')); ?>
				<div id='report_receiving_type' class="col-xs-3">
					<?php echo form_dropdown('receiving_type', array('all' => $this->lang->line('reports_all'),
						'receiving' => $this->lang->line('reports_receivings'),
						'returns' => $this->lang->line('reports_returns'),
						'requisitions' => $this->lang->line('reports_requisitions')), 'all', array('id'=>'input_type', 'class'=>'form-control')); ?>
				</div>
			<?php
			}
			?>
		</div>
	<?php
	}
	?>

	<?php	
	if (!empty($stock_locations) && count($stock_locations) > 1)
	{
	?>
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('reports_stock_location'), 'reports_stock_location_label', array('class'=>'required control-label col-xs-2')); ?>
			<div id='report_stock_location' class="col-xs-3">
				<?php echo form_dropdown('stock_location', $stock_locations, 'all', array('id'=>'location_id', 'class'=>'form-control')); ?>
			</div>
		</div>
	<?php
	}
	?>

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
	<?php $this->load->view('partial/daterangepicker'); ?>

	$("#generate_report").click(function()
	{		
		window.location = [window.location, start_date, end_date, $("#input_type").val() || 0, $("#location_id").val()].join("/");
	});
});
</script>
