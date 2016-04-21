<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
$(document).ready(function()
{
	table_support.init('<?php echo site_url($controller_name);?>', <?php echo $table_headers; ?>);
	table_support.init_delete('<?php echo $this->lang->line($controller_name."_confirm_delete")?>');

	$("#email").click(function(evvent)
	{
		do_email();
	});

	var do_email = function()
	{
		var recipients = $.map($("tr.selected a[href^='mailto:']"), function(element)
		{
			return $(element).attr('href').replace(/^mailto:/, '');
		});
		location.href = "mailto:" + recipients.join(",");
	};
// TODO override enable_actions to enable/disable email HERE
	var enable_actions = table_support.enable_actions;
/*	table_support.enable_actions = function ()
	{
		enable_actions();

	}
*/
});

</script>

<div id="title_bar" class="btn-toolbar">
	<?php
	if ($controller_name == 'customers')
	{
	?>
		<button class='btn btn-info btn-sm pull-right modal-dlg modal-btn-submit' data-href='<?php echo site_url($controller_name."/excel_import"); ?>'
				title='<?php echo $this->lang->line('customers_import_items_excel'); ?>'>
			<span class="glyphicon glyphicon-import"></span><?php echo $this->lang->line('common_import_excel'); ?>
		</button>
		<?php
	}
	?>
		<button class='btn btn-info btn-sm pull-right modal-dlg modal-btn-submit' data-href='<?php echo site_url($controller_name."/view"); ?>'
				title='<?php echo $this->lang->line($controller_name. '_new'); ?>'>
			<span class="glyphicon glyphicon-user"></span><?php echo $this->lang->line($controller_name. '_new'); ?>
		</button>
</div>

<div id="toolbar">
	<div class="pull-left btn-toolbar">
		<button id="delete" class="btn btn-default btn-sm" data-href='<?php echo site_url($controller_name."/delete"); ?>'><span class="glyphicon glyphicon-trash"></span>
			<?php echo $this->lang->line("common_delete");?></button>
		<button id="email" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-envelope"></span>
			<?php echo $this->lang->line("common_email");?></button>
	</div>
</div>

<div id="table_holder">
	<table id="table"></table>
</div>

<?php $this->load->view("partial/footer"); ?>
