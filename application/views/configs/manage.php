<?php $this->load->view("partial/header"); ?>

<div id="title_bar">
    <div id="title" class="float_left"><?php echo $this->lang->line('module_config'); ?></div>
</div>

<ul class="nav nav-tabs">
    <li class="active" role="presentation">
        <a href="#">General</a>
    </li>
    <li role="presentation">
        <a href="#">Locale</a>
    </li>
    <li role="presentation">
        <a href="#">Barcode</a>
    </li>
    <li role="presentation">
        <a href="#">Stock</a>
    </li>
    <li role="presentation">
        <a href="#">Receipt</a>
    </li>
</ul>

<div id="tab_contents">
    <div>
        <?php $this->load->view("configs/general_config"); ?>
    </div>
    <div>
        <?php $this->load->view("configs/locale_config"); ?>
    </div>
    <div>
        <?php $this->load->view("configs/barcode_config"); ?>
    </div>
    <div>
        <?php $this->load->view("configs/stock_config"); ?>
    </div>
    <div>
        <?php $this->load->view("configs/receipt_config"); ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $(".nav-tabs li a").click(function () {
            var $parent = $(this).parents("li");
            $parent.addClass("active").siblings().removeClass("active");
            $("#tab_contents > div").hide().filter("div:eq(" + $parent.index() + ")").show();
            return false;
        });
    });
</script>

<?php $this->load->view("partial/footer"); ?>