<?php
/**
 * @var string $controller_name
 * @var string $table_headers
 * @var array $config
 */
?>
<?= view('partial/header') ?>

<script type="application/javascript">
$(document).ready(function()
{
	<?= view('partial/bootstrap_tables_locale') ?>

	table_support.init({
		resource: '<?= esc($controller_name) ?>',
		headers: <?= $table_headers ?>,
		pageSize: <?= $config['lines_per_page'] ?>,
		uniqueId: 'item_kit_id'
	});

	$('#generate_barcodes').click(function()
	{
		window.open(
			'index.php/item_kits/generateBarcodes/'+table_support.selected_ids().join(':'),
			'_blank' // <- This is what makes it open in a new window.
		);
	});
});

</script>

<div id="title_bar" class="btn-toolbar">
	<button class='btn btn-info btn-sm pull-right modal-dlg' data-btn-submit='<?= lang('Common.submit') ?>' data-href='<?= esc("$controller_name/view") ?>'
			title='<?= lang(ucfirst($controller_name) . '.new') ?>'>
		<span class="glyphicon glyphicon-tags">&nbsp</span><?= lang(ucfirst($controller_name) . '.new') ?>
	</button>
</div>

<div id="toolbar">
	<div class="pull-left btn-toolbar">
		<button id="delete" class="btn btn-default btn-sm">
			<span class="glyphicon glyphicon-trash">&nbsp</span><?= lang('Common.delete') ?>
		</button>

		<button id="generate_barcodes" class="btn btn-default btn-sm" data-href='<?= esc("$controller_name/generateBarcodes") ?>'>
			<span class="glyphicon glyphicon-barcode">&nbsp</span><?= lang('Items.generate_barcodes') ?>
		</button>
	</div>
</div>

<div id="table_holder">
	<table id="table"></table>
</div>

<?= view('partial/footer') ?>
