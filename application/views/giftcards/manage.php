<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
	$(document).ready(function() {
		<?php $this->load->view('partial/bootstrap_tables_locale'); ?>
		table_support.init({
			resource: '<?= site_url($controller_name); ?>',
			headers: <?= $table_headers; ?>,
			pageSize: <?= $this->config->item('lines_per_page'); ?>,
			uniqueId: 'giftcard_id'
		});
	});
</script>

<div class="d-flex justify-content-end">
	<button class='btn btn-primary' data-btn-submit='<?= $this->lang->line('common_submit') ?>' data-href='<?= site_url($controller_name . "/view"); ?>' title='<?= $this->lang->line($controller_name . '_new'); ?>'>
		<i class="bi bi-heart pe-1"></i><?= $this->lang->line($controller_name . '_new'); ?>
	</button>
</div>

<button id="delete" class="btn btn-outline-secondary">
	<i class="bi bi-eraser pe-1"></i><?= $this->lang->line("common_delete"); ?>
</button>

<div id="table_holder">
	<table id="table"></table>
</div>

<?php $this->load->view("partial/footer"); ?>