<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
$(document).ready(function()
{
	table_support.init('<?php echo site_url($controller_name);?>', <?php echo $table_headers; ?>);
	table_support.init_delete('<?php echo $this->lang->line($controller_name."_confirm_delete")?>');
});
</script>

<div id="title_bar" class="btn-toolbar">
	<button class='btn btn-info btn-sm pull-right modal-dlg modal-btn-submit' data-href='<?php echo site_url($controller_name."/view"); ?>'
			title='<?php echo $this->lang->line($controller_name.'_new'); ?>'>
		<span class="glyphicon glyphicon-heart"></span><?php echo $this->lang->line($controller_name . '_new'); ?>
	</button>
</div>

<div id="toolbar">
	<div class="pull-left btn-toolbar">
		<button id="delete" class="btn btn-default btn-sm" data-href='<?php echo site_url($controller_name."/delete"); ?>'><span class="glyphicon glyphicon-trash"></span>
			<?php echo $this->lang->line("common_delete");?></button>
	</div>
</div>

<div id="table_holder">
	<table id="table"></table>
</div>

<?php $this->load->view("partial/footer"); ?>