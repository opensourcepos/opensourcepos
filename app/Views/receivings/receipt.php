<?php
/**
 * @var bool $print_after_sale
 * @var string $transaction_time
 * @var int $receiving_id
 * @var string $employee
 * @var array $cart
 * @var bool $show_stock_locations
 * @var float $total
 * @var string $mode
 * @var string $payment_type
 * @var float $amount_tendered
 * @var float $amount_change
 * @var string $barcode
 * @var array $config
 */
?>
<?= view('partial/header') ?>

<?php
	if (isset($error_message))
	{
		echo '<div class=\'alert alert-dismissible alert-danger\'>' . esc($error_message) . '</div>';
		exit;
	}

	echo view('partial/print_receipt', ['print_after_sale', $print_after_sale, 'selected_printer' => 'receipt_printer']) ?>

<div class="print_hide" id="control_buttons" style="text-align:right">
	<a href="javascript:printdoc();"><div class="btn btn-info btn-sm" id="show_print_button"><?= '<span class="glyphicon glyphicon-print">&nbsp</span>' . lang('Common.print') ?></div></a>
	<?= anchor("receivings", '<span class="glyphicon glyphicon-save">&nbsp</span>' . lang('Receivings.register'), ['class' => 'btn btn-info btn-sm', 'id' => 'show_sales_button']) ?>
</div>

<div id="receipt_wrapper">
	<div id="receipt_header">
		<?php
		if ($config['company_logo'] != '')
		{
		?>
			<div id="company_name"><img id="image" src="<?= esc(base_url('uploads/' . $config['company_logo']), 'url') ?>" alt="company_logo" /></div>
		<?php
		}
		?>

		<?php
		if ($config['receipt_show_company_name'])
		{
		?>
			<div id="company_name"><?= esc($config['company']) ?></div>
		<?php
		}
		?>

		<div id="company_address"><?= esc(nl2br($config['address'])) ?></div>
		<div id="company_phone"><?= esc($config['phone']) ?></div>
		<div id="sale_receipt"><?= lang('Receivings.receipt') ?></div>
		<div id="sale_time"><?= esc($transaction_time) ?></div>
	</div>

	<div id="receipt_general_info">
		<?php
		if(isset($supplier))
		{
		?>
			<div id="customer"><?= lang('Suppliers.supplier') . esc(": $supplier") ?></div>
		<?php
		}
		?>
		<div id="sale_id"><?= lang('Receivings.id') . ": $receiving_id" ?></div>
		<?php
		if (!empty($reference))
		{
		?>
			<div id="reference"><?= lang('Receivings.reference') . esc(": $reference") ?></div>
		<?php
		}
		?>
		<div id="employee"><?= lang('Employees.employee') . esc(": $employee") ?></div>
	</div>

	<table id="receipt_items">
		<tr>
			<th style="width:40%;"><?= lang('Items.item') ?></th>
			<th style="width:20%;"><?= lang('Common.price') ?></th>
			<th style="width:20%;"><?= lang('Sales.quantity') ?></th>
			<th style="width:15%;text-align:right;"><?= lang('Sales.total') ?></th>
		</tr>

		<?php
		foreach(array_reverse($cart, true) as $line => $item)
		{
		?>
			<tr>
				<td><?= esc($item['name'] . ' ' . $item['attribute_values']) ?></td>
				<td><?= to_currency($item['price']) ?></td>
				<td><?= to_quantity_decimals($item['quantity']) . ' ' . ($show_stock_locations ? ' [' . esc($item['stock_name']) . ']' : '') ?>&nbsp;&nbsp;&nbsp;x <?= $item['receiving_quantity'] != 0 ? to_quantity_decimals($item['receiving_quantity']) : 1 ?></td>
				<td><div class="total-value"><?= to_currency($item['total']) ?></div></td>
			</tr>
			<tr>
				<td ><?= esc($item['serialnumber']) ?></td>
			</tr>
			<?php
			if ($item['discount'] > 0 )
			{
			?>
				<tr>
					<?php
					if($item['discount_type'] == FIXED)
					{
					?>
						<td colspan="3" class="discount"><?= to_currency($item['discount']) . ' ' . lang('Sales.discount') ?></td>
					<?php
					}
					elseif($item['discount_type'] == PERCENT)
					{
					?>
						<td colspan="3" class="discount"><?= to_decimals($item['discount']) . ' ' . lang('Sales.discount_included') ?></td>
					<?php
					}
					?>
				</tr>
			<?php
			}
			?>
		<?php
		}
		?>
		<tr>
			<td colspan="3" style='text-align:right;border-top:2px solid #000000;'><?= lang('Sales.total') ?></td>
			<td style='border-top:2px solid #000000;'><div class="total-value"><?= to_currency($total) ?></div></td>
		</tr>
		<?php
		if($mode != 'requisition')
		{
		?>
			<tr>
				<td colspan="3" style='text-align:right;'><?= lang('Sales.payment') ?></td>
				<td><div class="total-value"><?= esc($payment_type) ?></div></td>
			</tr>

			<?php if(isset($amount_change))
			{
			?>
				<tr>
					<td colspan="3" style='text-align:right;'><?= lang('Sales.amount_tendered') ?></td>
					<td><div class="total-value"><?= to_currency($amount_tendered) ?></div></td>
				</tr>

				<tr>
					<td colspan="3" style='text-align:right;'><?= lang('Sales.change_due') ?></td>
					<td><div class="total-value"><?= $amount_change ?></div></td>
				</tr>
			<?php
			}
			?>
		<?php
		}
		?>
	</table>

	<div id="sale_return_policy">
		<?= esc(nl2br($config['return_policy'])) ?>
	</div>

	<div id='barcode'>
		<img alt='<?= esc($barcode) ?>' src='data:image/png;base64,<?= esc($barcode) ?>' /><br>
		<?= $receiving_id ?>
	</div>
</div>
<?= view('partial/footer') ?>
