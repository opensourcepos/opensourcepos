<?php $this->load->view("partial/header"); ?>

<?php
$info_tab = 'id="info-tab" data-bs-toggle="tab" href="#info" role="tab" aria-controls="info" title="' . $this->lang->line('config_info_configuration') . '"';
$info_title = '<i class="bi bi-shop"></i>&nbsp;&nbsp;' . $this->lang->line('config_info');

$general_tab = 'id="general-tab" data-bs-toggle="tab" href="#general" role="tab" aria-controls="general" title="' . $this->lang->line('config_general_configuration') . '"';
$general_title = '<i class="bi bi-sliders"></i>&nbsp;&nbsp;' . $this->lang->line('config_general');

$appearance_tab = 'id="appearance-tab" data-bs-toggle="tab" href="#appearance" role="tab" aria-controls="appearance" title="Appearance Configuration"';
$appearance_title = '<i class="bi bi-eye"></i>&nbsp;&nbsp;Appearance';

$locale_tab = 'id="locale-tab" data-bs-toggle="tab" href="#locale" role="tab" aria-controls="locale" title="' . $this->lang->line('config_locale_configuration') . '"';
$locale_title = '<i class="bi bi-translate"></i>&nbsp;&nbsp;' . $this->lang->line('config_locale');

$tax_tab = 'id="tax-tab" data-bs-toggle="tab" href="#tax" role="tab" aria-controls="tax" title="' . $this->lang->line('config_tax_configuration') . '"';
$tax_title = '<i class="bi bi-piggy-bank"></i>&nbsp;&nbsp;' . $this->lang->line('config_tax');

$barcode_tab = 'id="barcode-tab" data-bs-toggle="tab" href="#barcode" role="tab" aria-controls="barcode" title="' . $this->lang->line('config_barcode_configuration') . '"';
$barcode_title = '<i class="bi bi-upc-scan"></i>&nbsp;&nbsp;' . $this->lang->line('config_barcode');

$stock_tab = 'id="stock-tab" data-bs-toggle="tab" href="#stock" role="tab" aria-controls="stock" title="' . $this->lang->line('config_location_configuration') . '"';
$stock_title = '<i class="bi bi-truck"></i>&nbsp;&nbsp;' . $this->lang->line('config_location');

$receipt_tab = 'id="receipt-tab" data-bs-toggle="tab" href="#receipt" role="tab" aria-controls="receipt" title="' . $this->lang->line('config_receipt_configuration') . '"';
$receipt_title = '<i class="bi bi-receipt"></i>&nbsp;&nbsp;' . $this->lang->line('config_receipt');

$invoice_tab = 'id="invoice-tab" data-bs-toggle="tab" href="#invoice" role="tab" aria-controls="invoice" title="' . $this->lang->line('config_invoice_configuration') . '"';
$invoice_title = '<i class="bi bi-file-text"></i>&nbsp;&nbsp;' . $this->lang->line('config_invoice');

$reward_tab = 'id="reward-tab" data-bs-toggle="tab" href="#reward" role="tab" aria-controls="reward" title="' . $this->lang->line('config_reward_configuration') . '"';
$reward_title = '<i class="bi bi-trophy"></i>&nbsp;&nbsp;' . $this->lang->line('config_reward');

$table_tab = 'id="table-tab" data-bs-toggle="tab" href="#table" role="tab" aria-controls="table" title="' . $this->lang->line('config_table_configuration') . '"';
$table_title = '<i class="bi bi-cup-straw"></i>&nbsp;&nbsp;' . $this->lang->line('config_table');

$email_tab = 'id="email-tab" data-bs-toggle="tab" href="#email" role="tab" aria-controls="email" title="' . $this->lang->line('config_email_configuration') . '"';
$email_title = '<i class="bi bi-envelope"></i>&nbsp;&nbsp;' . $this->lang->line('config_email');

