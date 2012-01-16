<?php $this->load->view("partial/header"); ?>
<div id="edit_sale_wrapper">
<?php 
if ($success)
{
?>
	<h1><?php echo $this->lang->line('sales_delete_successful'); ?></h1>
<?php	
}
else
{
?>
	<h1><?php echo $this->lang->line('sales_delete_unsuccessful'); ?></h1>
<?php
}
?>
</div>
<?php $this->load->view("partial/footer"); ?>