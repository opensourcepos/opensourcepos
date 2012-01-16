
<?php $this->load->view("partial/header"); ?>
<div id="page_title" style="margin-bottom:8px;"><?php echo $this->lang->line('reports_reports'); ?></div>
<div id="welcome_message"><?php echo $this->lang->line('reports_welcome_message'); ?>
<ul id="report_list">
	<li><h3><?php echo $this->lang->line('reports_graphical_reports'); ?></h3>
		<ul>
			<li><a href="<?php echo site_url('reports/graphical_summary_sales');?>"><?php echo $this->lang->line('reports_sales'); ?></a></li>
			<li><a href="<?php echo site_url('reports/graphical_summary_categories');?>"><?php echo $this->lang->line('reports_categories'); ?></a></li>
			<li><a href="<?php echo site_url('reports/graphical_summary_customers');?>"><?php echo $this->lang->line('reports_customers'); ?></a></li>
			<li><a href="<?php echo site_url('reports/graphical_summary_suppliers');?>"><?php echo $this->lang->line('reports_suppliers'); ?></a></li>
			<li><a href="<?php echo site_url('reports/graphical_summary_items');?>"><?php echo $this->lang->line('reports_items'); ?></a></li>
			<li><a href="<?php echo site_url('reports/graphical_summary_employees');?>"><?php echo $this->lang->line('reports_employees'); ?></a></li>
			<li><a href="<?php echo site_url('reports/graphical_summary_taxes');?>"><?php echo $this->lang->line('reports_taxes'); ?></a></li>
			<li><a href="<?php echo site_url('reports/graphical_summary_discounts');?>"><?php echo $this->lang->line('reports_discounts'); ?></a></li>
			<li><a href="<?php echo site_url('reports/graphical_summary_payments');?>"><?php echo $this->lang->line('reports_payments'); ?></a></li>
		</ul>
	</li>
	
	<li><h3><?php echo $this->lang->line('reports_summary_reports'); ?></h3>
		<ul>
			<li><a href="<?php echo site_url('reports/summary_sales');?>"><?php echo $this->lang->line('reports_sales'); ?></a></li>
			<li><a href="<?php echo site_url('reports/summary_categories');?>"><?php echo $this->lang->line('reports_categories'); ?></a></li>
			<li><a href="<?php echo site_url('reports/summary_customers');?>"><?php echo $this->lang->line('reports_customers'); ?></a></li>
			<li><a href="<?php echo site_url('reports/summary_suppliers');?>"><?php echo $this->lang->line('reports_suppliers'); ?></a></li>
			<li><a href="<?php echo site_url('reports/summary_items');?>"><?php echo $this->lang->line('reports_items'); ?></a></li>
			<li><a href="<?php echo site_url('reports/summary_employees');?>"><?php echo $this->lang->line('reports_employees'); ?></a></li>
			<li><a href="<?php echo site_url('reports/summary_taxes');?>"><?php echo $this->lang->line('reports_taxes'); ?></a></li>
			<li><a href="<?php echo site_url('reports/summary_discounts');?>"><?php echo $this->lang->line('reports_discounts'); ?></a></li>
			<li><a href="<?php echo site_url('reports/summary_payments');?>"><?php echo $this->lang->line('reports_payments'); ?></a></li>
		</ul>
	</li>
	
	<li><h3><?php echo $this->lang->line('reports_detailed_reports'); ?></h3>
		<ul>
			<li><a href="<?php echo site_url('reports/detailed_sales');?>"><?php echo $this->lang->line('reports_sales'); ?></a></li>
			<li><a href="<?php echo site_url('reports/detailed_receivings');?>"><?php echo $this->lang->line('reports_receivings'); ?></a></li>
			<li><a href="<?php echo site_url('reports/specific_customer');?>"><?php echo $this->lang->line('reports_customer'); ?></a></li>
			<li><a href="<?php echo site_url('reports/specific_employee');?>"><?php echo $this->lang->line('reports_employee'); ?></a></li>
		</ul>
	
	</li>
	
	<li><h3><?php echo $this->lang->line('reports_inventory_reports'); ?></h3>
		<ul>
			<li><a href="<?php echo site_url('reports/inventory_low');?>"><?php echo $this->lang->line('reports_low_inventory'); ?></a></li>
			<li><a href="<?php echo site_url('reports/inventory_summary');?>"><?php echo $this->lang->line('reports_inventory_summary'); ?></a></li>
		</ul>
	</li>
</ul>
<?php
if(isset($error))
{
	echo "<div class='error_message'>".$error."</div>";
}
?>
<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript" language="javascript">
$(document).ready(function()
{
});
</script>
