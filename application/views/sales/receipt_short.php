<div id="receipt_wrapper" style="font-size:<?= $this->config->item('receipt_font_size'); ?>px">
	<div id="receipt_header">
		<?php
		if ($this->config->item('company_logo') != '') {
		?>
			<div id="company_name">
				<img id="image" src="<?= base_url('uploads/' . $this->config->item('company_logo')); ?>" alt="company_logo" />
			</div>
		<?php
		}
		?>

		<?php
		if ($this->config->item('receipt_show_company_name')) {
		?>
			<div id="company_name"><?= $this->config->item('company'); ?></div>
		<?php
		}
		?>

		<div id="company_address"><?= nl2br($this->config->item('address')); ?></div>
		<div id="company_phone"><?= $this->config->item('phone'); ?></div>
		<div id="sale_receipt"><?= $this->lang->line('sales_receipt'); ?></div>
		<div id="sale_time"><?= $transaction_time ?></div>
	</div>

	<div id="receipt_general_info">
		<?php
		if (isset($customer)) {
		?>
			<div id="customer"><?= $this->lang->line('customers_customer') . ": " . $customer; ?></div>
		<?php
		}
		?>

		<div id="sale_id"><?= $this->lang->line('sales_id') . ": " . $sale_id; ?></div>

		<?php
		if (!empty($invoice_number)) {
		?>
			<div id="invoice_number"><?= $this->lang->line('sales_invoice_number') . ": " . $invoice_number; ?></div>
		<?php
		}
		?>

		<div id="employee"><?= $this->lang->line('employees_employee') . ": " . $employee; ?></div>
	</div>

	<table id="receipt_items">
		<tr>
			<th style="width:50%;"><?= $this->lang->line('sales_description_abbrv'); ?></th>
			<th style="width:25%;"><?= $this->lang->line('sales_quantity'); ?></th>
			<th colspan="4" style="width:25%;" class="total-value"><?= $this->lang->line('sales_total'); ?></th>
		</tr>
		<?php
		foreach ($cart as $line => $item) {
		?>
			<tr>
				<td><?= ucfirst($item['name'] . ' ' . $item['attribute_values']); ?></td>
				<td><?= to_quantity_decimals($item['quantity']); ?></td>
				<td class="total-value"><?= to_currency($item[($this->config->item('receipt_show_total_discount') ? 'total' : 'discounted_total')]); ?></td>
			</tr>
			<tr>
				<?php
				if ($this->config->item('receipt_show_description')) {
				?>
					<td colspan="2"><?= $item['description']; ?></td>
				<?php
				}
				?>
				<?php
				if ($this->config->item('receipt_show_serialnumber')) {
				?>
					<td><?= $item['serialnumber']; ?></td>
				<?php
				}
				?>
			</tr>
			<?php
			if ($item['discount'] > 0) {
			?>
				<tr>
					<?php
					if ($item['discount_type'] == FIXED) {
					?>
						<td colspan="2" class="discount"><?= to_currency($item['discount']) . " " . $this->lang->line("sales_discount") ?></td>
					<?php
					} elseif ($item['discount_type'] == PERCENT) {
					?>
						<td colspan="2" class="discount"><?= to_decimals($item['discount']) . " " . $this->lang->line("sales_discount_included") ?></td>
					<?php
					}
					?>
					<td class="total-value"><?= to_currency($item['discounted_total']); ?></td>
				</tr>
			<?php
			}
			?>

		<?php
		}
		?>

		<?php
		if ($this->config->item('receipt_show_total_discount') && $discount > 0) {
		?>
			<tr>
				<td colspan="2" style='text-align:right;border-top:2px solid #000000;'><?= $this->lang->line('sales_sub_total'); ?></td>
				<td style='text-align:right;border-top:2px solid #000000;'><?= to_currency($subtotal); ?></td>
			</tr>
			<tr>
				<td colspan="2" class="total-value"><?= $this->lang->line('sales_discount'); ?>:</td>
				<td class="total-value"><?= to_currency($discount * -1); ?></td>
			</tr>
		<?php
		}
		?>

		<?php
		if ($this->config->item('receipt_show_taxes')) {
		?>
			<tr>
				<td colspan="2" style='text-align:right;border-top:2px solid #000000;'><?= $this->lang->line('sales_sub_total'); ?></td>
				<td style='text-align:right;border-top:2px solid #000000;'><?= to_currency($subtotal); ?></td>
			</tr>
			<?php
			foreach ($taxes as $tax_group_index => $tax) {
			?>
				<tr>
					<td colspan="2" class="total-value"><?= (float)$tax['tax_rate'] . '% ' . $tax['tax_group']; ?>:</td>
					<td class="total-value"><?= to_currency_tax($tax['sale_tax_amount']); ?></td>
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
			<td colspan="2" style="text-align:right;<?= $border ? 'border-top: 2px solid black;' : ''; ?>"><?= $this->lang->line('sales_total'); ?></td>
			<td style="text-align:right;<?= $border ? 'border-top: 2px solid black;' : ''; ?>"><?= to_currency($total); ?></td>
		</tr>


		<?php
		$only_sale_check = FALSE;
		$show_giftcard_remainder = FALSE;
		foreach ($payments as $payment_id => $payment) {
			$only_sale_check |= $payment['payment_type'] == $this->lang->line('sales_check');
			$splitpayment = explode(':', $payment['payment_type']);
			$show_giftcard_remainder |= $splitpayment[0] == $this->lang->line('sales_giftcard');
		?>
			<tr>
				<td colspan="2" style="text-align:right;"><?= $splitpayment[0]; ?> </td>
				<td class="total-value"><?= to_currency($payment['payment_amount'] * -1); ?></td>
			</tr>
		<?php
		}
		?>

		<?php
		if (isset($cur_giftcard_value) && $show_giftcard_remainder) {
		?>
			<tr>
				<td colspan="2" style="text-align:right;"><?= $this->lang->line('sales_giftcard_balance'); ?></td>
				<td class="total-value"><?= to_currency($cur_giftcard_value); ?></td>
			</tr>
		<?php
		}
		?>
		<tr>
			<td colspan="2" style="text-align:right;"> <?= $this->lang->line($amount_change >= 0 ? ($only_sale_check ? 'sales_check_balance' : 'sales_change_due') : 'sales_amount_due'); ?> </td>
			<td class="total-value"><?= to_currency($amount_change); ?></td>
		</tr>
	</table>

	<div id="sale_return_policy">
		<?= nl2br($this->config->item('return_policy')); ?>
	</div>

	<div id="barcode">
		<img src='data:image/png;base64,<?= $barcode; ?>' /><br>
		<?= $sale_id; ?>
	</div>
</div>