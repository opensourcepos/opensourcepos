<?php $this->load->view("partial/header"); ?>
<div id="page_title" style="margin-bottom:8px;"><?php echo $this->lang->line('reports_reports'); ?></div>
<div id="welcome_message"><?php echo $this->lang->line('reports_welcome_message'); ?>
<ul id="report_list">
	<li><h3><?php echo $this->lang->line('reports_graphical_reports'); ?></h3>
		<ul>
			<?php
			foreach($grants as $grant) 
			{
				show_report_if_allowed($grant, 'graphical_summary');
			}
			?>
		</ul>
	</li>
	
	<li><h3><?php echo $this->lang->line('reports_summary_reports'); ?></h3>
		<ul>
			<?php 
			foreach($grants as $grant) 
			{
				show_report_if_allowed($grant, 'summary');
			}
			?>
		</ul>
	</li>
	
	<li><h3><?php echo $this->lang->line('reports_detailed_reports'); ?></h3>
		<ul>
		<?php 			
			show_report_if_allowed($grants, 'detailed', 'sales');
			show_report_if_allowed($grants, 'detailed', 'receivings');
			show_report_if_allowed($grants, 'specific', 'customer', 'customers');
			show_report_if_allowed($grants, 'specific', 'discount', 'sales');
			show_report_if_allowed($grants, 'specific', 'employee', 'employees');
		?>
		</ul>
	</li>
	<?php 
	if ($this->Employee->has_permission('reports_inventory', $this->session->userdata('person_id')))
	{
	?>
	<li><h3><?php echo $this->lang->line('reports_inventory_reports'); ?></h3>
		<ul>
		<?php 
			show_report_if_allowed($grants, '', 'inventory_low', 'inventory');	
			show_report_if_allowed($grants, '', 'inventory_summary', 'inventory');
		?>
		</ul>
	</li>
	<?php 
	}
	?>
</ul>
<?php
if(isset($error))
{
	echo "<div class='error_message'>".$error."</div>";
}
?>
<?php $this->load->view("partial/footer"); ?>