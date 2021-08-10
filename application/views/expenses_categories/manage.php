<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	$(document).ready(function() {
		<?php $this->load->view('partial/bootstrap_tables_locale'); ?>

		table_support.init({
			resource: '<?= site_url($controller_name); ?>',
			headers: <?= $table_headers; ?>,
			pageSize: <?= $this->config->item('lines_per_page'); ?>,
			uniqueId: 'expense_category_id',

		});

		// when any filter is clicked and the dropdown window is closed
		$('#filters').on('hidden.bs.select', function(e) {
			table_support.refresh();
		});
	});
</script>

<div class="btn-toolbar justify-content-end d-print-none mb-3" role="toolbar">
	<button class="btn btn-primary modal-dlg" data-btn-submit="<?= $this->lang->line('common_submit') ?>" data-href="<?= site_url($controller_name . '/view'); ?>" title="<?= $this->lang->line($controller_name . '_new'); ?>">
		<i class="bi bi-list pe-1"></i><?= $this->lang->line($controller_name . '_new'); ?>
	</button>
</div>

<div class="btn-toolbar mb-3" role="toolbar">
	<button id="delete" class="btn btn-outline-secondary d-print-none">
		<i class="bi bi-trash pe-1"></i><?= $this->lang->line('common_delete'); ?>
	</button>
</div>

<div id="table_holder">
	<table id="table"></table>
</div>

<?php $this->load->view("partial/footer"); ?>