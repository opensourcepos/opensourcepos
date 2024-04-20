<?php
/**
 * @var string $sale_id_num
 * @var bool $print_after_sale
 * @var string $customer_info
 * @var string $company_info
 * @var string $invoice_number
 * @var string $transaction_date
 * @var float $total
 * @var bool $include_hsn
 * @var string $discount
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
<?= view('partial/header') ?>

<?php
if(isset($error_message))
{
	echo "<div class='alert alert-dismissible alert-danger'>$error_message</div>";
	exit;
}
?>

<?php if(!empty($customer_email)): ?>
<script type="application/javascript">
$(document).ready(function()
{
	var send_email = function()
	{
		$.get('<?= site_url() . "sales/send_pdf/$sale_id_num" ?>',
			function(response)
			{
				$.notify({ message: response.message }, { type: response.success ? 'success' : 'danger'});
			}, 'json'
		);
	};

	$("#show_email_button").click(send_email);

	<?php if(!empty($email_receipt)): ?>
		send_email();
	<?php endif; ?>
});
</script>
<?php endif; ?>

<?= view('partial/print_receipt', ['print_after_sale' => $print_after_sale, 'selected_printer' => 'invoice_printer']) ?>

<div class="print_hide" id="control_buttons" style="text-align:right">
	<a href="javascript:printdoc();"><div class="btn btn-info btn-sm" id="show_print_button"><?= '<span class="glyphicon glyphicon-print">&nbsp</span>' . lang('Common.print') ?></div></a>
	<?php /* this line will allow to print and go back to sales automatically.... echo anchor('sales', '<span class=\'glyphicon glyphicon-print\'>&nbsp</span>' . lang('Common.print'), ['class' => 'btn btn-info btn-sm', 'id' => 'show_print_button', 'onclick' => 'window.print();')); */ ?>
	<?php if(isset($customer_email) && !empty($customer_email)): ?>
		<a href="javascript:void(0);"><div class="btn btn-info btn-sm" id="show_email_button"><?= '<span class="glyphicon glyphicon-envelope">&nbsp</span>' . lang('Sales.send_invoice') ?></div></a>
	<?php endif; ?>
	<?= anchor("sales", '<span class="glyphicon glyphicon-shopping-cart">&nbsp</span>' . lang('Sales.register'), ['class' => 'btn btn-info btn-sm', 'id' => 'show_sales_button']) ?>
	<?= anchor("sales/manage", '<span class="glyphicon glyphicon-list-alt">&nbsp</span>' . lang('Sales.takings'), ['class' => 'btn btn-info btn-sm', 'id' => 'show_takings_button']) ?>
</div>

