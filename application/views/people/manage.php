<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
$(document).ready(function() 
{
	table_support.init('<?php echo site_url($controller_name);?>', <?php echo $table_headers; ?>);
});

</script>

<div id="title_bar">
	<?php
	if ($controller_name == 'customers')
	{
	?>
		<?php echo anchor("$controller_name/excel_import",
			"<div class='btn btn-info btn-sm pull-right'><span>" . $this->lang->line('common_import_excel') . "</span></div>",
			array('class'=>'modal-dlg modal-btn-submit', 'title'=>$this->lang->line('customers_import_items_excel'))); ?>

		<?php echo anchor("$controller_name/view/-1",
			"<div class='btn btn-info btn-sm pull-right' style='margin-right: 10px;'><span>" . $this->lang->line('customers_new') . "</span></div>",
			array('class'=>'modal-dlg modal-btn-submit', 'title'=>$this->lang->line('customers_new'))); ?>
	<?php
	}
	else
	{
	?>
		<?php echo anchor("$controller_name/view/-1",
			"<div class='btn btn-info btn-sm pull-right'><span>" . $this->lang->line($controller_name . '_new') . "</span></div>",
			array('class'=>'modal-dlg modal-btn-submit', 'title'=>$this->lang->line($controller_name . '_new'))); ?>
	<?php
	}
	?>
</div>

<div id="toolbar">
	<div class="pull-left arrow-left">

		<?php echo anchor("$controller_name/delete", '<div class="btn btn-default btn-sm"><span>' . $this->lang->line("common_delete") . '</span></div>', array('id'=>'delete')); ?>
		<span><a href="#" id="email"><div class="btn btn-default btn-sm"><?php echo $this->lang->line("common_email");?></div></a></span></div>
	</div>
</div>

<div id="table_holder">
	<table id="table"></table>
</div>

<?php $this->load->view("partial/footer"); ?>
