<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	$(document).ready(function() {
		<?php $this->load->view('partial/bootstrap_tables_locale'); ?>

		table_support.init({
			resource: '<?= site_url($controller_name); ?>',
			headers: <?= $table_headers; ?>,
			pageSize: <?= $this->config->item('lines_per_page'); ?>,
			uniqueId: 'item_kit_id'
		});

		$('#generate_barcodes').click(function() {
			window.open(
				'index.php/item_kits/generate_barcodes/' + table_support.selected_ids().join(':'),
				'_blank' // <- This is what makes it open in a new window.
			);
		});
	});
</script>

<div class="btn-toolbar justify-content-end mb-3" role="toolbar">
	<button class="btn btn-primary modal-dlg" data-btn-submit="<?= $this->lang->line('common_submit') ?>" data-href="<?= site_url($controller_name . '/view'); ?>" title="<?= $this->lang->line($controller_name . '_new'); ?>">
		<i class="bi bi-tags pe-1"></i><?= $this->lang->line($controller_name . '_new'); ?>
	</button>
</div>

<div class="btn-toolbar mb-3" role="toolbar">
	<button id="delete" class="btn btn-outline-secondary me-2">
		<i class="bi bi-trash pe-1"></i><?= $this->lang->line('common_delete'); ?>
	</button>
	<button id="generate_barcodes" class="btn btn-outline-secondary" data-href="<?= site_url($controller_name . '/generate_barcodes'); ?>">
		<i class="bi bi-upc pe-1"></i><?= $this->lang->line('items_generate_barcodes'); ?>
	</button>
</div>

<div id="table_holder">
	<table id="table"></table>
</div>

<?php $this->load->view("partial/footer"); ?>