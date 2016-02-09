<?php $this->load->view("partial/header"); ?>

<div id="title_bar">
    <div id="title" class="float_left"><?php echo $this->lang->line('module_config'); ?></div>
</div>

<ul class="nav nav-tabs" data-tabs="tabs">
    <li class="active" role="presentation">
        <a data-toggle="tab" href="#general">General</a>
    </li>
    <li role="presentation">
        <a data-toggle="tab" href="#locale">Locale</a>
    </li>
    <li role="presentation">
        <a data-toggle="tab" href="#barcode">Barcode</a>
    </li>
    <li role="presentation">
        <a data-toggle="tab" href="#stock">Stock</a>
    </li>
    <li role="presentation">
        <a data-toggle="tab" href="#receipt">Receipt</a>
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