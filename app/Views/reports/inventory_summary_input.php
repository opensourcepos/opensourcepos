<?php
/**
 * @var array $stock_locations
 * @var array $item_count
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
		<?php echo form_label(lang('Reports.stock_location'), 'reports_stock_location_label', ['class' => 'required control-label col-xs-2']) ?>
		<div id='report_stock_location' class="col-xs-3">
			<?php echo form_dropdown('stock_location', esc($stock_locations, 'attr'), 'all', 'id="location_id" class="form-control"') ?>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?php echo form_label(lang('Reports.item_count'), 'reports_item_count_label', ['class' => 'required control-label col-xs-2']) ?>
		<div id='report_item_count' class="col-xs-3">
			<?php echo form_dropdown('item_count', esc($item_count, 'attr'), 'all', 'id="item_count" class="form-control"') ?>
		</div>
	</div>

	<?php
		echo form_button ([
			'name' => 'generate_report',
			'id' => 'generate_report',
			'content' => lang('Common.submit'),
			'class' => 'btn btn-primary btn-sm'
	]) ?>
<?php echo form_close() ?>

<?php echo view('partial/footer') ?>

<script type="text/javascript">
$(document).ready(function()
{
	$("#generate_report").click(function()
	{
		window.location = [window.location, $("#location_id").val(), $("#item_count").val()].join("/");
	});
});
</script>