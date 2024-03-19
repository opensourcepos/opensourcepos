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
 * @var array $config
 */
?>
<div id="receipt_wrapper" style="width:100%;">
	<div id="receipt_header" style="text-align:center;">
		<?php
		if($config['company_logo'] != '')
		{
		?>
			<div id="company_name">
				<img id="image" src="data:image/png;base64,<?= base64_encode(file_get_contents('uploads/' . $config['company_logo'])) ?>" alt="company_logo" />
			</div>
		<?php
		}
		?>

		<?php
		if($config['receipt_show_company_name'])
		{
		?>
			<div id="company_name" style="font-size:150%; font-weight:bold;"><?= esc($config['company']) ?></div>
		<?php
		}
		?>

		<div id="company_address"><?= nl2br(esc($config['address'])) ?></div>
		<div id="company_phone"><?= esc($config['phone']) ?></div>
		<br>
		<div id="sale_receipt"><?= lang('Sales.receipt') ?></div>
		<div id="sale_time"><?= esc($transaction_time) ?></div>
	</div>

	<br>

	<div id="receipt_general_info" style="text-align:left;">
		<?php
		if(isset($customer))
		{
		?>
			<div id="customer"><?= lang('Customers.customer') . esc(": $customer") ?></div>
		<?php
		}
		?>

		<div id="sale_id"><?= lang('Sales.id') . esc(": $sale_id") ?></div>
		<div id="employee"><?= lang('Employees.employee') . esc(": $employee") ?></div>
	</div>

	<br>

	<table id="receipt_items" style="text-align:left;width:100%;">
		<tr>
			<th style="width:40%;"><?= lang('Sales.description_abbrv') ?></th>
			<th style="width:20%;"><?= lang('Sales.price') ?></th>
			<th style="width:20%;"><?= lang('Sales.quantity') ?></th>
			<th style="width:20%;text-align:right;"><?= lang('Sales.total') ?></th>
		</tr>
		<?php
		foreach($cart as $line => $item)
		{
			if($item['print_option'] == PRINT_YES)
			{
			?>
				<tr>
					<td><?= esc(ucfirst($item['name'] . ' ' . $item['attribute_values'])) ?></td>
					<td><?= to_currency($item['price']) ?></td>
					<td><?= to_quantity_decimals($item['quantity']) ?></td>
					<td style="text-align:right;"><?= to_currency($item[($config['receipt_show_total_discount'] ? 'total' : 'discounted_total')]) ?></td>
				</tr>
				<tr>
					<?php
					if($config['receipt_show_description'])
					{
					?>
						<td colspan="2"><?= esc($item['description']) ?></td>
					<?php
					}

					if($config['receipt_show_serialnumber'])
					{
					?>
						<td><?= esc($item['serialnumber']) ?></td>
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
							<td colspan="3" class="discount"><?= to_currency($item['discount']) . " " . lang('Sales.discount') ?></td>
						<?php
						}
						elseif($item['discount_type'] == PERCENT)
						{
						?>
							<td colspan="3" class="discount"><?= to_decimals($item['discount']) . " " . lang('Sales.discount_included') ?></td>
						<?php
						}
						?>
						<td class="total-value"><?= to_currency($item['discounted_total']) ?></td>
					</tr>
				<?php
				}
			}
		}

		if($config['receipt_show_total_discount'] && $discount > 0)
		{
		?>
			<tr>
				<td colspan="3" style="text-align:right;border-top:2px solid #000000;"><?= lang('Sales.sub_total') ?></td>
				<td style="text-align:right;border-top:2px solid #000000;"><?= to_currency($subtotal) ?></td>
			</tr>
			<tr>
				<td colspan="3" style="text-align:right;"><?= lang('Sales.discount') ?>:</td>
				<td style="text-align:right;"><?= to_currency($discount*-1) ?></td>
			</tr>
		<?php
		}
		?>

		<?php
		if($config['receipt_show_taxes'])
		{
		?>
			<tr>
				<td colspan="3" style='text-align:right;border-top:2px solid #000000;'><?= lang('Sales.sub_total') ?></td>
				<td style="text-align:right;border-top:2px solid #000000;"><?= to_currency($subtotal) ?></td>
			</tr>
			<?php
			foreach($taxes as $tax_group_index => $tax)
			{
			?>
				<tr>
					<td colspan="3" style="text-align:right;"><?= (float)$tax['tax_rate'] . '% ' . $tax['tax_group'] ?>:</td>
					<td style="text-align:right;"><?= to_currency_tax($tax['sale_tax_amount']) ?></td>
				</tr>
			<?php
			}
			?>
		<?php
		}
		?>

		<tr>
		</tr>

		<?php $border = (!$config['receipt_show_taxes'] && !($config['receipt_show_total_discount'] && $discount > 0)) ?>
		<tr>
			<td colspan="3" style="<?= $border ? 'border-top: 2px solid black;' : '' ?>text-align:right;"><?= lang('Sales.total') ?></td>
			<td style="<?= $border ? 'border-top: 2px solid black;' : '' ?>text-align:right"><?= to_currency($total) ?></td>
		</tr>

		<tr>
			<td colspan="4">&nbsp;</td>
		</tr>

		<?php
		$only_sale_check = false;
		$show_giftcard_remainder = false;
		foreach($payments as $payment_id => $payment)
		{
			$only_sale_check |= $payment['payment_type'] == lang('Sales.check');
			$splitpayment = explode(':', $payment['payment_type']);
			$show_giftcard_remainder |= $splitpayment[0] == lang('Sales.giftcard');
		?>
			<tr>
				<td colspan="3" style="text-align:right;"><?= $splitpayment[0] ?> </td>
				<td style="text-align:right;"><?= to_currency( $payment['payment_amount'] * -1 ) ?></td>
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
			<td colspan="3" style="text-align:right;"><?= lang('Sales.giftcard_balance') ?></td>
			<td style="text-align:right"><?= to_currency($cur_giftcard_value) ?></td>
		</tr>
		<?php
		}
		?>
		<tr>
			<td colspan="3" style="text-align:right;"> <?= lang($amount_change >= 0 ? ($only_sale_check ? 'Sales.check_balance' : 'Sales.change_due') : 'Sales.amount_due') ?> </td>
			<td style="text-align:right"><?= to_currency($amount_change) ?></td>
		</tr>

		<tr>
			<td colspan="4">&nbsp;</td>
		</tr>
	</table>

	<div id="sale_return_policy" style="text-align:center">
		<?= nl2br(esc($config['return_policy'])) ?>
	</div>

	<br>

	<div id="barcode" style="text-align:center">
		<?= $barcode ?><br>
		<?= $sale_id ?>
	</div>
</div>
