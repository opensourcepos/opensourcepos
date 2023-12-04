<?php
/**
 * @var string $mimetype
 * @var string $customer_info
 * @var string $company_info
 * @var string $invoice_number
 * @var string $transaction_date
 * @var float $amount_due
 * @var float $total
 * @var float $discount
 * @var array $cart
 * @var float $subtotal
 * @var array $taxes
 * @var array $payments
 * @var float $amount_change
 * @var string $barcode
 * @var int $sale_id
 * @var array $config
 */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="<?= $this->request->getLocale() ?>">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?= base_url('css/invoice_email.css') ?>"/>
	<title><?= lang('Sales.email_receipt') ?></title>
</head>

<body>

<?php
if(isset($error_message))
{
	echo "<div class='alert alert-dismissible alert-danger'>$error_message</div>";
	exit;
}
?>

<div id="page-wrap">
	<div id="header"><?= lang('Sales.invoice') ?></div>
	<table id="info">
		<tr>
			<td id="logo">
				<?php if($config['company_logo'] != '')
				{
				?>
					<img id="image" src="data:<?= esc($mimetype) ?>;base64,<?= base64_encode(file_get_contents('uploads/' . esc($config['company_logo']))) ?>" alt="company_logo" />
				<?php
				}
				?>
			</td>
			<td id="customer-title" id="customer"><?php if(isset($customer)) { echo nl2br(esc($customer_info)); } ?></td>
		</tr>
		<tr>
			<td id="company-title" id="company">
				<?= esc($config['company']) ?><br/>
				<?= nl2br(esc($company_info)) ?>
			</td>
			<td id="meta">
				<table id="meta-content"  align="right">
				<tr>
					<td class="meta-head"><?= lang('Sales.invoice_number') ?></td>
					<td><?= esc($invoice_number) ?></td>
				</tr>
				<tr>
					<td class="meta-head"><?= lang('Common.date') ?></td>
					<td><?= esc($transaction_date) ?></td>
				</tr>
				<?php
				if($amount_due > 0)
				{
				?>
					<tr>
						<td class="meta-head"><?= lang('Sales.amount_due') ?></td>
						<td class="due"><?= to_currency($total) ?></td>
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
			<th><?= lang('Sales.item_number') ?></th>
			<th><?= lang('Sales.item_name') ?></th>
			<th><?= lang('Sales.quantity') ?></th>
			<th><?= lang('Sales.price') ?></th>
			<th><?= lang('Sales.discount') ?></th>
			<?php
			$invoice_columns = 6;
			if($discount > 0)
			{
				$invoice_columns = $invoice_columns + 1;
			?>
				<th><?= lang('Sales.customer_discount') ?></th>
			<?php
			}
			?>
			<th><?= lang('Sales.total') ?></th>
		</tr>

		<?php
		foreach($cart as $line=>$item)
		{
			if($item['print_option'] == PRINT_YES)
			{
		?>
				<tr class="item-row">
					<td><?= $item['item_number'] ?></td>
					<td class="item-name"><?= esc($item['name']) ?></td>
					<td><?= to_quantity_decimals($item['quantity']) ?></td>
					<td><?= to_currency($item['price']) ?></td>
					<td><?= ($item['discount_type'] == FIXED) ? to_currency($item['discount']) : to_decimals($item['discount']) . '%' ?></td>
					<?php if ($item['discount'] > 0): ?>
						<td><?= to_currency($item['discounted_total'] / $item['quantity']) ?></td>
					<?php endif; ?>
					<td class="total-line"><?= to_currency($item['discounted_total']) ?></td>
				</tr>
		<?php
			}
		}
		?>

		<tr>
			<td colspan="<?= $invoice_columns ?>" align="center"><?= '&nbsp;' ?></td>
		</tr>

		<tr>
			<td colspan="<?= $invoice_columns-3 ?>" class="blank"> </td>
			<td colspan="2" class="total-line"><?= lang('Sales.sub_total') ?></td>
			<td id="subtotal" class="total-value"><?= to_currency($subtotal) ?></td>
		</tr>

		<?php
		foreach($taxes as $tax_group_index => $tax)
		{
		?>
			<tr>
				<td colspan="<?= $invoice_columns-3 ?>" class="blank"> </td>
				<td colspan="2" class="total-line"><?= (float)$tax['tax_rate'] . '% ' . $tax['tax_group'] ?></td>
				<td id="taxes" class="total-value"><?= to_currency_tax($tax['sale_tax_amount']) ?></td>
			</tr>
		<?php
		}
		?>

		<tr>
			<td colspan="<?= $invoice_columns-3 ?>" class="blank"> </td>
			<td colspan="2" class="total-line"><?= lang('Sales.total') ?></td>
			<td id="total" class="total-value"><?= to_currency($total) ?></td>
		</tr>

		<?php
		$only_sale_check = false;
		$show_giftcard_remainder = false;

		foreach($payments as $payment_id=>$payment)
		{
			$only_sale_check |= $payment['payment_type'] == lang('Sales.check');
			$splitpayment = explode(':', $payment['payment_type']);	//TODO: $splitpayment does not meet the variable naming conventions for this project
			$show_giftcard_remainder |= $splitpayment[0] == lang('Sales.giftcard');
		?>
			<tr>
				<td colspan="<?= $invoice_columns-3 ?>" class="blank"> </td>
				<td colspan="2" class="total-line"><?= $splitpayment[0] ?></td>
				<td class="total-value"><?= to_currency(-$payment['payment_amount']) ?></td>
			</tr>
		<?php
		}
		?>

		<?php
		if(isset($cur_giftcard_value) && $show_giftcard_remainder)
		{
		?>
			<tr>
				<td colspan="<?= $invoice_columns-3 ?>" class="blank"> </td>
				<td colspan="2" class="total-line"><?= lang('Sales.giftcard_balance') ?></td>
				<td class="total-value" id="giftcard"><?= to_currency($cur_giftcard_value) ?></td>
			</tr>
		<?php
		}
		?>

		<?php
		if(!empty($payments))
		{
		?>
			<tr>
				<td colspan="<?= $invoice_columns-3 ?>" class="blank"> </td>
				<td colspan="2" class="total-line"><?= lang($amount_change >= 0 ? ($only_sale_check ? 'Sales.check_balance' : 'Sales.change_due') : 'Sales.amount_due') ?></td>
				<td class="total-value"><?= to_currency($amount_change) ?></td>
			</tr>
		<?php
		}
		?>
	</table>

	<div id="terms">
		<div id="sale_return_policy">
			<h5>
				<span><?= nl2br($config['payment_message']) ?></span>
				<span><?= lang('Sales.comments') . ': ' . (empty($comments) ? $config['invoice_default_comments'] : $comments) ?></span>
			</h5>
			<?= nl2br($config['return_policy']) ?>
		</div>
		<div id='barcode'>
			<img alt='<?= esc($barcode) ?>' src='data:image/png;base64,<?= esc($barcode) ?>' /><br>
			<?= $sale_id ?>
		</div>
	</div>
</div>

</body>
</html>
