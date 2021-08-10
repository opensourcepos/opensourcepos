<?php
/**
 * @var array $grants
 */

use app\Models\Employee;

?>
<?php echo view('partial/header') ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>

<?php
if(isset($error))
{
	echo '<div class=\'alert alert-dismissible alert-danger\'>' . esc($error) . '</div>';
}
?>

<div class="row">
	<div class="col-md-4">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<h3 class="panel-title"><span class="glyphicon glyphicon-stats">&nbsp</span><?php echo lang('Reports.graphical_reports') ?></h3>
			</div>
			<div class="list-group">
				<?php
				foreach($grants as $grant) 
				{
					if (preg_match('/reports_/', $grant['permission_id']) && !preg_match('/(inventory|receivings)/', $grant['permission_id']))
					{
						show_report('graphical_summary', $grant['permission_id']);
					}
				}
				?>
			 </div>
		</div>
	</div>

	<div class="col-md-4">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<h3 class="panel-title"><span class="glyphicon glyphicon-list">&nbsp</span><?php echo lang('Reports.summary_reports') ?></h3>
			</div>
			<div class="list-group">
				<?php 
				foreach($grants as $grant) 
				{
					if (preg_match('/reports_/', $grant['permission_id']) && !preg_match('/(inventory|receivings)/', $grant['permission_id']))
					{
						show_report('summary', $grant['permission_id']);
					}
				}
				?>
			 </div>
		</div>
	</div>

	<div class="col-md-4">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<h3 class="panel-title"><span class="glyphicon glyphicon-list-alt">&nbsp</span><?php echo lang('Reports.detailed_reports') ?></h3>
			</div>
			<div class="list-group">
				<?php 			
				$person_id = $this->session->get('person_id');
				show_report_if_allowed('detailed', 'sales', $person_id);
				show_report_if_allowed('detailed', 'receivings', $person_id);
				show_report_if_allowed('specific', 'customer', $person_id, 'reports_customers');
				show_report_if_allowed('specific', 'discount', $person_id, 'reports_discounts');
				show_report_if_allowed('specific', 'employee', $person_id, 'reports_employees');
				show_report_if_allowed('specific', 'supplier', $person_id, 'reports_suppliers');
				?>
			 </div>
		</div>

		<?php
		$employee = model(Employee::class);
		if ($employee->has_grant('reports_inventory', $this->session->get('person_id')))
		{
		?>
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title"><span class="glyphicon glyphicon-book">&nbsp</span><?php echo lang('Reports.inventory_reports') ?></h3>
				</div>
				<div class="list-group">
				<?php 
				show_report('', 'reports_inventory_low');
				show_report('', 'reports_inventory_summary');
				?>
				</div>
			</div>
		<?php 
		}
		?>
	</div>
</div>

<?php echo view('partial/footer') ?>
