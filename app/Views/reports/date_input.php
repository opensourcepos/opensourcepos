<?php
/**
 * @var array $sale_type_options
 */
?>
<?php echo view('partial/header') ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>


<div id="page_title"><?php echo lang('Reports.report_input') ?></div>

<?php
if(isset($error))
{
	echo '<div class=\'alert alert-dismissible alert-danger\'>' . esc($error) . '</div>';
}
?>

<?php echo form_open('#', ['id' => 'item_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']) ?>
	<div class="form-group form-group-sm">
		<?php echo form_label(lang('Reports.date_range'), 'report_date_range_label', ['class' => 'control-label col-xs-2 required']) ?>
		<div class="col-xs-3">
				<?php echo form_input (['name' => 'daterangepicker', 'class' => 'form-control input-sm', 'id' => 'daterangepicker']) ?>
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
				<?php echo form_label(lang('Reports.sale_type'), 'reports_sale_type_label', ['class' => 'required control-label col-xs-2']) ?>
				<div id='report_sale_type' class="col-xs-3">
					<?php echo form_dropdown('sale_type', esc($sale_type_options, 'attr'), 'complete', ['id' => 'input_type', 'class' => 'form-control']) ?>
				</div>
			<?php
			}
			elseif($mode == 'receiving')
			{
			?>
				<?php echo form_label(lang('Reports.receiving_type'), 'reports_receiving_type_label', ['class' => 'required control-label col-xs-2']) ?>
				<div id='report_receiving_type' class="col-xs-3">
					<?php echo form_dropdown(
							'receiving_type',
							[
								'all' => lang('Reports.all'),
								'receiving' => lang('Reports.receivings'),
								'returns' => lang('Reports.returns'),
								'requisitions' => lang('Reports.requisitions')
							],
							'all',
							['id' => 'input_type', 'class' => 'form-control']) ?>
				</div>
			<?php
			}
			?>
		</div>
	<?php
	}
	?>

	<?php	
	if (isset($discount_type_options))
	{
	?>
		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Reports.discount_type'), 'reports_discount_type_label', ['class' => 'required control-label col-xs-2']) ?>
			<div id='report_discount_type' class="col-xs-3">
				<?php echo form_dropdown('discount_type', esc($discount_type_options, 'attr'), esc(config('OSPOS')->settings['default_sales_discount_type'], 'attr'), ['id' => 'discount_type_id', 'class' => 'form-control']) ?>
			</div>
		</div>
	<?php
	}
	?>

	<?php	
	if (!empty($stock_locations) && count($stock_locations) > 2)
	{
	?>
		<div class="form-group form-group-sm">
			<?php echo form_label(lang('Reports.stock_location'), 'reports_stock_location_label', ['class' => 'required control-label col-xs-2']) ?>
			<div id='report_stock_location' class="col-xs-3">
				<?php echo form_dropdown('stock_location', esc($stock_locations, 'attr'), 'all', ['id' => 'location_id', 'class' => 'form-control']) ?>
			</div>
		</div>
	<?php
	}
	?>

	<?php
		echo form_button ([
			'name' => 'generate_report',
			'id' => 'generate_report',
			'content'=>lang('Common.submit'),
			'class' => 'btn btn-primary btn-sm']
	);	?>
<?php echo form_close() ?>

<?php echo view('partial/footer') ?>

<script type="text/javascript">
$(document).ready(function()
{
	<?php echo view('partial/daterangepicker') ?>

	$("#generate_report").click(function()
	{		
		window.location = [window.location, start_date, end_date, $("#input_type").val() || 0, $("#location_id").val() || 'all', $("#discount_type_id").val() || 0 ].join("/");
	});
});
</script>