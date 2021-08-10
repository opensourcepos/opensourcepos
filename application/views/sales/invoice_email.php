<!doctype html>
<html lang="<?= current_language_code(); ?>">

<head>
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="<?= base_url() . 'css/invoice_email.css'; ?>" />
</head>

<body>

	<?php
	if (isset($error_message)) {
		echo "<div class='alert alert-dismissible alert-danger'>" . $error_message . "</div>";
		exit;
	}
	?>

	<div id="page-wrap">
		<div id="header"><?= $this->lang->line('sales_invoice'); ?></div>
		<table id="info">
			<tr>
				<td id="logo">
					<?php if ($this->config->item('company_logo') != '') {
					?>
						<img id="image" src="data:<?= $mimetype ?>;base64,<?= base64_encode(file_get_contents('uploads/' . $this->config->item('company_logo'))); ?>" alt="company_logo" />
					<?php
					}
					?>
				</td>
				<td id="customer-title" id="customer"><?php if (isset($customer)) {
															echo nl2br($customer_info);
														} ?></td>
			</tr>
			<tr>
				<td id="company-title" id="company">
					<?= $this->config->item('company'); ?><br />
					<?= nl2br($company_info); ?>
				</td>
				<td id="meta">
					<table id="meta-content" align="right">
						<tr>
							<td class="meta-head"><?= $this->lang->line('sales_invoice_number'); ?></td>
							<td><?= $invoice_number; ?></td>
						</tr>
						<tr>
							<td class="meta-head"><?= $this->lang->line('common_date'); ?></td>
							<td><?= $transaction_date; ?></td>
						</tr>
						<?php
						if ($amount_due > 0) {
						?>
							<tr>
								<td class="meta-head"><?= $this->lang->line('sales_amount_due'); ?></td>
								<td class="due"><?= to_currency($total); ?></td>
							</tr>
						<?php
						}
						?>
					</table>
				</td>
			</tr>
		</table>

		<table id="items">
			<tr>
				<th><?= $this->lang->line('sales_item_number'); ?></th>
				<th><?= $this->lang->line('sales_item_name'); ?></th>
				<th><?= $this->lang->line('sales_quantity'); ?></th>
				<th><?= $this->lang->line('sales_price'); ?></th>
				<th><?= $this->lang->line('sales_discount'); ?></th>
				<?php
				$invoice_columns = 6;
				if ($discount > 0) {
					$invoice_columns = $invoice_columns + 1;
				?>
					<th><?= $this->lang->line('sales_customer_discount'); ?></th>
				<?php
				}
				?>
				<th><?= $this->lang->line('sales_total'); ?></th>
			</tr>

			<?php
			foreach ($cart as $line => $item) {
				if ($item['print_option'] == PRINT_YES) {
			?>
					<tr class="item-row">
						<td><?= $item['item_number']; ?></td>
						<td class="item-name"><?= $item['name']; ?></td>
						<td><?= to_quantity_decimals($item['quantity']); ?></td>
						<td><?= to_currency($item['price']); ?></td>
						<td><?= ($item['discount_type'] == FIXED) ? to_currency($item['discount']) : to_decimals($item['discount']) . '%'; ?></td>
						<?php if ($item['discount'] > 0) : ?>
							<td><?= to_currency($item['discounted_total'] / $item['quantity']); ?></td>
						<?php endif; ?>
						<td class="total-line"><?= to_currency($item['discounted_total']); ?></td>
					</tr>
			<?php
				}
			}
			?>

			<tr>
				<td colspan="<?= $invoice_columns; ?>" align="center"><?= '&nbsp;'; ?></td>
			</tr>

			<tr>
				<td colspan="<?= $invoice_columns - 3; ?>" class="blank"> </td>
				<td colspan="2" class="total-line"><?= $this->lang->line('sales_sub_total'); ?></td>
				<td id="subtotal" class="total-value"><?= to_currency($subtotal); ?></td>
			</tr>

			<?php
			foreach ($taxes as $tax_group_index => $tax) {
			?>
				<tr>
					<td colspan="<?= $invoice_columns - 3; ?>" class="blank"> </td>
					<td colspan="2" class="total-line"><?= (float)$tax['tax_rate'] . '% ' . $tax['tax_group']; ?></td>
					<td id="taxes" class="total-value"><?= to_currency_tax($tax['sale_tax_amount']); ?></td>
				</tr>
			<?php
			}
			?>

			<tr>
				<td colspan="<?= $invoice_columns - 3; ?>" class="blank"> </td>
				<td colspan="2" class="total-line"><?= $this->lang->line('sales_total'); ?></td>
				<td id="total" class="total-value"><?= to_currency($total); ?></td>
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
					<td colspan="<?= $invoice_columns - 3; ?>" class="blank"> </td>
					<td colspan="2" class="total-line"><?= $splitpayment[0]; ?></td>
					<td class="total-value"><?= to_currency(-$payment['payment_amount']); ?></td>
				</tr>
			<?php
			}
			?>

			<?php
			if (isset($cur_giftcard_value) && $show_giftcard_remainder) {
			?>
				<tr>
					<td colspan="<?= $invoice_columns - 3; ?>" class="blank"> </td>
					<td colspan="2" class="total-line"><?= $this->lang->line('sales_giftcard_balance'); ?></td>
					<td class="total-value" id="giftcard"><?= to_currency($cur_giftcard_value); ?></td>
				</tr>
			<?php
			}
			?>

			<?php
			if (!empty($payments)) {
			?>
				<tr>
					<td colspan="<?= $invoice_columns - 3; ?>" class="blank"> </td>
					<td colspan="2" class="total-line"><?= $this->lang->line($amount_change >= 0 ? ($only_sale_check ? 'sales_check_balance' : 'sales_change_due') : 'sales_amount_due'); ?></td>
					<td class="total-value"><?= to_currency($amount_change); ?></td>
				</tr>
			<?php
			}
			?>
		</table>

		<div id="terms">
			<div id="sale_return_policy">
				<h5>
					<div><?= nl2br($this->config->item('payment_message')); ?></div>
					<div><?= $this->lang->line('sales_comments') . ': ' . (empty($comments) ? $this->config->item('invoice_default_comments') : $comments); ?></div>
				</h5>
				<?= nl2br($this->config->item('return_policy')); ?>
			</div>
			<div id='barcode'>
				<img src='data:image/png;base64,<?= $barcode; ?>' /><br>
				<?= $sale_id; ?>
			</div>
		</div>
	</div>

</body>

</html>