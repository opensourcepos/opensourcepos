<?php
/**
 * @var string $controller_name
 * @var string $table_headers
 */
?>
<?php echo view('partial/header') ?>
<script type="text/javascript">
$(document).ready(function()
{
	<?php echo view('partial/bootstrap_tables_locale') ?>
	table_support.init({
		resource: '<?php echo esc(site_url($controller_name), 'url') ?>',
		headers: <?php echo esc($table_headers) ?>,
		pageSize: <?php echo $config['lines_per_page'] ?>,
		uniqueId: 'giftcard_id'
	});
});
</script>

<div id="title_bar" class="btn-toolbar">
	<button class='btn btn-info btn-sm pull-right modal-dlg' data-btn-submit='<?php echo lang('Common.submit') ?>' data-href='<?php echo esc(site_url("$controller_name/view"), 'url') ?>'
			title='<?php echo lang($controller_name . '.new') ?>'>
		<span class="glyphicon glyphicon-heart">&nbsp</span><?php echo lang($controller_name . '.new') ?>
	</button>
</div>

<div id="toolbar">
	<div class="pull-left btn-toolbar">
		<button id="delete" class="btn btn-default btn-sm">
			<span class="glyphicon glyphicon-trash">&nbsp</span><?php echo lang('Common.delete') ?>
		</button>
	</div>
</div>

<div id="table_holder">
	<table id="table"></table>
</div>

<?php echo view('partial/footer') ?>
