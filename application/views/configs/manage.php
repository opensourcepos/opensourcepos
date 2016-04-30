<?php $this->load->view("partial/header"); ?>

<ul class="nav nav-tabs" data-tabs="tabs">
    <li class="active" role="presentation">
        <a data-toggle="tab" href="#info" title="<?php echo $this->lang->line('config_info_configuration'); ?>"><?php echo $this->lang->line('config_info'); ?></a>
    </li>
    <li role="presentation">
        <a data-toggle="tab" href="#general" title="<?php echo $this->lang->line('config_general_configuration'); ?>"><?php echo $this->lang->line('config_general'); ?></a>
    </li>
    <li role="presentation">
        <a data-toggle="tab" href="#locale" title="<?php echo $this->lang->line('config_locale_configuration'); ?>"><?php echo $this->lang->line('config_locale'); ?></a>
    </li>
    <li role="presentation">
        <a data-toggle="tab" href="#barcode" title="<?php echo $this->lang->line('config_barcode_configuration'); ?>"><?php echo $this->lang->line('config_barcode'); ?></a>
    </li>
    <li role="presentation">
        <a data-toggle="tab" href="#stock" title="<?php echo $this->lang->line('config_location_configuration'); ?>"><?php echo $this->lang->line('config_location'); ?></a>
    </li>
    <li role="presentation">
        <a data-toggle="tab" href="#receipt" title="<?php echo $this->lang->line('config_receipt_configuration'); ?>"><?php echo $this->lang->line('config_receipt'); ?></a>
    </li>
    <li role="presentation">
        <a data-toggle="tab" href="#invoice" title="<?php echo $this->lang->line('config_invoice_configuration'); ?>"><?php echo $this->lang->line('config_invoice'); ?></a>
    </li>
	<?php
	if($this->Employee->has_grant('messages', $this->session->userdata('person_id')))
	{
	?>
		<li role="presentation">
			<a data-toggle="tab" href="#message" title="<?php echo $this->lang->line('config_message_configuration'); ?>"><?php echo $this->lang->line('config_message'); ?></a>
		</li>
	<?php
	}
	?>
</ul>

<div class="tab-content">
    <div class="tab-pane fade in active" id="info">
        <?php $this->load->view("configs/info_config"); ?>
    </div>
    <div class="tab-pane" id="general">
        <?php $this->load->view("configs/general_config"); ?>
    </div>
    <div class="tab-pane" id="locale">
        <?php $this->load->view("configs/locale_config"); ?>
    </div>
    <div class="tab-pane" id="barcode">
        <?php $this->load->view("configs/barcode_config"); ?>
    </div>
    <div class="tab-pane" id="stock">
        <?php $this->load->view("configs/stock_config"); ?>
    </div>
    <div class="tab-pane" id="receipt">
        <?php $this->load->view("configs/receipt_config"); ?>
    </div>
    <div class="tab-pane" id="invoice">
        <?php $this->load->view("configs/invoice_config"); ?>
    </div>
	<?php
	if($this->Employee->has_grant('messages', $this->session->userdata('person_id')))
	{
	?>
		<div class="tab-pane" id="message">
			<?php $this->load->view("configs/message_config"); ?>
		</div>
	<?php
	}
	?>
</div>

<?php $this->load->view("partial/footer"); ?>
