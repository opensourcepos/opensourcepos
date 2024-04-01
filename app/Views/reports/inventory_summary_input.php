<?php
/**
 * @var array $stock_locations
 * @var array $item_count
 */
?>
<?= view('partial/header') ?>

<script type="application/javascript">
	dialog_support.init("a.modal-dlg");
</script>


<div id="page_title"><?= lang('Reports.report_input') ?></div>

<?php
if(isset($error))
{
	echo '<div class=\'alert alert-dismissible alert-danger\'>' . esc($error) . '</div>';
}
?>

<?= form_open('#', ['id' => 'item_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']) ?>

	<div class="form-group form-group-sm">
		<?= form_label(lang('Reports.stock_location'), 'reports_stock_location_label', ['class' => 'required control-label col-xs-2']) ?>
		<div id='report_stock_location' class="col-xs-3">
			<?= form_dropdown('stock_location', $stock_locations, 'all', 'id="location_id" class="form-control"') ?>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?= form_label(lang('Reports.item_count'), 'reports_item_count_label', ['class' => 'required control-label col-xs-2']) ?>
		<div id='report_item_count' class="col-xs-3">
			<?= form_dropdown('item_count', $item_count, 'all', 'id="item_count" class="form-control"') ?>
		</div>
	</div>

	<?php
		echo form_button ([
			'name' => 'generate_report',
			'id' => 'generate_report',
			'content' => lang('Common.submit'),
			'class' => 'btn btn-primary btn-sm'
	]) ?>
<?= form_close() ?>

<?= view('partial/footer') ?>

<script type="application/javascript">
$(document).ready(function()
{
	$("#generate_report").click(function()
	{
		window.location = [window.location, $("#location_id").val(), $("#item_count").val()].join("/");
	});
});
</script>
