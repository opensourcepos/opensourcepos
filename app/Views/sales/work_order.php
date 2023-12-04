<?php
/**
 * @var int $sale_id_num
 * @var bool $print_after_sale
 * @var string $sales_work_order
 * @var string $customer_info
 * @var string $company_info
 * @var string $work_order_number_label
 * @var string $work_order_number
 * @var string $transaction_date
 * @var bool $print_price_info
 * @var string $total
 * @var array $cart
 * @var float $subtotal
 * @var array $taxes
 * @var array $payments
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
	<script type="text/javascript">
		$(document).ready(function()
		{
			var send_email = function()
			{
				$.get('<?= esc("/sales/send_pdf/$sale_id_num/work_order") ?>',
					function(response)
					{
						$.notify( { message: response.message }, { type: response.success ? 'success' : 'danger'} )
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
	<?php /* this line will allow to print and go back to sales automatically.... echo anchor("sales", '<span class="glyphicon glyphicon-print">&nbsp</span>' . lang('Common.print'), ['class' => 'btn btn-info btn-sm', 'id' => 'show_print_button', 'onclick' => 'window.print();')) */ ?>
	<?php if(isset($customer_email) && !empty($customer_email)): ?>
		<a href="javascript:void(0);"><div class="btn btn-info btn-sm" id="show_email_button"><?= '<span class="glyphicon glyphicon-envelope">&nbsp</span>' . lang('Sales.send_work_order') ?></div></a>
	<?php endif; ?>
	<?= anchor("sales", '<span class="glyphicon glyphicon-shopping-cart">&nbsp</span>' . lang('Sales.register'), ['class' => 'btn btn-info btn-sm', 'id' => 'show_sales_button']) ?>
	<?= anchor("sales/discard_suspended_sale", '<span class="glyphicon glyphicon-remove">&nbsp</span>' . lang('Sales.discard'), ['class' => 'btn btn-danger btn-sm', 'id' => 'discard_work_order_button']) ?>
</div>

<div id="page-wrap">
	<div id="header"><?= $sales_work_order ?></div>
	<div id="block1">
		<div id="customer-title">
			<?php
			if(isset($customer))
			{
			?>
				<div id="customer"><?= esc(nl2br($customer_info)) ?></div>
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
				<div id="company_name"><?= esc($config['company']) ?></div>
			<?php
			}
			?>
		</div>
	</div>

	<div id="block2">
		<div id="company-title"><?= esc(nl2br($company_info)) ?></div>
		<table id="meta">
			<tr>
				<td class="meta-head"><?= esc($work_order_number_label) ?> </td>
				<td><?= esc($work_order_number) ?></td>
			</tr>
			<tr>
				<td class="meta-head"><?= lang('Common.date') ?></td>
				<td><?= esc($transaction_date) ?></td>
			</tr>
			<?php
			if($print_price_info)
			{
			?>
				<tr>
					<td class="meta-head"><?= lang('Sales.amount_due') ?></td>
					<td><?= to_currency(esc($total)) ?></td>
				</tr>
			<?php
			}
			?>
		</table>
	</div>

	<table id="items">
		<tr>
			<th><?= lang('Sales.item_number') ?></th>
			<th><?= lang('Sales.item_name') ?></th>
			<th><?= lang('Sales.quantity') ?></th>
			<th><?= lang('Sales.price') ?></th>
			<th><?= lang('Sales.discount') ?></th>
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
					<td style='text-align:center;'><?= to_quantity_decimals($item['quantity']) ?></td>
					<td><?php if($print_price_info) echo to_currency($item['price']) ?></td>
					<td style='text-align:center;'><?= ($item['discount_type'] == FIXED) ? to_currency($item['discount']) : to_decimals($item['discount']) . '%' ?></td>
					<td style='border-right: solid 1px; text-align:right;'><?php if($print_price_info) echo to_currency($item['discounted_total']) ?></td>
				</tr>

				<?php
				if($item['is_serialized'] || $item['allow_alt_description'] && !empty($item['description']))
				{
				?>
					<tr class="item-row">
						<td></td>
						<td class="item-name" colspan="4"><?= esc($item['description']) ?></td>
						<td style='text-align:center;'><?= esc($item['serialnumber']) ?></td>
					</tr>
				<?php
				}
			}
		}
		?>
		<tr>
			<td class="blank" colspan="6" style="text-align: center;"><?= '&nbsp;' //TODO: Why is PHP needed for an HTML `&nbsp;`? ?></td>
		</tr>
		<?php if($print_price_info) { ?>
			<tr>
				<td colspan="3" class="blank-bottom"> </td>
				<td colspan="2" class="total-line"><?= lang('Sales.sub_total') ?></td>
				<td class="total-value" id="subtotal"><?= to_currency($subtotal) ?></td>
			</tr>
			<?php
			foreach($taxes as $tax_group_index => $tax)
			{
				?>
				<tr>
					<td colspan="3" class="blank"> </td>
					<td colspan="2" class="total-line"><?= (float)$tax['tax_rate'] . '% ' . $tax['tax_group'] ?></td>
					<td class="total-value" id="taxes"><?= to_currency_tax($tax['sale_tax_amount']) ?></td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td colspan="3" class="blank"> </td>
				<td colspan="2" class="total-line"><?= lang('Sales.total') ?></td>
				<td class="total-value" id="total"><?= to_currency($total) ?></td>
			</tr>
		<?php } ?>
		<?php
		$only_sale_check = false;
		$show_giftcard_remainder = false;
		foreach($payments as $payment_id => $payment)
		{
			$only_sale_check |= $payment['payment_type'] == lang('Sales.check');
			$splitpayment = explode(':', $payment['payment_type']);	//TODO: $splitpayment does not match naming conventions for the project
			$show_giftcard_remainder |= $splitpayment[0] == lang('Sales.giftcard');
			?>
			<tr>
				<td colspan="3" class="blank"> </td>
				<td colspan="2" class="total-line"><?= $splitpayment[0] ?></td>
				<td class="total-value" id="paid"><?= to_currency( $payment['payment_amount'] ) ?></td>
			</tr>
			<?php
		}
		?>
	</table>
	<div id="terms">
		<div id="sale_return_policy">
			<h5>
				<span style='padding:4%;'><?= empty($comments) ? '' : lang('Sales.comments') . esc(": $comments") ?></span>
			</h5>
		</div>
	</div>
</div>

<script type="text/javascript">
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