$message_tab = 'id="message-tab" data-bs-toggle="tab" href="#message" role="tab" aria-controls="message" title="' . $this->lang->line('config_message_configuration') . '"';
$message_title = '<i class="bi bi-chat"></i>&nbsp;&nbsp;' . $this->lang->line('config_message');

$integrations_tab = 'id="integrations-tab" data-bs-toggle="tab" href="#integrations" role="tab" aria-controls="integrations" title="' . $this->lang->line('config_integrations_configuration') . '"';
$integrations_title = '<i class="bi bi-gear-wide-connected"></i>&nbsp;&nbsp;' . $this->lang->line('config_integrations');

$system_tab = 'id="system-tab" data-bs-toggle="tab" href="#system" role="tab" aria-controls="system" title="' . $this->lang->line('config_system_info') . '"';
$system_title = '<i class="bi bi-info-circle"></i>&nbsp;&nbsp;' . $this->lang->line('config_system_info');

$license_tab = 'id="license-tab" data-bs-toggle="tab" href="#license" role="tab" aria-controls="license" title="' . $this->lang->line('config_license_configuration') . '"';
$license_title = '<i class="bi bi-journal-check"></i>&nbsp;&nbsp;' . $this->lang->line('config_license');
?>

<div class="row">
	<div class="col-lg order-lg-2">
		<div class="list-group d-none d-lg-block sticky-top" id="configs-list-tab" role="tablist">
			<button class="list-group-item list-group-item-action active" <?= $info_tab; ?>><?= $info_title; ?></button>
			<button class="list-group-item list-group-item-action" <?= $general_tab; ?>><?= $general_title; ?></button>
			<button class="list-group-item list-group-item-action" <?= $appearance_tab; ?>><?= $appearance_title; ?></button>
			<button class="list-group-item list-group-item-action" <?= $locale_tab; ?>><?= $locale_title; ?></button>
			<button class="list-group-item list-group-item-action" <?= $tax_tab; ?>><?= $tax_title; ?></button>
			<button class="list-group-item list-group-item-action" <?= $barcode_tab; ?>><?= $barcode_title; ?></button>
			<button class="list-group-item list-group-item-action" <?= $stock_tab; ?>><?= $stock_title; ?></button>
			<button class="list-group-item list-group-item-action" <?= $receipt_tab; ?>><?= $receipt_title; ?></button>
			<button class="list-group-item list-group-item-action" <?= $invoice_tab; ?>><?= $invoice_title; ?></button>
			<button class="list-group-item list-group-item-action" <?= $reward_tab; ?>><?= $reward_title; ?></button>
			<button class="list-group-item list-group-item-action" <?= $table_tab; ?>><?= $table_title; ?></button>
			<button class="list-group-item list-group-item-action" <?= $email_tab; ?>><?= $email_title; ?></button>
			<button class="list-group-item list-group-item-action" <?= $message_tab; ?>><?= $message_title; ?></button>
			<button class="list-group-item list-group-item-action" <?= $integrations_tab; ?>><?= $integrations_title; ?></button>
			<button class="list-group-item list-group-item-action" <?= $system_tab; ?>><?= $system_title; ?></button>
			<button class="list-group-item list-group-item-action" <?= $license_tab; ?>><?= $license_title; ?></button>
		</div>
		<div class="nav dropdown d-lg-none mb-3">
			<button class="btn btn-primary w-100 dropdown-toggle" id="configs-dropdown" data-bs-toggle="dropdown" aria-expanded="false">Select Configuration...</button>
			<ul class="dropdown-menu w-100" aria-labbeledby="configs-dropdown">
				<li><a class="dropdown-item" <?= $info_tab; ?>><?= $info_title; ?></a></li>
				<li><a class="dropdown-item" <?= $general_tab; ?>><?= $general_title; ?></a></li>
				<li><a class="dropdown-item" <?= $appearance_tab; ?>><?= $appearance_title; ?></a></li>
				<li><a class="dropdown-item" <?= $locale_tab; ?>><?= $locale_title; ?></a></li>
				<li><a class="dropdown-item" <?= $tax_tab; ?>><?= $tax_title; ?></a></li>
				<li><a class="dropdown-item" <?= $barcode_tab; ?>><?= $barcode_title; ?></a></li>
				<li><a class="dropdown-item" <?= $stock_tab; ?>><?= $stock_title; ?></a></li>
				<li><a class="dropdown-item" <?= $receipt_tab; ?>><?= $receipt_title; ?></a></li>
				<li><a class="dropdown-item" <?= $invoice_tab; ?>><?= $invoice_title; ?></a></li>
				<li><a class="dropdown-item" <?= $reward_tab; ?>><?= $reward_title; ?></a></li>
				<li><a class="dropdown-item" <?= $table_tab; ?>><?= $table_title; ?></a></li>
				<li><a class="dropdown-item" <?= $email_tab; ?>><?= $email_title; ?></a></li>
				<li><a class="dropdown-item" <?= $message_tab; ?>><?= $message_title; ?></a></li>
				<li><a class="dropdown-item" <?= $integrations_tab; ?>><?= $integrations_title; ?></a></li>
				<li><a class="dropdown-item" <?= $system_tab; ?>><?= $system_title; ?></a></li>
				<li><a class="dropdown-item" <?= $license_tab; ?>><?= $license_title; ?></a></li>
			</ul>
		</div>
	</div>

	<div class="col-lg-9 order-lg-1">
		<div class="tab-content" id="configs-content">
			<div class="tab-pane active" id="info" role="tabpanel" aria-labelledby="info-tab"><?php $this->load->view("configs/info_config"); ?></div>
			<div class="tab-pane" id="general" role="tabpanel" aria-labelledby="general-tab"><?php $this->load->view("configs/general_config"); ?></div>
			<div class="tab-pane" id="appearance" role="tabpanel" aria-labelledby="appearance-tab"><?php $this->load->view("configs/appearance_config"); ?></div>
			<div class="tab-pane" id="locale" role="tabpanel" aria-labelledby="locale-tab"><?php $this->load->view("configs/locale_config"); ?></div>
			<div class="tab-pane" id="tax" role="tabpanel" aria-labelledby="tax-tab"><?php $this->load->view("configs/tax_config"); ?></div>
			<div class="tab-pane" id="barcode" role="tabpanel" aria-labelledby="barcode-tab"><?php $this->load->view("configs/barcode_config"); ?></div>
			<div class="tab-pane" id="stock" role="tabpanel" aria-labelledby="stock-tab"><?php $this->load->view("configs/stock_config"); ?></div>
			<div class="tab-pane" id="receipt" role="tabpanel" aria-labelledby="receipt-tab"><?php $this->load->view("configs/receipt_config"); ?></div>
			<div class="tab-pane" id="invoice" role="tabpanel" aria-labelledby="invoice-tab"><?php $this->load->view("configs/invoice_config"); ?></div>
			<div class="tab-pane" id="reward" role="tabpanel" aria-labelledby="reward-tab"><?php $this->load->view("configs/reward_config"); ?></div>
			<div class="tab-pane" id="table" role="tabpanel" aria-labelledby="table-tab"><?php $this->load->view("configs/table_config"); ?></div>
			<div class="tab-pane" id="email" role="tabpanel" aria-labelledby="email-tab"><?php $this->load->view("configs/email_config"); ?></div>
			<div class="tab-pane" id="message" role="tabpanel" aria-labelledby="message-tab"><?php $this->load->view("configs/message_config"); ?></div>
			<div class="tab-pane" id="integrations" role="tabpanel" aria-labelledby="integrations-tab"><?php $this->load->view("configs/integrations_config"); ?></div>
			<div class="tab-pane" id="system" role="tabpanel" aria-labelledby="system-tab"><?php $this->load->view("configs/system_info"); ?></div>
			<div class="tab-pane" id="license" role="tabpanel" aria-labelledby="license-tab"><?php $this->load->view("configs/license_config"); ?></div>
		</div>
	</div>
</div>

<?php $this->load->view("partial/footer"); ?>