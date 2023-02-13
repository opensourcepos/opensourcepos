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
		headers: <?php echo esc($table_headers, 'js') ?>,
		pageSize: <?php echo $config['lines_per_page'] ?>,
		uniqueId: 'people.person_id',
		enableActions: function()
		{
			var email_disabled = $("td input:checkbox:checked").parents("tr").find("td a[href^='mailto:']").length == 0;
			$("#email").prop('disabled', email_disabled);
		}
	});

	$("#email").click(function(event)
	{
		var recipients = $.map($("tr.selected a[href^='mailto:']"), function(element)
		{
			return $(element).attr('href').replace(/^mailto:/, '');
		});
		location.href = "mailto:" + recipients.join(",");
	});
});
</script>

<div id="title_bar" class="btn-toolbar">
	<?php
	if ($controller_name == 'customers')	//TODO: === ?
	{
	?>
		<button class='btn btn-info btn-sm pull-right modal-dlg' data-btn-submit='<?php echo lang('Common.submit') ?>' data-href='<?php echo esc(site_url("$controller_name/csv_import"), 'url') ?>'
				title='<?php echo lang('Customers.import_items_csv') ?>'>
			<span class="glyphicon glyphicon-import">&nbsp</span><?php echo lang('Common.import_csv') ?>
		</button>
	<?php
	}
	?>
	<button class='btn btn-info btn-sm pull-right modal-dlg' data-btn-submit='<?php echo lang('Common.submit') ?>' data-href='<?php echo esc(site_url("$controller_name/view"), 'url') ?>'
			title='<?php echo lang("$controller_name.new") ?>'>
		<span class="glyphicon glyphicon-user">&nbsp</span><?php echo lang("$controller_name.new") ?>
	</button>
</div>

<div id="toolbar">
	<div class="pull-left btn-toolbar">
		<button id="delete" class="btn btn-default btn-sm">
			<span class="glyphicon glyphicon-trash">&nbsp</span><?php echo lang('Common.delete') ?>
		</button>
		<button id="email" class="btn btn-default btn-sm">
			<span class="glyphicon glyphicon-envelope">&nbsp</span><?php echo lang('Common.email') ?>
		</button>
	</div>
</div>

<div id="table_holder">
	<table id="table"></table>
</div>

<?php echo view('partial/footer') ?>
