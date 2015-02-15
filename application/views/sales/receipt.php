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
		<?php if ($this->Appconfig->get('company_logo') == '') 
        { 
        ?>
        <div id="company_name"><?php echo $this->config->item('company_logo'); ?></div>
		<?php 
		}
		else 
		{ 
		?>
		<div id="company_name"><img id="image" src="<?php echo base_url('uploads/' . $this->Appconfig->get('company_logo')); ?>" alt="company_logo" /></div>			
		<?php
		}
		?>
		<div id="company_address"><?php echo nl2br($this->config->item('address')); ?></div>
		<div id="company_phone"><?php echo $this->config->item('phone'); ?></div>
		<div id="sale_receipt"><?php echo $receipt_title; ?></div>
		<div id="sale_time"><?php echo $transaction_time ?></div>
	</div>
	<div id="receipt_general_info">
		<?php if(isset($customer))
		{
		?>
		<div id="customer"><?php echo $this->lang->line('customers_customer').": ".$customer; ?></div>
		<?php
		}
		?>
		<div id="sale_id"><?php echo $this->lang->line('sales_id').": ".$sale_id; ?></div>
		<?php if (!empty($invoice_number))
		{
		?>
		<div id="invoice_number"><?php echo $this->lang->line('recvs_invoice_number').": ".$invoice_number; ?></div>	
		<?php 
		}
		?>
		<div id="employee"><?php echo $this->lang->line('employees_employee').": ".$employee; ?></div>
	</div>

	<table id="receipt_items">
	<tr>
	<th style="width:40%;"><?php echo $this->lang->line('sales_item_name_short'); ?></th>
	<th style="width:20%;"><?php echo $this->lang->line('sales_taxed_price_short'); ?></th>
	<th style="width:20%;"><?php echo $this->lang->line('sales_quantity_short'); ?></th>
	<th style="width:20%;text-align:right;"><?php echo $this->lang->line('sales_total'); ?></th>
	</tr>
	<?php
	foreach(array_reverse($cart, true) as $line=>$item)
	{
	?>
		<tr>
			<td><span class='long_name'> <?php echo ucfirst(empty($item['description']) ? $item['name'] : $item['description']); ?></span></td>
		
		
			<td><?php echo to_currency($item['price']); ?></td>
			<td style='text-align:center;'><?php 
				echo $item['quantity'] . " " . ($show_stock_locations ? " [" . $item['stock_name'] . "]" : ""); 
			?></td>
			<td><div class="total-value"><?php echo to_currency($item['taxed_total']); ?></td>
		</tr>
	    <tr>
	    <td colspan="2" align="center"><?php echo $item['description']; ?></td>
		<td colspan="2" ><?php echo $item['serialnumber']; ?></td>
	    </tr>
	    <?php if ($item['discount'] > 0 ) : ?>
		<tr>
			<td colspan="2" style="font-weight: bold;"> <?php echo $item['discount'] . " " . $this->lang->line("sales_discount_included")?> </td>
		</tr>
		<?php endif; ?>

	<?php
	}
	?>
	<!-- only show if setting enabled in conf!g -->
	<tr>
	<td colspan="2" style='text-align:right;border-top:2px solid #000000;'><?php echo $this->lang->line('sales_sub_total'); ?></td>
	<td colspan="2" style='text-align:right;border-top:2px solid #000000;'><?php echo to_currency($subtotal); ?></td>
	</tr>

	<?php foreach($taxes as $name=>$value) { ?>
		<tr>
			<td colspan="2" style='text-align:right;'><?php echo $name; ?>:</td>
			<td colspan="2" style='text-align:right;'><?php echo to_currency($value); ?></td>
		</tr>
	<?php }; ?>
<!-- END CONDITION -->
	<tr>
	<td colspan="2" style='text-align:right;'><?php echo $this->lang->line('sales_total'); ?></td>
	<td colspan="2" style='text-align:right'><?php echo to_currency($total); ?></td>
	</tr>

    <tr><td colspan="4">&nbsp;</td></tr>

	<?php
	$only_sale_check = TRUE;
	
	foreach($payments as $payment_id=>$payment)
	{ 
		$only_sale_check &= $payment[ SALE_PAYMENT_PAYMENT_TYPE ] == $this->lang->line('sales_check');
  		?>
		<tr>
		<td colspan="2" style="text-align:right;"><?php $splitpayment=explode(':',$payment['payment_type']); echo $splitpayment[0]; ?> </td>
		<td colspan="2" style="text-align:right"><div class="total-value"><?php echo to_currency( $payment['payment_amount'] * -1 ); ?></div></td>
	    </tr>
	<?php
	}
	?>

    <tr><td colspan="4">&nbsp;</td></tr>

    <?php 
	    if (isset($cur_giftcard_value))
	    {
	    ?>
	    <tr>
			<td colspan="2" style='text-align:right;'><?php echo $this->lang->line('sales_giftcard_balance'); ?></td>
	    	<td colspan="2" style='text-align:right'><?php echo to_currency($cur_giftcard_value); ?></td>
	    </tr>
	    <?php 
	    }
    ?>
	<tr>
		<td colspan="2" style='text-align:right;'> <?php echo $this->lang->line($amount_change >= 0 ? ($only_sale_check ? 'sales_check_due' : 'sales_change_due') : 'sales_invoice_amount_due') ; ?> </td>
		<td colspan="2" style='text-align:right'><?php echo $amount_change; ?></td>
	</tr>

	</table>

	<div id="sale_return_policy">
	<?php echo nl2br($this->config->item('return_policy')); ?>
	</div>
	<div id='barcode'>
	<img src='data:image/png;base64,<?php echo $barcode; ?>' /><br>
	<?php echo $sale_id; ?>
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