<div id="page-wrap">
	<div id="header"><?= lang('Sales.invoice') ?></div>
	<div id="block1">
		<div id="customer-title">
			<?php
			if(isset($customer))
			{
			?>
				<div id="customer"><?= nl2br(esc($customer_info)) ?></div>
			<?php
			}
			?>
		</div>

		<div id="logo">
			<?php
			if($config['company_logo'] != '')
			{
			?>
				<img id="image" src="<?= base_url('uploads/' . $config['company_logo']) ?>" alt="company_logo" />
			<?php
			}
			?>
			<div>&nbsp</div>
			<?php
			if($config['receipt_show_company_name'])
			{
			?>
				<div id="company_name"><?= $config['company'] ?></div>
			<?php
			}
			?>
		</div>
	</div>

	<div id="block2">
		<div id="company-title"><?= nl2br(esc($company_info)) ?></div>
		<table id="meta">
			<tr>
				<td class="meta-head"><?= lang('Sales.invoice_number') ?> </td>
				<td><?= esc($invoice_number) ?></td>
			</tr>
			<tr>
				<td class="meta-head"><?= lang('Common.date') ?></td>
				<td><?= esc($transaction_date) ?></td>
			</tr>
			<tr>
				<td class="meta-head"><?= lang('Sales.invoice_total') ?></td>
				<td><?= to_currency($total) ?></td>
			</tr>
		</table>
	</div>

	<table id="items">
		<tr>
			<th><?= lang('Sales.item_number') ?></th>
			<?php
				$invoice_columns = 6;
				if($include_hsn)
				{
					$invoice_columns += 1;
					?>
					<th><?= lang('Sales.hsn') ?></th>
					<?php
				}
			?>
			<th><?= lang('Sales.item_name') ?></th>
			<th><?= lang('Sales.quantity') ?></th>
			<th><?= lang('Sales.price') ?></th>
			<th><?= lang('Sales.discount') ?></th>
			<?php
			if($discount > 0)
			{
				$invoice_columns += 1;
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
					<?php if($include_hsn): ?>
						<td style='text-align:center;'><?= esc($item['hsn_code']) ?></td>
					<?php endif; ?>
					<td class="item-name"><?= ($item['is_serialized'] || $item['allow_alt_description']) && !empty($item['description']) ? $item['description'] : $item['name'] . ' ' . $item['attribute_values'] ?></td>
					<td style='text-align:center;'><?= to_quantity_decimals($item['quantity']) ?></td>
					<td><?= to_currency($item['price']) ?></td>
					<td style='text-align:center;'><?= ($item['discount_type'] == FIXED) ? to_currency($item['discount']) : to_decimals($item['discount']) . '%' ?></td>
					<?php if($discount > 0): ?>
					<td style='text-align:center;'><?= to_currency($item['discounted_total'] / $item['quantity']) ?></td>
					<?php endif; ?>
					<td style='border-right: solid 1px; text-align:right;'><?= to_currency($item['discounted_total']) ?></td>
				</tr>
				<?php
				if($item['is_serialized'])
				{
				?>
					<tr class="item-row">
						<td class="item-description" colspan="<?= $invoice_columns-1 ?>"></td>
						<td style='text-align:center;'><?= $item['serialnumber'] //TODO: serialnumber does not match variable naming conventions for this project. ?></td>
					</tr>
				<?php
				}
			}
		}
		?>

		<tr>
			<td class="blank" colspan="<?= $invoice_columns ?>" style="text-align: center;"><?= '&nbsp;' ?></td>
		</tr>

		<tr>
			<td colspan="<?= $invoice_columns-3 ?>" class="blank-bottom"> </td>
			<td colspan="2" class="total-line"><?= lang('Sales.sub_total') ?></td>
			<td class="total-value" id="subtotal"><?= to_currency($subtotal) ?></td>
		</tr>

		<?php
		foreach($taxes as $tax_group_index=>$tax)
		{
		?>
			<tr>
				<td colspan="<?= $invoice_columns-3 ?>" class="blank"> </td>
				<td colspan="2" class="total-line"><?= (float)$tax['tax_rate'] . '% ' . $tax['tax_group'] ?></td>
				<td class="total-value" id="taxes"><?= to_currency_tax($tax['sale_tax_amount']) ?></td>
			</tr>
		<?php
		}
		?>

		<tr>
			<td colspan="<?= $invoice_columns-3 ?>" class="blank"> </td>
			<td colspan="2" class="total-line"><?= lang('Sales.total') ?></td>
			<td class="total-value" id="total"><?= to_currency($total) ?></td>
		</tr>

		<?php
		$only_sale_check = false;
		$show_giftcard_remainder = false;
		foreach($payments as $payment_id => $payment)
		{
			$only_sale_check |= $payment['payment_type'] == lang('Sales.check');
			$splitpayment = explode(':', $payment['payment_type']);	//TODO: $splitpayment does not meet variable naming standards for this project.
			$show_giftcard_remainder |= $splitpayment[0] == lang('Sales.giftcard');
		?>
			<tr>
				<td colspan="<?= $invoice_columns-3 ?>" class="blank"> </td>
				<td colspan="2" class="total-line"><?= $splitpayment[0] ?></td>
				<td class="total-value" id="paid"><?= to_currency( $payment['payment_amount'] * -1 ) ?></td>
			</tr>
		<?php
		}

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

		if(!empty($payments))
		{
		?>
		<tr>
			<td colspan="<?= $invoice_columns-3 ?>" class="blank"> </td>
			<td colspan="2" class="total-line"><?= lang($amount_change >= 0 ? ($only_sale_check ? 'Sales.check_balance' : 'Sales.change_due') : 'Sales.amount_due') ?></td>
			<td class="total-value" id="change"><?= to_currency($amount_change) ?></td>
		</tr>
		<?php
		}
		?>

	</table>

	<div id="terms">
		<div id="sale_return_policy">
			<h5>
				<span><?= nl2br(esc($config['payment_message'])) ?></span>
				<span style='padding:4%;'><?= empty($comments) ? '' : lang('Sales.comments') . ': ' . esc($comments) ?></span>
				<span style='padding:4%;'><?= esc($config['invoice_default_comments']) ?></span>
			</h5>
			<div style='padding:2%;'><?= nl2br(esc($config['return_policy'])) ?></div>
		</div>
		<div id='barcode'>
			<?= $barcode ?><br>
			<?= $sale_id ?>
		</div>
	</div>
</div>

<script type="application/javascript">
$(window).on("load", function()
{
	// install firefox addon in order to use this plugin
	if(window.jsPrintSetup)
	{
		<?php if(!$config['print_header'])
		{
		?>
			// set page header
			jsPrintSetup.setOption('headerStrLeft', '');
			jsPrintSetup.setOption('headerStrCenter', '');
			jsPrintSetup.setOption('headerStrRight', '');
		<?php
		}

		if(!$config['print_footer'])
		{
		?>
			// set empty page footer
			jsPrintSetup.setOption('footerStrLeft', '');
			jsPrintSetup.setOption('footerStrCenter', '');
			jsPrintSetup.setOption('footerStrRight', '');
		<?php
		}
		?>
	}
});
</script>

<?= view('partial/footer') ?>
