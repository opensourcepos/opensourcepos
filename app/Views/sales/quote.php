<?php
/**
 * @var int $sale_id_num
 * @var bool $print_after_sale
 * @var string $customer_info
 * @var string $company_info
 * @var string $quote_number
 * @var string $transaction_date
 * @var float $total
 * @var float $discount
 * @var array $cart
 * @var float $subtotal
 * @var array $taxes
 * @var array $payments
 */
?>
<?php echo view('partial/header') ?>

<?php
if (isset($error_message))
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
				$.get('<?php echo site_url() . esc("/sales/send_pdf/$sale_id_num/quote") ?>',
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

<?php echo view('partial/print_receipt', ['print_after_sale' => $print_after_sale, 'selected_printer' => 'invoice_printer']) ?>

<div class="print_hide" id="control_buttons" style="text-align:right">
	<a href="javascript:printdoc();"><div class="btn btn-info btn-sm" id="show_print_button"><?php echo '<span class="glyphicon glyphicon-print">&nbsp</span>' . lang('Common.print') ?></div></a>
	<?php /* this line will allow to print and go back to sales automatically.... echo anchor('sales', '<span class=\'glyphicon glyphicon-print\'>&nbsp</span>' . lang('Common.print'), ['class' => 'btn btn-info btn-sm', 'id' => 'show_print_button', 'onclick' => 'window.print();']); */ ?>
	<?php if(isset($customer_email) && !empty($customer_email)): ?>
		<a href="javascript:void(0);"><div class="btn btn-info btn-sm" id="show_email_button"><?php echo '<span class="glyphicon glyphicon-envelope">&nbsp</span>' . lang('Sales.send_quote') ?></div></a>
	<?php endif; ?>
	<?php echo anchor('sales', '<span class=\'glyphicon glyphicon-shopping-cart\'>&nbsp</span>' . lang('Sales.register'), ['class' => 'btn btn-info btn-sm', 'id' => 'show_sales_button']) ?>
	<?php echo anchor('sales/discard_suspended_sale', '<span class="glyphicon glyphicon-remove">&nbsp</span>' . lang('Sales.discard'), ['class' => 'btn btn-danger btn-sm', 'id' => 'discard_quote_button']) ?>
</div>

<div id="page-wrap">
	<div id="header"><?php echo lang('Sales.quote') ?></div>
	<div id="block1">
		<div id="customer-title">
			<?php
			if(isset($customer))
			{
			?>
				<div id="customer"><?php echo nl2br(esc($customer_info)) ?></div>
			<?php
			}
			?>
		</div>

		<div id="logo">
			<?php
			if(config('OSPOS')->settings['company_logo'] != '')
			{
			?>
				<img id="image" src="<?php echo base_url('uploads/' . esc(config('OSPOS')->settings['company_logo'], 'url')) ?>" alt="company_logo" />
			<?php
			}
			?>
			<div>&nbsp</div>
			<?php
			if(config('OSPOS')->settings['receipt_show_company_name'])
			{
			?>
				<div id="company_name"><?php echo esc(config('OSPOS')->settings['company']) ?></div>
			<?php
			}
			?>
		</div>
	</div>

	<div id="block2">
		<div id="company-title"><?php echo nl2br(esc($company_info)) ?></div>
		<table id="meta">
			<tr>
				<td class="meta-head"><?php echo lang('Sales.quote_number') ?></td>
				<td><?php echo esc($quote_number) ?></td>
			</tr>
			<tr>
				<td class="meta-head"><?php echo lang('Common.date') ?></td>
				<td><?php echo esc($transaction_date) ?></td>
			</tr>
			<tr>
				<td class="meta-head"><?php echo lang('Sales.invoice_total') ?></td>
				<td><?php echo to_currency($total) ?></td>
			</tr>
		</table>
	</div>

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
					<td style='text-align:center;'><?php echo to_quantity_decimals($item['quantity']) ?></td>
					<td><?php echo to_currency($item['price']) ?></td>
					<td style='text-align:center;'><?php echo ($item['discount_type'] == FIXED) ? to_currency($item['discount']) : to_decimals($item['discount']) . '%' ?></td>
					<?php if($discount > 0): ?>
						<td style='text-align:center;'><?php echo to_currency($item['discounted_total'] / $item['quantity']) ?></td>
					<?php endif; ?>
					<td style='border-right: solid 1px; text-align:right;'><?php echo to_currency($item['discounted_total']) ?></td>
				</tr>

				<?php if($item['is_serialized'])
				{
				?>
					<tr class="item-row">
						<td class="item-name" colspan="<?php echo $quote_columns-1 ?>"></td>
						<td style='text-align:center;'><?php echo esc($item['serialnumber']) //TODO: the variable serialnumber does not meet naming conventions for this project?></td>
					</tr>
				<?php
				}
			}
		}
		?>

		<tr>
			<td class="blank" colspan="<?php echo $quote_columns ?>" style="text-align: center;"><?php echo '&nbsp;' //TODO: align is deprecated.  Also should replace the php echo for nbsp with simple html?></td>
		</tr>

		<tr>
			<td colspan="<?php echo $quote_columns-3 ?>" class="blank-bottom"> </td>
			<td colspan="2" class="total-line"><?php echo lang('Sales.sub_total') ?></td>
			<td class="total-value" id="subtotal"><?php echo to_currency($subtotal) ?></td>
		</tr>

		<?php
		foreach($taxes as $tax_group_index => $tax)
		{
		?>
			<tr>
				<td colspan="<?php echo $quote_columns-3 ?>" class="blank"> </td>
				<td colspan="2" class="total-line"><?php echo (float)$tax['tax_rate'] . '% ' . $tax['tax_group'] ?></td>
				<td class="total-value" id="taxes"><?php echo to_currency_tax($tax['sale_tax_amount']) ?></td>
			</tr>
		<?php
		}
		?>

		<tr>
			<td colspan="<?php echo $quote_columns-3 ?>" class="blank"> </td>
			<td colspan="2" class="total-line"><?php echo lang('Sales.total') ?></td>
			<td class="total-value" id="total"><?php echo to_currency($total) ?></td>
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
				<td colspan="<?php echo $quote_columns-3 ?>" class="blank"> </td>
				<td colspan="2" class="total-line"><?php echo $splitpayment[0] ?></td>
				<td class="total-value" id="paid"><?php echo to_currency($payment['payment_amount']) ?></td>
			</tr>
		<?php
		}
		?>
	</table>
	<div id="terms">
		<div id="sale_return_policy">
			<h5>
				<span style='padding:4%;'><?php echo empty($comments) ? '' : lang('Sales.comments') . ': ' . esc($comments) ?></span>
				<span style='padding:4%;'><?php echo esc(config('OSPOS')->settings['quote_default_comments']) ?></span>
			</h5>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(window).on("load", function()
	{
		// install firefox addon in order to use this plugin
		if (window.jsPrintSetup)
		{
			<?php
			if(!config('OSPOS')->settings['print_header'])
			{
			?>
				// set page header
				jsPrintSetup.setOption('headerStrLeft', '');
				jsPrintSetup.setOption('headerStrCenter', '');
				jsPrintSetup.setOption('headerStrRight', '');
			<?php
			}

			if(!config('OSPOS')->settings['print_footer'])
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

<?php echo view('partial/footer') ?>
