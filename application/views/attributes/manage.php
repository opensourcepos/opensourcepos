<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	document.querySelector(document).ready(function() {
		<?php $this->load->view('partial/bootstrap_tables_locale'); ?>

		table_support.init({
			resource: '<?= site_url($controller_name); ?>',
			headers: <?= $table_headers; ?>,
			pageSize: <?= $this->config->item('lines_per_page'); ?>,
			uniqueId: 'definition_id'
		});
	});
</script>

<div id="title_bar" class="btn-toolbar d-flex justify-content-end d-print-none">
	<button class="btn btn-info modal-dlg" data-btn-submit="<?= $this->lang->line('common_submit') ?>" data-href="<?= site_url($controller_name . '/view'); ?>" title="<?= $this->lang->line($controller_name . '_new'); ?>">
		<i class="bi bi-star-fill"></i> <?= $this->lang->line($controller_name . '_new'); ?>
	</button>
</div>

<div id="toolbar">
	<div class="form-inline" role="toolbar">
		<button id="delete" class="btn btn-secondary d-print-none"><i class="bi bi-eraser"></i> <?= $this->lang->line('common_delete'); ?></button>
	</div>
</div>

<table id="table"></table>

<?php $this->load->view("partial/footer"); ?>