<?php
/**
 * @var string $transaction_time
 * @var int $sale_id
 * @var string $employee
 * @var float $discount
 * @var array $cart
 * @var float $subtotal
 * @var array $taxes
 * @var float $total
 * @var array $payments
 * @var float $amount_change
 * @var string $barcode
 */
?>
<div id="receipt_wrapper" style="font-size:<?php echo esc(config('OSPOS')->receipt_font_size) ?>px">
	<div id="receipt_header">
		<?php
		if(config('OSPOS')->company_logo != '')
		{
		?>
			<div id="company_name">
				<img id="image" src="<?php echo base_url('uploads/' . esc(config('OSPOS')->company_logo, 'url')) ?>" alt="company_logo" />
			</div>
		<?php
		}
		?>

		<?php
		if(config('OSPOS')->receipt_show_company_name)
		{
		?>
			<div id="company_name"><?php echo esc(config('OSPOS')->company) ?></div>
		<?php
		}
		?>

		<div id="company_address"><?php echo nl2br(esc(config('OSPOS')->address)) ?></div>
		<div id="company_phone"><?php echo esc(config('OSPOS')->phone) ?></div>
		<div id="sale_receipt"><?php echo lang('Sales.receipt') ?></div>
		<div id="sale_time"><?php echo esc($transaction_time) ?></div>
	</div>

	<div id="receipt_general_info">
		<?php
		if(isset($customer))
		{
		?>
			<div id="customer"><?php echo lang('Customers.customer') . esc(": $customer") ?></div>
		<?php
		}
		?>

		<div id="sale_id"><?php echo lang('Sales.id') . esc(": $sale_id") ?></div>

		<?php
		if(!empty($invoice_number))
		{
		?>
			<div id="invoice_number"><?php echo lang('Sales.invoice_number') . ": $invoice_number" ?></div>
		<?php
		}
		?>

		<div id="employee"><?php echo lang('Employees.employee') . esc(": $employee") ?></div>
	</div>

	<table id="receipt_items">
		<tr>
			<th style="width:50%;"><?php echo lang('Sales.description_abbrv') ?></th>
			<th style="width:25%;"><?php echo lang('Sales.quantity') ?></th>
			<th colspan="4" style="width:25%;" class="total-value"><?php echo lang('Sales.total') ?></th>
		</tr>
		<?php
		foreach($cart as $line => $item)
		{
		?>
			<tr>
				<td><?php echo esc(ucfirst($item['name'] . ' ' . $item['attribute_values'])) ?></td>
				<td><?php echo to_quantity_decimals($item['quantity']) ?></td>
				<td class="total-value"><?php echo to_currency($item[(config('OSPOS')->receipt_show_total_discount ? 'total' : 'discounted_total')]) ?></td>
			</tr>
			<tr>
				<?php
				if(config('OSPOS')->receipt_show_description)
				{
				?>
					<td colspan="2"><?php echo esc($item['description']) ?></td>
				<?php
				}
				?>
				<?php
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
						<td colspan="2" class="discount"><?php echo to_currency($item['discount']) . " " . lang('Sales.discount') ?></td>
					<?php
					}
					elseif($item['discount_type'] == PERCENT)
					{
					?>
						<td colspan="2" class="discount"><?php echo to_decimals($item['discount']) . " " . lang('Sales.discount_included') ?></td>
					<?php
					}	
					?>
					<td class="total-value"><?php echo to_currency($item['discounted_total']) ?></td>
				</tr>
			<?php
			}
			?>

		<?php
		}
		?>

		<?php
		if(config('OSPOS')->receipt_show_total_discount && $discount > 0)
		{
		?>
			<tr>
				<td colspan="2" style='text-align:right;border-top:2px solid #000000;'><?php echo lang('Sales.sub_total') ?></td>
				<td style='text-align:right;border-top:2px solid #000000;'><?php echo to_currency($subtotal) ?></td>
			</tr>
			<tr>
				<td colspan="2" class="total-value"><?php echo lang('Sales.discount') ?>:</td>
				<td class="total-value"><?php echo to_currency($discount * -1) ?></td>
			</tr>
		<?php
		}
		?>

		<?php
		if(config('OSPOS')->receipt_show_taxes)
		{
		?>
			<tr>
				<td colspan="2" style='text-align:right;border-top:2px solid #000000;'><?php echo lang('Sales.sub_total') ?></td>
				<td style='text-align:right;border-top:2px solid #000000;'><?php echo to_currency($subtotal) ?></td>
			</tr>
			<?php
			foreach($taxes as $tax_group_index => $tax)
			{
			?>
				<tr>
					<td colspan="2" class="total-value"><?php echo (float)$tax['tax_rate'] . '% ' . $tax['tax_group'] ?>:</td>
					<td class="total-value"><?php echo to_currency_tax($tax['sale_tax_amount']) ?></td>
				</tr>
			<?php
			}
			?>
		<?php
		}
		?>

		<tr>
		</tr>

		<?php $border = (!config('OSPOS')->receipt_show_taxes && !(config('OSPOS')->receipt_show_total_discount && $discount > 0)); ?>
		<tr>
			<td colspan="2" style="text-align:right;<?php echo $border ? 'border-top: 2px solid black;' : '' ?>"><?php echo lang('Sales.total') ?></td>
			<td style="text-align:right;<?php echo $border ? 'border-top: 2px solid black;' : '' ?>"><?php echo to_currency($total) ?></td>
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
				<td colspan="2" style="text-align:right;"><?php echo $splitpayment[0] ?> </td>
				<td class="total-value"><?php echo to_currency( $payment['payment_amount'] * -1 ) ?></td>
			</tr>
		<?php
		}
		?>

		<?php
		if(isset($cur_giftcard_value) && $show_giftcard_remainder)
		{
		?>
		<tr>
			<td colspan="2" style="text-align:right;"><?php echo lang('Sales.giftcard_balance') ?></td>
			<td class="total-value"><?php echo to_currency($cur_giftcard_value) ?></td>
		</tr>
		<?php
		}
		?>
		<tr>
			<td colspan="2" style="text-align:right;"> <?php echo lang($amount_change >= 0 ? ($only_sale_check ? 'Sales.check_balance' : 'Sales.change_due') : 'Sales.amount_due') ?> </td>
			<td class="total-value"><?php echo to_currency($amount_change) ?></td>
		</tr>
	</table>

	<div id="sale_return_policy">
		<?php echo nl2br(esc(config('OSPOS')->return_policy)) ?>
	</div>

	<div id="barcode">
		<img alt='<?php echo esc($barcode, 'attr') ?>' src='data:image/png;base64,<?php echo esc($barcode, 'attr') ?>' /><br>
		<?php echo $sale_id ?>
	</div>
</div>
