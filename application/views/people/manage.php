<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	$(document).ready(function() {
		<?php $this->load->view('partial/bootstrap_tables_locale'); ?>

		table_support.init({
			resource: '<?= site_url($controller_name); ?>',
			headers: <?= $table_headers; ?>,
			pageSize: <?= $this->config->item('lines_per_page'); ?>,
			uniqueId: 'people.person_id',
			enableActions: function() {
				var email_disabled = $("td input:checkbox:checked").parents("tr").find("td a[href^='mailto:']").length == 0;
				$("#email").prop('disabled', email_disabled);
			}
		});

		$("#email").click(function(event) {
			var recipients = $.map($("tr.selected a[href^='mailto:']"), function(element) {
				return $(element).attr('href').replace(/^mailto:/, '');
			});
			location.href = "mailto:" + recipients.join(",");
		});
	});
</script>

<div class="btn-toolbar justify-content-end mb-3" role="toolbar">
	<?php if ($controller_name == 'customers') { ?>
		<button class="btn btn-primary modal-dlg me-2" data-btn-submit="<?= $this->lang->line('common_submit') ?>" data-href="<?= site_url($controller_name . '/csv_import'); ?>" title="<?= $this->lang->line('customers_import_items_csv'); ?>">
			<i class="bi bi-box-arrow-in-down-right pe-1"></i><?= $this->lang->line('common_import_csv'); ?>
		</button>
	<?php } ?>
	<button class="btn btn-primary modal-dlg" data-btn-submit="<?= $this->lang->line('common_submit') ?>" data-href="<?= site_url($controller_name . '/view'); ?>" title="<?= $this->lang->line($controller_name . '_new'); ?>">
		<i class="bi bi-person pe-1"></i><?= $this->lang->line($controller_name . '_new'); ?>
	</button>
</div>

<div class="btn-toolbar mb-3" role="toolbar">
	<button id="delete" class="btn btn-outline-secondary me-2">
		<i class="bi bi-trash pe-1"></i><?= $this->lang->line('common_delete'); ?>
	</button>
	<button id="email" class="btn btn-outline-secondary">
		<i class="bi bi-envelope pe-1"></i><?= $this->lang->line('common_email'); ?>
	</button>
</div>

<div id="table_holder">
	<table id="table"></table>
</div>

<?php $this->load->view("partial/footer"); ?>