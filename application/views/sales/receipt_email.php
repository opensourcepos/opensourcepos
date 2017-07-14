<div id="receipt_wrapper" style="width:100%;">
	<div id="receipt_header" style="text-align:center;">
		<?php
		if($this->config->item('company_logo') != '')
		{
		?>
			<div id="company_name">
				<img id="image" src="data:image/png;base64,<?php echo base64_encode(file_get_contents('uploads/' . $this->config->item('company_logo'))); ?>" alt="company_logo" />
			</div>
		<?php
		}
		?>

		<?php
		if($this->config->item('receipt_show_company_name'))
		{
		?>
			<div id="company_name" style="font-size:150%; font-weight:bold;"><?php echo $this->config->item('company'); ?></div>
		<?php
		}
		?>

		<div id="company_address"><?php echo nl2br($this->config->item('address')); ?></div>
		<div id="company_phone"><?php echo $this->config->item('phone'); ?></div>
		<br>
		<div id="sale_receipt"><?php echo $receipt_title; ?></div>
		<div id="sale_time"><?php echo $transaction_time ?></div>
	</div>

	<br>

	<div id="receipt_general_info" style="text-align:left;">
		<?php
		if(isset($customer))
		{
		?>
			<div id="customer"><?php echo $this->lang->line('customers_customer').": ".$customer; ?></div>
		<?php
		}
		?>

		<div id="sale_id"><?php echo $this->lang->line('sales_id').": ".$sale_id; ?></div>
		<div id="employee"><?php echo $this->lang->line('employees_employee').": ".$employee; ?></div>
	</div>

	<br>

	<table id="receipt_items" style="text-align:left;width:100%;">
		<tr>
			<th style="width:40%;"><?php echo $this->lang->line('sales_description_abbrv'); ?></th>
			<th style="width:20%;"><?php echo $this->lang->line('sales_price'); ?></th>
			<th style="width:20%;"><?php echo $this->lang->line('sales_quantity'); ?></th>
			<th style="width:20%;text-align:right;"><?php echo $this->lang->line('sales_total'); ?></th>
		</tr>
		<?php
		foreach($cart as $line=>$item)
		{
		?>
			<tr>
				<td><?php echo ucfirst($item['name']); ?></td>
				<td><?php echo to_currency($item['price']); ?></td>
				<td><?php echo to_quantity_decimals($item['quantity']); ?></td>
				<td style="text-align:right;"><?php echo to_currency($item[($this->config->item('receipt_show_total_discount') ? 'total' : 'discounted_total')]); ?></td>
			</tr>
			<tr>
				<?php
				if($this->config->item('receipt_show_description'))
				{
				?>
					<td colspan="2"><?php echo $item['description']; ?></td>
				<?php
				}
				?>
				<?php
				if($this->config->item('receipt_show_serialnumber'))
				{
				?>
					<td><?php echo $item['serialnumber']; ?></td>
				<?php
				}
				?>
			</tr>
			<?php
			if($item['discount'] > 0)
			{
			?>
				<tr>
					<td colspan="3" style="font-weight: bold;"><?php echo number_format($item['discount'], 0) . " " . $this->lang->line("sales_discount_included")?></td>
					<td style="text-align:right;"><?php echo to_currency($item['discounted_total']) ; ?></td>
				</tr>
			<?php
			}
			?>
		<?php
		}
		?>

		<?php
		if($this->config->item('receipt_show_total_discount') && $discount > 0)
		{
		?>
			<tr>
				<td colspan="3" style="text-align:right;border-top:2px solid #000000;"><?php echo $this->lang->line('sales_sub_total'); ?></td>
				<td style="text-align:right;border-top:2px solid #000000;"><?php echo to_currency($subtotal); ?></td>
			</tr>
			<tr>
				<td colspan="3" style="text-align:right;"><?php echo $this->lang->line('sales_discount'); ?>:</td>
				<td style="text-align:right;"><?php echo to_currency($discount*-1); ?></td>
			</tr>
		<?php
		}
		?>

		<?php
		if($this->config->item('receipt_show_taxes'))
		{
		?>
			<tr>
				<td colspan="3" style='text-align:right;border-top:2px solid #000000;'><?php echo $this->lang->line('sales_sub_total'); ?></td>
				<td style="text-align:right;border-top:2px solid #000000;"><?php echo to_currency($subtotal); ?></td>
			</tr>
			<?php
			foreach($taxes as $tax_group_index=>$sales_tax)
			{
			?>
				<tr>
					<td colspan="3" style="text-align:right;"><?php echo $sales_tax['tax_group']; ?>:</td>
					<td style="text-align:right;"><?php echo to_currency_tax($sales_tax['sale_tax_amount']); ?></td>
				</tr>
			<?php
			}
			?>
		<?php
		}
		?>

		<tr>
		</tr>

		<?php $border = (!$this->config->item('receipt_show_taxes') && !($this->config->item('receipt_show_total_discount') && $discount > 0)); ?>
		<tr>
			<td colspan="3" style="<?php echo $border? 'border-top: 2px solid black;' :''; ?>text-align:right;"><?php echo $this->lang->line('sales_total'); ?></td>
			<td style="<?php echo $border? 'border-top: 2px solid black;' :''; ?>text-align:right"><?php echo to_currency($total); ?></td>
		</tr>

		<tr>
			<td colspan="4">&nbsp;</td>
		</tr>

		<?php
		$only_sale_check = FALSE;
		$show_giftcard_remainder = FALSE;
		foreach($payments as $payment_id=>$payment)
		{
			$only_sale_check |= $payment['payment_type'] == $this->lang->line('sales_check');
			$splitpayment = explode(':', $payment['payment_type']);
			$show_giftcard_remainder |= $splitpayment[0] == $this->lang->line('sales_giftcard');
		?>
			<tr>
				<td colspan="3" style="text-align:right;"><?php echo $splitpayment[0]; ?> </td>
				<td style="text-align:right;"><?php echo to_currency( $payment['payment_amount'] * -1 ); ?></td>
			</tr>
		<?php
		}
		?>

		<tr>
			<td colspan="4">&nbsp;</td>
		</tr>

		<?php
		if(isset($cur_giftcard_value) && $show_giftcard_remainder)
		{
		?>
		<tr>
			<td colspan="3" style="text-align:right;"><?php echo $this->lang->line('sales_giftcard_balance'); ?></td>
			<td style="text-align:right"><?php echo to_currency($cur_giftcard_value); ?></td>
		</tr>
		<?php
		}
		?>
		<tr>
			<td colspan="3" style="text-align:right;"> <?php echo $this->lang->line($amount_change >= 0 ? ($only_sale_check ? 'sales_check_balance' : 'sales_change_due') : 'sales_amount_due') ; ?> </td>
			<td style="text-align:right"><?php echo to_currency($amount_change); ?></td>
		</tr>

		<tr>
			<td colspan="4">&nbsp;</td>
		</tr>
	</table>

	<div id="sale_return_policy" style="text-align:center">
		<?php echo nl2br($this->config->item('return_policy')); ?>
	</div>

	<br>

	<div id="barcode" style="text-align:center">
		<img src='data:image/png;base64,<?php echo $barcode; ?>' /><br>
		<?php echo $sale_id; ?>
	</div>
</div>
