<?php
/**
 * @var int $person_id
 * @var array $permission_ids
 * @var array $grants
 */

use App\Models\Employee;

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
				<?php foreach ($permission_ids as $permission_id) :
					if (can_show_report($permission_id, ['inventory', 'receiving'])) :
						$link = get_report_link($permission_id, 'graphical_summary');
						?>
						<a class="list-group-item" href="<?php echo $link['path']; ?>"><?php echo $link['label']; ?></a>
						<?php
					endif;
				endforeach;
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
				<?php foreach ($permission_ids as $permission_id) :
					if (can_show_report($permission_id, ['inventory', 'receiving'])) :
						$link = get_report_link($permission_id, 'summary');
						?>
						<a class="list-group-item" href="<?php echo $link['path']; ?>"><?php echo $link['label']; ?></a>
						<?php
					endif;
				endforeach;
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
				$detailed_reports = [
					'reports_sales' => 'detailed',
					'reports_receivings' => 'detailed',
					'reports_customers' => 'specific',
					'reports_discounts' => 'specific',
					'reports_employees' => 'specific',
					'reports_suppliers' => 'specific',
				];
				foreach ($detailed_reports as $report_name => $prefix) :
					if (in_array($report_name, $permission_ids)) :
						$link = get_report_link($report_name, $prefix);
						?>
						<a class="list-group-item" href="<?php echo $link['path']; ?>"><?php echo $link['label']; ?></a>
					<?php
					endif;
				endforeach;
				?>
			 </div>
		</div>

		<?php
		if (in_array('reports_inventory', $permission_ids))
		 {
		?>
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title"><span class="glyphicon glyphicon-book">&nbsp</span><?php echo lang('Reports.inventory_reports') ?></h3>
				</div>
				<div class="list-group">
				<?php
				$inventory_low_report = get_report_link('reports_inventory_low');
				$inventory_summary_report = get_report_link('reports_inventory_summary');
				?>
					<a class="list-group-item" href="<?php echo $inventory_low_report['path']; ?>"><?php echo $inventory_low_report['label']; ?></a>
					<a class="list-group-item" href="<?php echo $inventory_summary_report['path']; ?>"><?php echo $inventory_summary_report['label']; ?></a>
				</div>
			</div>
		<?php 
		}
		?>
	</div>
</div>

<?php echo view('partial/footer') ?>
