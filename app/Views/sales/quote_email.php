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
 */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="<?php echo $this->request->getLocale() ?>">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo base_url('css/invoice_email.css') ?>"/>
	<title><?php echo lang('Sales.send_quote') ?></title>
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
	<div id="header"><?php echo lang('Sales.quote') ?></div>
	<table id="info">
		<tr>
			<td id="logo">
				<?php if($config['company_logo'] != '')
				{
				?>
					<img id="image" src="<?php echo 'uploads/' . esc($config['company_logo'],'url') ?>" alt="company_logo" />
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
					<?php echo esc($config['company']) ?>
					<?php echo nl2br(esc($company_info)) ?>
				</div>
			</td>
			<td id="meta">
				<table id="meta-content"  align="right">
					<tr>
						<td class="meta-head"><?php echo lang('Sales.quote_number') ?> </td>
						<td><?php echo esc($quote_number) ?></td>
					</tr>
					<tr>
						<td class="meta-head"><?php echo lang('Common.date') ?></td>
						<td><?php echo $transaction_date ?></td>
					</tr>
					<?php
					if($amount_due > 0)
					{
					?>
						<tr>
							<td class="meta-head"><?php echo lang('Sales.amount_due') ?></td>
							<td class="due"><?php echo to_currency($total) ?></td>
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
			<th><?php echo lang('Sales.item_number') ?></th>
			<th><?php echo lang('Sales.item_name') ?></th>
			<th><?php echo lang('Sales.quantity') ?></th>
			<th><?php echo lang('Sales.price') ?></th>
			<th><?php echo lang('Sales.discount') ?></th>
			<?php
			$quote_columns = 6;
			if($discount > 0)
			{
				$quote_columns = $quote_columns + 1;
				?>
				<th><?php echo lang('Sales.customer_discount') ?></th>
				<?php
			}
			?>
			<th><?php echo lang('Sales.total') ?></th>
		</tr>

		<?php
		foreach($cart as $line => $item)
		{
			if($item['print_option'] == PRINT_YES)
			{
			?>
				<tr class="item-row">
					<td><?php echo esc($item['item_number']) ?></td>
					<td class="item-name"><?php echo esc($item['name']) ?></td>
					<td><?php echo to_quantity_decimals($item['quantity']) ?></td>
					<td><?php echo to_currency($item['price']) ?></td>
					<td><?php echo ($item['discount_type'] == FIXED) ? to_currency($item['discount']) : to_decimals($item['discount']) . '%' ?></td>
					<?php if($discount > 0): ?>
						<td><?php echo to_currency($item['discounted_total'] / $item['quantity']) ?></td>
					<?php endif; ?>
					<td class="total-line"><?php echo to_currency($item['discounted_total']) ?></td>
				</tr>
			<?php
			}
		}
		?>

		<tr>
			<td colspan="<?php echo $quote_columns ?>" align="center"><?php echo '&nbsp;' //TODO: Replace the php echo for nbsp with just straight html? ?></td>
		</tr>

		<tr>
			<td colspan="<?php echo $quote_columns-3 ?>" class="blank"> </td>
			<td colspan="2" class="total-line"><?php echo lang('Sales.sub_total') ?></td>
			<td id="subtotal" class="total-value"><?php echo to_currency($subtotal) ?></td>
		</tr>

		<?php
		foreach($taxes as $tax_group_index => $tax)
		{
		?>
			<tr>
				<td colspan="<?php echo $quote_columns-3 ?>" class="blank"> </td>
				<td colspan="2" class="total-line"><?php echo (float)$tax['tax_rate'] . '% ' . $tax['tax_group'] ?></td>
				<td id="taxes" class="total-value"><?php echo to_currency_tax($tax['sale_tax_amount']) ?></td>
			</tr>
		<?php
		}
		?>

		<tr>
			<td colspan="<?php echo $quote_columns-3 ?>" class="blank"> </td>
			<td colspan="2" class="total-line"><?php echo lang('Sales.total') ?></td>
			<td id="total" class="total-value"><?php echo to_currency($total) ?></td>
		</tr>
	</table>

	<div id="terms">
		<div id="sale_return_policy">
			<h5>
				<span><?php echo nl2br(esc($config['payment_message'])) ?></span>
				<span><?php echo lang('Sales.comments') . ': ' . (empty($comments) ? $config['quote_default_comments'] : esc($comments)) ?></span>
			</h5>
			<?php echo nl2br(esc($config['return_policy'])) ?>
		</div>
		<div id='barcode'>
			<?php echo $quote_number ?>
		</div>
	</div>
</div>

</body>
</html>
