<?php
	echo view('partial/header')

/**
 * @var string $controller_name
 * @var string $table_headers
 */
?>

<script type="text/javascript">
	$(document).ready(function()
	{
		<?php echo view('partial/bootstrap_tables_locale') ?>

		table_support.init({
			resource: '<?php echo esc($controller_name) ?>',
			headers: <?php echo $table_headers ?>,
			pageSize: <?php echo $config['lines_per_page'] ?>,
			uniqueId: 'definition_id'
		});
	});
</script>

<div id="title_bar" class="btn-toolbar print_hide">

	<button class='btn btn-info btn-sm pull-right modal-dlg' data-btn-submit='<?php echo lang('Common.submit') ?>' data-href='<?php echo esc("$controller_name/view") ?>'
	        title='<?php echo lang("$controller_name.new") ?>'>
		<span class="glyphicon glyphicon-star">&nbsp</span><?php echo lang("$controller_name.new") ?>
	</button>
</div>

<div id="toolbar">
	<div class="pull-left form-inline" role="toolbar">
		<button id="delete" class="btn btn-default btn-sm print_hide">
			<span class="glyphicon glyphicon-trash">&nbsp</span><?php echo lang('Common.delete') ?>
		</button>
	</div>
</div>

<div id="table_holder">
	<table id="table"></table>
</div>

<?php echo view('partial/footer') ?>
