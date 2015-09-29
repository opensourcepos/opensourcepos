<?php $this->load->view("partial/header"); ?>
<link rel="stylesheet" rev="stylesheet" href="<?php echo base_url();?>css/tabcontent.css" />
<script src="<?php echo base_url();?>js/tabcontent.js" type="text/javascript" language="javascript" charset="UTF-8"></script>

<div id="title_bar">
    <div id="title" class="float_left"><?php echo $this->lang->line('module_config'); ?></div>
</div>
<ul class="tabs" data-persist="true">
    <li><a href="#general_config">General</a></li>
    <li><a href="#locale_config">Locale</a></li>
    <li><a href="#barcode_config">Barcode</a></li>
    <li><a href="#stock_config">Stock</a></li>
    <li><a href="#receipt_config">Receipt</a></li>
</ul>

<div class="tabcontents">
    <div id="general_config">
        <?php $this->load->view("configs/general_config"); ?>
    </div>
    <div id="locale_config">
        <?php $this->load->view("configs/locale_config"); ?>
    </div>
    <div id="barcode_config">
        <?php $this->load->view("configs/barcode_config"); ?>
    </div>
    <div id="stock_config">
        <?php $this->load->view("configs/stock_config"); ?>
    </div>
    <div id="receipt_config">
        <?php $this->load->view("configs/receipt_config"); ?>
    </div>
</div>
<div id="feedback_bar"></div>
<?php $this->load->view("partial/footer"); ?>