<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>

<?php if (isset($error)) {
	echo "<div class='alert alert-dismissible alert-danger'>" . $error . "</div>";
} ?>

<div class="row">
	<div class="col-md-6 col-lg-4 mb-3">
		<div class="card bg-primary">
			<div class="card-header text-white">
				<i class="bi bi-bar-chart pe-2"></i><?= $this->lang->line('reports_graphical_reports'); ?>
			</div>
			<ul class="list-group list-group-flush">
				<?php
				foreach ($grants as $grant) {
					if (preg_match('/reports_/', $grant['permission_id']) && !preg_match('/(inventory|receivings)/', $grant['permission_id'])) {
						show_report('graphical_summary', $grant['permission_id']);
					}
				}
				?>
			</ul>
		</div>
	</div>

	<div class="col-md-6 col-lg-4 mb-3">
		<div class="card bg-primary">
			<div class="card-header text-white">
				<i class="bi bi-card-list pe-2"></i><?= $this->lang->line('reports_summary_reports'); ?>
			</div>
			<div class="list-group list-group-flush">
				<?php
				foreach ($grants as $grant) {
					if (preg_match('/reports_/', $grant['permission_id']) && !preg_match('/(inventory|receivings)/', $grant['permission_id'])) {
						show_report('summary', $grant['permission_id']);
					}
				}
				?>
			</div>
		</div>
	</div>

	<div class="col-lg-4 mb-3">
		<div class="card bg-primary mb-3">
			<div class="card-header text-white">
				<i class="bi bi-card-checklist pe-2"></i><?= $this->lang->line('reports_detailed_reports'); ?>
			</div>
			<div class="list-group list-group-flush">
				<?php
				$person_id = $this->session->userdata('person_id');
				show_report_if_allowed('detailed', 'sales', $person_id);
				show_report_if_allowed('detailed', 'receivings', $person_id);
				show_report_if_allowed('specific', 'customer', $person_id, 'reports_customers');
				show_report_if_allowed('specific', 'discount', $person_id, 'reports_discounts');
				show_report_if_allowed('specific', 'employee', $person_id, 'reports_employees');
				show_report_if_allowed('specific', 'supplier', $person_id, 'reports_suppliers');
				?>
			</div>
		</div>

		<?php if ($this->Employee->has_grant('reports_inventory', $this->session->userdata('person_id'))) { ?>
			<div class="card bg-primary">
				<div class="card-header text-white">
					<i class="bi bi-box pe-2"></i><?= $this->lang->line('reports_inventory_reports'); ?>
				</div>
				<div class="list-group list-group-flush">
					<?php
					show_report('', 'reports_inventory_low');
					show_report('', 'reports_inventory_summary');
					?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>

<?php $this->load->view("partial/footer"); ?>