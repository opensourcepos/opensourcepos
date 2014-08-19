<?php $this->load->view("partial/header"); ?>
<?php
if (isset($error_message))
{
    echo '<h1 style="text-align: center;">'.$error_message.'</h1>';
    exit;
}
?>
<div id="receipt_wrapper">
    <div id="receipt_header">
        <div id="company_name"><?php echo $this->config->item('company'); ?></div>
        <div id="company_address"><?php echo nl2br($this->config->item('address')); ?></div>
        <div id="company_phone"><?php echo $this->config->item('phone'); ?></div>
        <div id="sale_receipt"><?php echo $receipt_title; ?></div>
        <div id="sale_time"><?php echo $transaction_time ?></div>
    </div>
    <div id="receipt_general_info">
        <div id="sale_id"><?php echo $this->lang->line('recvs_id').": ".$receiving_id; ?></div>
        <div id="employee"><?php echo $this->lang->line('employees_employee').": ".$employee; ?></div>
    </div>

    <table id="receipt_items">
    <tr>
    <th style="width:25%;"><?php echo $this->lang->line('items_item'); ?></th>
    <th style="width:16%;text-align:center;"><?php echo $this->lang->line('reqs_quantity'); ?></th>
    <th style="width:25%;text-align:center;"><?php echo $this->lang->line('reqs_related_item'); ?></th>
    <th style="width:17%;text-align:center;"><?php echo $this->lang->line('reqs_unit_quantity'); ?></th>
    <th style="width:17%;text-align:center;"><?php echo $this->lang->line('reqs_related_item_quantity'); ?></th>
    </tr>
    <?php

    foreach(array_reverse($cart, true) as $line=>$item)
    {
    ?>
        <tr>
        <td><span class='long_name'><?php echo $item['name']; ?></span><span class='short_name'><?php echo character_limiter($item['name'],10); ?></span></td>
        <td style='text-align:center;'><?php echo $item['quantity']; ?></td>
        <td style='text-align:center;'><?php echo $item['related_item']; ?></td>
        <td style='text-align:center;'><?php echo $item['unit_quantity']; ?></td>
        <?php
            $related_item_id = $this->Item->get_item_id($this->Item_unit->get_info($item['item_id'])->related_number,'sale_stock');
            $total_related_item_qty = $this->Item->get_info($related_item_id)->quantity;
        ?>
        <td style='text-align:center;'><?php echo $total_related_item_qty; ?></td>
        </tr>
        <tr>

        <td colspan="2" align="center"><?php echo $item['description']; ?></td>
        <td colspan="2" ><?php echo $item['serialnumber']; ?></td>
        </tr>
    <?php
    }
    ?>  

    </table>

    <div id="sale_return_policy">
    <?php echo nl2br($this->config->item('return_policy')); ?>
    </div>
    <div id='barcode'>
    <?php echo "<img src='index.php?c=barcode&barcode=$receiving_id&text=$receiving_id&width=250&height=50' />"; ?>
    </div>
</div>
<?php $this->load->view("partial/footer"); ?>

<?php if ($this->Appconfig->get('print_after_sale'))
{
?>
<script type="text/javascript">
$(window).load(function()
{
    window.print();
});
</script>
<?php
}
?>