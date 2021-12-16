<?php echo view('partial/header') ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>

<ul class="nav nav-tabs" data-tabs="tabs">
	<li class="active" role="presentation">
		<a data-toggle="tab" href="#info_tab" title="<?php echo lang('Config.info_configuration') ?>"><?php echo lang('Config.info') ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#general_tab" title="<?php echo lang('Config.general_configuration') ?>"><?php echo lang('Config.general') ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#tax_tab" title="<?php echo lang('Config.tax_configuration') ?>"><?php echo lang('Config.tax') ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#locale_tab" title="<?php echo lang('Config.locale_configuration') ?>"><?php echo lang('Config.locale') ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#barcode_tab" title="<?php echo lang('Config.barcode_configuration') ?>"><?php echo lang('Config.barcode') ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#stock_tab" title="<?php echo lang('Config.location_configuration') ?>"><?php echo lang('Config.location') ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#receipt_tab" title="<?php echo lang('Config.receipt_configuration') ?>"><?php echo lang('Config.receipt') ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#invoice_tab" title="<?php echo lang('Config.invoice_configuration') ?>"><?php echo lang('Config.invoice') ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#reward_tab" title="<?php echo lang('Config.reward_configuration') ?>"><?php echo lang('Config.reward') ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#table_tab" title="<?php echo lang('Config.table_configuration') ?>"><?php echo lang('Config.table') ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#system_tab" title="<?php echo lang('Config.system_conf') ?>"><?php echo lang('Config.system_conf') ?></a>
	</li>
</ul>

<div class="tab-content">
	<div class="tab-pane fade in active" id="info_tab">
		<?php echo view('configs/info_config') ?>
	</div>
	<div class="tab-pane" id="general_tab">
		<?php echo view('configs/general_config') ?>
	</div>
	<div class="tab-pane" id="tax_tab">
		<?php echo view('configs/tax_config') ?>
	</div>
	<div class="tab-pane" id="locale_tab">
		<?php echo view('configs/locale_config') ?>
	</div>
	<div class="tab-pane" id="barcode_tab">
		<?php echo view('configs/barcode_config') ?>
	</div>
	<div class="tab-pane" id="stock_tab">
		<?php echo view('configs/stock_config') ?>
	</div>
	<div class="tab-pane" id="receipt_tab">
		<?php echo view('configs/receipt_config') ?>
	</div>
	<div class="tab-pane" id="invoice_tab">
		<?php echo view('configs/invoice_config') ?>
	</div>
	<div class="tab-pane" id="reward_tab">
		<?php echo view('configs/reward_config') ?>
	</div>
	<div class="tab-pane" id="table_tab">
		<?php echo view('configs/table_config') ?>
	</div>
	<div class="tab-pane" id="system_tab">
		<?php echo view('configs/system_config') ?>
	</div>
</div>

<?php echo view('partial/footer') ?>
