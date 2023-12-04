<?php
/**
 * @var string $controller_name
 * @var string $tax_rate_table_headers
 * @var array $config
 */
?>
<script type="text/javascript">
$(document).ready(function()
{
	<?= view('partial/bootstrap_tables_locale') ?>
	table_support.init({
		resource: '<?= esc($controller_name) ?>',
		headers: <?= $tax_rate_table_headers ?>,
		pageSize: <?= $config['lines_per_page'] ?>,
		uniqueId: 'tax_rate_id'
	});
});
</script>

<div id="title_bar" class="btn-toolbar">
	<button class='btn btn-info btn-sm pull-right modal-dlg' data-btn-submit='<?= lang('Common.submit') ?>' data-href='<?= esc("$controller_name/view") ?>'
			title='<?= lang("$controller_name.new") ?>'>
		<span class="glyphicon glyphicon-usd">&nbsp</span><?= lang("$controller_name.new") ?>
	</button>
</div>

<div id="toolbar">
	<div class="pull-left btn-toolbar">
		<button id="delete" class="btn btn-default btn-sm">
			<span class="glyphicon glyphicon-trash">&nbsp</span><?= lang('Common.delete') ?>
		</button>
	</div>
</div>

<div id="table_holder">
	<table id="table"></table>
</div>
