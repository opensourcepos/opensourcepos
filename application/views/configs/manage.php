<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>

<ul class="nav nav-tabs" data-tabs="tabs">
	<li class="active" role="presentation">
		<a data-toggle="tab" href="#info_tab" title="<?php echo $this->lang->line('config_info_configuration'); ?>"><?php echo $this->lang->line('config_info'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#general_tab" title="<?php echo $this->lang->line('config_general_configuration'); ?>"><?php echo $this->lang->line('config_general'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#tax_tab" title="<?php echo $this->lang->line('config_tax_configuration'); ?>"><?php echo $this->lang->line('config_tax'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#locale_tab" title="<?php echo $this->lang->line('config_locale_configuration'); ?>"><?php echo $this->lang->line('config_locale'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#barcode_tab" title="<?php echo $this->lang->line('config_barcode_configuration'); ?>"><?php echo $this->lang->line('config_barcode'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#stock_tab" title="<?php echo $this->lang->line('config_location_configuration'); ?>"><?php echo $this->lang->line('config_location'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#receipt_tab" title="<?php echo $this->lang->line('config_receipt_configuration'); ?>"><?php echo $this->lang->line('config_receipt'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#invoice_tab" title="<?php echo $this->lang->line('config_invoice_configuration'); ?>"><?php echo $this->lang->line('config_invoice'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#email_tab" title="<?php echo $this->lang->line('config_email_configuration'); ?>"><?php echo $this->lang->line('config_email'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#reward_tab" title="<?php echo $this->lang->line('config_reward_configuration'); ?>"><?php echo $this->lang->line('config_reward'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#table_tab" title="<?php echo $this->lang->line('config_table_configuration'); ?>"><?php echo $this->lang->line('config_table'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#message_tab" title="<?php echo $this->lang->line('config_message_configuration'); ?>"><?php echo $this->lang->line('config_message'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#integrations_tab" title="<?php echo $this->lang->line('config_integrations_configuration'); ?>"><?php echo $this->lang->line('config_integrations'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#license_tab" title="<?php echo $this->lang->line('config_license_configuration'); ?>"><?php echo $this->lang->line('config_license'); ?></a>
	</li>
	<li role="presentation">
		<a data-toggle="tab" href="#system_info" title="<?php echo $this->lang->line('config_system_info'); ?>"><?php echo $this->lang->line('config_system_info'); ?></a>
	</li>
</ul>

<div class="tab-content">
	<div class="tab-pane fade in active" id="info_tab">
		<?php $this->load->view("configs/info_config"); ?>
	</div>
	<div class="tab-pane" id="general_tab">
		<?php $this->load->view("configs/general_config"); ?>
	</div>
	<div class="tab-pane" id="tax_tab">
		<?php $this->load->view("configs/tax_config"); ?>
	</div>
	<div class="tab-pane" id="locale_tab">
		<?php $this->load->view("configs/locale_config"); ?>
	</div>
	<div class="tab-pane" id="barcode_tab">
		<?php $this->load->view("configs/barcode_config"); ?>
	</div>
	<div class="tab-pane" id="stock_tab">
		<?php $this->load->view("configs/stock_config"); ?>
	</div>
	<div class="tab-pane" id="receipt_tab">
		<?php $this->load->view("configs/receipt_config"); ?>
	</div>
	<div class="tab-pane" id="invoice_tab">
		<?php $this->load->view("configs/invoice_config"); ?>
	</div>
	<div class="tab-pane" id="email_tab">
		<?php $this->load->view("configs/email_config"); ?>
	</div>
	<div class="tab-pane" id="reward_tab">
		<?php $this->load->view("configs/reward_config"); ?>
	</div>
	<div class="tab-pane" id="table_tab">
		<?php $this->load->view("configs/table_config"); ?>
	</div>
	<div class="tab-pane" id="message_tab">
		<?php $this->load->view("configs/message_config"); ?>
	</div>
	<div class="tab-pane" id="integrations_tab">
		<?php $this->load->view("configs/integrations_config"); ?>
	</div>
	<div class="tab-pane" id="license_tab">
		<?php $this->load->view("configs/license_config"); ?>
	</div>
	<div class="tab-pane" id="system_info">
		<?php $this->load->view("configs/system_info"); ?>
	</div>
</div>

<?php $this->load->view("partial/footer"); ?>
