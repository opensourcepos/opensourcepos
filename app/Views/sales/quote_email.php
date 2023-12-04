<?php
/**
 * @var string $customer_info
 * @var string $company_info
 * @var string $quote_number
 * @var string $transaction_date
 * @var float $amount_due
 * @var float $total
 * @var float $discount
 * @var array $cart
 * @var float $subtotal
 * @var array $taxes
 * @var array $config
 */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="<?= $this->request->getLocale() ?>">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?= base_url('css/invoice_email.css') ?>"/>
	<title><?= lang('Sales.send_quote') ?></title>
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
	<div id="header"><?= lang('Sales.quote') ?></div>
	<table id="info">
		<tr>
			<td id="logo">
				<?php if($config['company_logo'] != '')
				{
				?>
					<img id="image" src="<?= 'uploads/' . esc($config['company_logo'],'url') ?>" alt="company_logo" />
				<?php
				}
				?>
			</td>
			<td id="customer-title">
				<pre><?php if(isset($customer)) { echo esc($customer_info); } ?></pre>
			</td>
		</tr>
		<tr>
			<td id="company-title">
				<div id="company">
					<?= esc($config['company']) ?>
					<?= nl2br(esc($company_info)) ?>
				</div>
			</td>
			<td id="meta">
				<table id="meta-content"  align="right">
					<tr>
						<td class="meta-head"><?= lang('Sales.quote_number') ?> </td>
						<td><?= esc($quote_number) ?></td>
					</tr>
					<tr>
						<td class="meta-head"><?= lang('Common.date') ?></td>
						<td><?= $transaction_date ?></td>
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
			$quote_columns = 6;
			if($discount > 0)
			{
				$quote_columns = $quote_columns + 1;
				?>
				<th><?= lang('Sales.customer_discount') ?></th>
				<?php
			}
			?>
			<th><?= lang('Sales.total') ?></th>
		</tr>

		<?php
		foreach($cart as $line => $item)
		{
			if($item['print_option'] == PRINT_YES)
			{
			?>
				<tr class="item-row">
					<td><?= esc($item['item_number']) ?></td>
					<td class="item-name"><?= esc($item['name']) ?></td>
					<td><?= to_quantity_decimals($item['quantity']) ?></td>
					<td><?= to_currency($item['price']) ?></td>
					<td><?= ($item['discount_type'] == FIXED) ? to_currency($item['discount']) : to_decimals($item['discount']) . '%' ?></td>
					<?php if($discount > 0): ?>
						<td><?= to_currency($item['discounted_total'] / $item['quantity']) ?></td>
					<?php endif; ?>
					<td class="total-line"><?= to_currency($item['discounted_total']) ?></td>
				</tr>
			<?php
			}
		}
		?>

		<tr>
			<td colspan="<?= $quote_columns ?>" align="center"><?= '&nbsp;' //TODO: Replace the php echo for nbsp with just straight html? ?></td>
		</tr>

		<tr>
			<td colspan="<?= $quote_columns-3 ?>" class="blank"> </td>
			<td colspan="2" class="total-line"><?= lang('Sales.sub_total') ?></td>
			<td id="subtotal" class="total-value"><?= to_currency($subtotal) ?></td>
		</tr>

		<?php
		foreach($taxes as $tax_group_index => $tax)
		{
		?>
			<tr>
				<td colspan="<?= $quote_columns-3 ?>" class="blank"> </td>
				<td colspan="2" class="total-line"><?= (float)$tax['tax_rate'] . '% ' . $tax['tax_group'] ?></td>
				<td id="taxes" class="total-value"><?= to_currency_tax($tax['sale_tax_amount']) ?></td>
			</tr>
		<?php
		}
		?>

		<tr>
			<td colspan="<?= $quote_columns-3 ?>" class="blank"> </td>
			<td colspan="2" class="total-line"><?= lang('Sales.total') ?></td>
			<td id="total" class="total-value"><?= to_currency($total) ?></td>
		</tr>
	</table>

	<div id="terms">
		<div id="sale_return_policy">
			<h5>
				<span><?= nl2br(esc($config['payment_message'])) ?></span>
				<span><?= lang('Sales.comments') . ': ' . (empty($comments) ? $config['quote_default_comments'] : esc($comments)) ?></span>
			</h5>
			<?= nl2br(esc($config['return_policy'])) ?>
		</div>
		<div id='barcode'>
			<?= $quote_number ?>
		</div>
	</div>
</div>

</body>
</html>
