<?php
/**
 * @var string $transaction_time
 * @var int $sale_id
 * @var string $employee
 * @var array $cart
 * @var float $discount
 * @var float $subtotal
 * @var array $taxes
 * @var float $total
 * @var array $payments
 * @var float $amount_change
 * @var string $barcode
 */
?>
<div id="receipt_wrapper" style="width:100%;">
	<div id="receipt_header" style="text-align:center;">
		<?php
		if(config('OSPOS')->company_logo != '')
		{
		?>
			<div id="company_name">
				<img id="image" src="data:image/png;base64,<?php echo base64_encode(file_get_contents('uploads/' . config('OSPOS')->company_logo)) ?>" alt="company_logo" />
			</div>
		<?php
		}
		?>

		<?php
		if(config('OSPOS')->receipt_show_company_name)
		{
		?>
			<div id="company_name" style="font-size:150%; font-weight:bold;"><?php echo esc(config('OSPOS')->company) ?></div>
		<?php
		}
		?>

		<div id="company_address"><?php echo nl2br(esc(config('OSPOS')->address)) ?></div>
		<div id="company_phone"><?php echo esc(config('OSPOS')->phone) ?></div>
		<br>
		<div id="sale_receipt"><?php echo lang('Sales.receipt') ?></div>
		<div id="sale_time"><?php echo esc($transaction_time) ?></div>
	</div>

	<br>

	<div id="receipt_general_info" style="text-align:left;">
		<?php
		if(isset($customer))
		{
		?>
			<div id="customer"><?php echo lang('Customers.customer') . esc(": $customer") ?></div>
		<?php
		}
		?>

		<div id="sale_id"><?php echo lang('Sales.id') . esc(": $sale_id") ?></div>
		<div id="employee"><?php echo lang('Employees.employee') . esc(": $employee") ?></div>
	</div>

	<br>

	<table id="receipt_items" style="text-align:left;width:100%;">
		<tr>
			<th style="width:40%;"><?php echo lang('Sales.description_abbrv') ?></th>
			<th style="width:20%;"><?php echo lang('Sales.price') ?></th>
			<th style="width:20%;"><?php echo lang('Sales.quantity') ?></th>
			<th style="width:20%;text-align:right;"><?php echo lang('Sales.total') ?></th>
		</tr>
		<?php
		foreach($cart as $line => $item)
		{
			if($item['print_option'] == PRINT_YES)
			{
			?>
				<tr>
					<td><?php echo esc(ucfirst($item['name'] . ' ' . $item['attribute_values'])) ?></td>
					<td><?php echo to_currency($item['price']) ?></td>
					<td><?php echo to_quantity_decimals($item['quantity']) ?></td>
					<td style="text-align:right;"><?php echo to_currency($item[(config('OSPOS')->receipt_show_total_discount ? 'total' : 'discounted_total')]) ?></td>
				</tr>
				<tr>
					<?php
					if(config('OSPOS')->receipt_show_description)
					{
					?>
						<td colspan="2"><?php echo esc($item['description']) ?></td>
					<?php
					}

					if(config('OSPOS')->receipt_show_serialnumber)
					{
					?>
						<td><?php echo esc($item['serialnumber']) ?></td>
					<?php
					}
					?>
				</tr>
				<?php
				if($item['discount'] > 0)
				{
				?>
					<tr>
						<?php
						if($item['discount_type'] == FIXED)
						{
						?>
							<td colspan="3" class="discount"><?php echo to_currency($item['discount']) . " " . lang('Sales.discount') ?></td>
						<?php
						}
						elseif($item['discount_type'] == PERCENT)
						{
						?>
							<td colspan="3" class="discount"><?php echo to_decimals($item['discount']) . " " . lang('Sales.discount_included') ?></td>
						<?php
						}	
						?>
						<td class="total-value"><?php echo to_currency($item['discounted_total']) ?></td>
					</tr>
				<?php
				}
			}
		}

		if(config('OSPOS')->receipt_show_total_discount && $discount > 0)
		{
		?>
			<tr>
				<td colspan="3" style="text-align:right;border-top:2px solid #000000;"><?php echo lang('Sales.sub_total') ?></td>
				<td style="text-align:right;border-top:2px solid #000000;"><?php echo to_currency($subtotal) ?></td>
			</tr>
			<tr>
				<td colspan="3" style="text-align:right;"><?php echo lang('Sales.discount') ?>:</td>
				<td style="text-align:right;"><?php echo to_currency($discount*-1) ?></td>
			</tr>
		<?php
		}
		?>

		<?php
		if(config('OSPOS')->receipt_show_taxes)
		{
		?>
			<tr>
				<td colspan="3" style='text-align:right;border-top:2px solid #000000;'><?php echo lang('Sales.sub_total') ?></td>
				<td style="text-align:right;border-top:2px solid #000000;"><?php echo to_currency($subtotal) ?></td>
			</tr>
			<?php
			foreach($taxes as $tax_group_index => $tax)
			{
			?>
				<tr>
					<td colspan="3" style="text-align:right;"><?php echo (float)$tax['tax_rate'] . '% ' . $tax['tax_group'] ?>:</td>
					<td style="text-align:right;"><?php echo to_currency_tax($tax['sale_tax_amount']) ?></td>
				</tr>
			<?php
			}
			?>
		<?php
		}
		?>

		<tr>
		</tr>

		<?php $border = (!config('OSPOS')->receipt_show_taxes && !(config('OSPOS')->receipt_show_total_discount && $discount > 0)) ?>
		<tr>
			<td colspan="3" style="<?php echo $border ? 'border-top: 2px solid black;' : '' ?>text-align:right;"><?php echo lang('Sales.total') ?></td>
			<td style="<?php echo $border ? 'border-top: 2px solid black;' : '' ?>text-align:right"><?php echo to_currency($total) ?></td>
		</tr>

		<tr>
			<td colspan="4">&nbsp;</td>
		</tr>

		<?php
		$only_sale_check = FALSE;
		$show_giftcard_remainder = FALSE;
		foreach($payments as $payment_id => $payment)
		{
			$only_sale_check |= $payment['payment_type'] == lang('Sales.check');
			$splitpayment = explode(':', $payment['payment_type']);
			$show_giftcard_remainder |= $splitpayment[0] == lang('Sales.giftcard');
		?>
			<tr>
				<td colspan="3" style="text-align:right;"><?php echo $splitpayment[0] ?> </td>
				<td style="text-align:right;"><?php echo to_currency( $payment['payment_amount'] * -1 ) ?></td>
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
			<td colspan="3" style="text-align:right;"><?php echo lang('Sales.giftcard_balance') ?></td>
			<td style="text-align:right"><?php echo to_currency($cur_giftcard_value) ?></td>
		</tr>
		<?php
		}
		?>
		<tr>
			<td colspan="3" style="text-align:right;"> <?php echo lang($amount_change >= 0 ? ($only_sale_check ? 'Sales.check_balance' : 'Sales.change_due') : 'Sales.amount_due') ?> </td>
			<td style="text-align:right"><?php echo to_currency($amount_change) ?></td>
		</tr>

		<tr>
			<td colspan="4">&nbsp;</td>
		</tr>
	</table>

	<div id="sale_return_policy" style="text-align:center">
		<?php echo nl2br(esc(config('OSPOS')->return_policy)) ?>
	</div>

	<br>

	<div id="barcode" style="text-align:center">
		<img alt='<?php echo esc($barcode, 'attr') ?>' src='data:image/png;base64,<?php echo esc($barcode, 'attr') ?>' /><br>
		<?php echo $sale_id ?>
	</div>
</div>
