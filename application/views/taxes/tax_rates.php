<script type="text/javascript">
	$(document).ready(function() {
		<?php $this->load->view('partial/bootstrap_tables_locale'); ?>
		table_support.init({
			resource: '<?= site_url($controller_name); ?>',
			headers: <?= $tax_rate_table_headers; ?>,
			pageSize: <?= $this->config->item('lines_per_page'); ?>,
			uniqueId: 'tax_rate_id'
		});
	});
</script>

<?php
$title_rates['config_title'] = $this->lang->line('taxes_tax_rates_configuration');
$this->load->view('configs/config_header', $title_rates);
?>

<div class="btn-toolbar justify-content-end mb-3" role="toolbar">
	<button class="btn btn-primary modal-dlg" data-btn-submit="<?= $this->lang->line('common_submit') ?>" data-href="<?= site_url($controller_name . "/view"); ?>" title="<?= $this->lang->line($controller_name . '_new'); ?>">
		<i class="bi bi-piggy-bank pe-1"></i><?= $this->lang->line($controller_name . '_new'); ?>
	</button>
</div>

<div class="btn-toolbar mb-3" role="toolbar">
	<button id="delete" class="btn btn-outline-secondary">
		<i class="bi bi-trash pe-1"></i><?= $this->lang->line('common_delete'); ?>
	</button>
</div>

<div id="table_holder">
	<table id="table"></table>
</div>