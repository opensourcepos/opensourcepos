<?php $this->load->view("partial/header"); ?>

<ul class="nav nav-tabs" data-tabs="tabs">
    <li class="active" role="presentation">
        <a data-toggle="tab" href="#general" title="<?php echo $this->lang->line('config_general_configuration'); ?>">General</a>
    </li>
    <li role="presentation">
        <a data-toggle="tab" href="#locale" title="<?php echo $this->lang->line('config_locale_configuration'); ?>">Locale</a>
    </li>
    <li role="presentation">
        <a data-toggle="tab" href="#barcode" title="<?php echo $this->lang->line('config_barcode_configuration'); ?>">Barcode</a>
    </li>
    <li role="presentation">
        <a data-toggle="tab" href="#stock" title="<?php echo $this->lang->line('config_location_configuration'); ?>">Stock</a>
    </li>
    <li role="presentation">
        <a data-toggle="tab" href="#receipt" title="<?php echo $this->lang->line('config_receipt_configuration'); ?>">Receipt</a>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade in active" id="general">
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
</div>

<?php $this->load->view("partial/footer"); ?>