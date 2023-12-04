<?= view('partial/header') ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>

<ul class="nav nav-tabs" data-tabs="tabs">
	<li class="active" role="presentation">
		<a data-toggle="tab" href="#info_tab" title="<?= lang('Config.info_configuration') ?>"><?= lang('Config.info') ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#general_tab" title="<?= lang('Config.general_configuration') ?>"><?= lang('Config.general') ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#tax_tab" title="<?= lang('Config.tax_configuration') ?>"><?= lang('Config.tax') ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#locale_tab" title="<?= lang('Config.locale_configuration') ?>"><?= lang('Config.locale') ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#barcode_tab" title="<?= lang('Config.barcode_configuration') ?>"><?= lang('Config.barcode') ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#stock_tab" title="<?= lang('Config.location_configuration') ?>"><?= lang('Config.location') ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#receipt_tab" title="<?= lang('Config.receipt_configuration') ?>"><?= lang('Config.receipt') ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#invoice_tab" title="<?= lang('Config.invoice_configuration') ?>"><?= lang('Config.invoice') ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#reward_tab" title="<?= lang('Config.reward_configuration') ?>"><?= lang('Config.reward') ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#table_tab" title="<?= lang('Config.table_configuration') ?>"><?= lang('Config.table') ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#system_tab" title="<?= lang('Config.system_conf') ?>"><?= lang('Config.system_conf') ?></a>
	</li>
</ul>

<div class="tab-content">
	<div class="tab-pane fade in active" id="info_tab">
		<?= view('configs/info_config') ?>
	</div>
	<div class="tab-pane" id="general_tab">
		<?= view('configs/general_config') ?>
	</div>
	<div class="tab-pane" id="tax_tab">
		<?= view('configs/tax_config') ?>
	</div>
	<div class="tab-pane" id="locale_tab">
		<?= view('configs/locale_config') ?>
	</div>
	<div class="tab-pane" id="barcode_tab">
		<?= view('configs/barcode_config') ?>
	</div>
	<div class="tab-pane" id="stock_tab">
		<?= view('configs/stock_config') ?>
	</div>
	<div class="tab-pane" id="receipt_tab">
		<?= view('configs/receipt_config') ?>
	</div>
	<div class="tab-pane" id="invoice_tab">
		<?= view('configs/invoice_config') ?>
	</div>
	<div class="tab-pane" id="reward_tab">
		<?= view('configs/reward_config') ?>
	</div>
	<div class="tab-pane" id="table_tab">
		<?= view('configs/table_config') ?>
	</div>
	<div class="tab-pane" id="system_tab">
		<?= view('configs/system_config') ?>
	</div>
</div>

<?= view('partial/footer') ?>
