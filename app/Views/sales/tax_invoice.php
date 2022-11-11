<?php $this->load->view("partial/header"); ?>

<?php
if(isset($error_message))
{
	echo "<div class='alert alert-dismissible alert-danger'>".$error_message."</div>";
	exit;
}
?>

<?php if(!empty($customer_email)): ?>
<script type="text/javascript">
$(document).ready(function()
{
	var send_email = function()
	{
		$.get('<?php echo site_url() . "/sales/send_pdf/" . $sale_id_num; ?>',
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

<?php $this->load->view('partial/print_receipt', array('print_after_sale'=>$print_after_sale, 'selected_printer'=>'invoice_printer')); ?>

<div class="print_hide" id="control_buttons" style="text-align:right">
	<a href="javascript:printdoc();"><div class="btn btn-info btn-sm", id="show_print_button"><?php echo '<span class="glyphicon glyphicon-print">&nbsp</span>' . $this->lang->line('common_print'); ?></div></a>
	<?php /* this line will allow to print and go back to sales automatically.... echo anchor("sales", '<span class="glyphicon glyphicon-print">&nbsp</span>' . $this->lang->line('common_print'), array('class'=>'btn btn-info btn-sm', 'id'=>'show_print_button', 'onclick'=>'window.print();')); */ ?>
	<?php if(isset($customer_email) && !empty($customer_email)): ?>
		<a href="javascript:void(0);"><div class="btn btn-info btn-sm", id="show_email_button"><?php echo '<span class="glyphicon glyphicon-envelope">&nbsp</span>' . $this->lang->line('sales_send_invoice'); ?></div></a>
	<?php endif; ?>
	<?php echo anchor("sales", '<span class="glyphicon glyphicon-shopping-cart">&nbsp</span>' . $this->lang->line('sales_register'), array('class'=>'btn btn-info btn-sm', 'id'=>'show_sales_button')); ?>
	<?php echo anchor("sales/manage", '<span class="glyphicon glyphicon-list-alt">&nbsp</span>' . $this->lang->line('sales_takings'), array('class'=>'btn btn-info btn-sm', 'id'=>'show_takings_button')); ?>
</div>

<div id="page-wrap">
	<div id="header"><?php echo $this->lang->line('sales_tax_invoice'); ?></div>
	<div id="block1">
		<div id="customer-title">
			<?php
			if(isset($customer))
			{
			?>
				<div id="customer"><?php echo nl2br($customer_info) ?></div>
			<?php
			}
			?>
		</div>

		<div id="logo">
			<?php
			if($this->Appconfig->get('company_logo') != '')
			{
			?>
				<img id="image" src="<?php echo base_url('uploads/' . $this->Appconfig->get('company_logo')); ?>" alt="company_logo" />
			<?php
			}
			?>
			<div>&nbsp</div>
			<?php
			if($this->Appconfig->get('receipt_show_company_name'))
			{
			?>
				<div id="company_name"><?php echo $this->config->item('company'); ?></div>
			<?php
			}
			?>
		</div>
	</div>

	<div id="block2">
		<div id="company-title"><?php echo nl2br($company_info) ?></div>
		<table id="meta">
			<tr>
				<td class="meta-head"><?php echo $this->lang->line('sales_invoice_number');?> </td>
				<td><?php echo $invoice_number; ?></td>
			</tr>
			<tr>
				<td class="meta-head"><?php echo $this->lang->line('common_date'); ?></td>
				<td><?php echo $transaction_date; ?></td>
			</tr>
			<tr>
				<td class="meta-head"><?php echo $this->lang->line('sales_amount_due'); ?></td>
				<td><?php echo to_currency($total); ?></td>
			</tr>
		</table>
	</div>

	<table id="items">
		<tr>
			<th><?php echo $this->lang->line('sales_item_number'); ?></th>
			<?php
				$invoice_columns = 6;
				if($include_hsn)
				{
					$invoice_columns += 1;
					?>
					<th><?php echo $this->lang->line('sales_hsn'); ?></th>
					<?php
				}
			?>
			<th><?php echo $this->lang->line('sales_item_name'); ?></th>
			<th><?php echo $this->lang->line('sales_quantity'); ?></th>
			<th><?php echo $this->lang->line('sales_price'); ?></th>
			<th><?php echo $this->lang->line('sales_discount'); ?></th>
			<?php
			if($discount > 0)
			{
				$invoice_columns += 1;
				?>
				<th><?php echo $this->lang->line('sales_customer_discount'); ?></th>
			<?php
			}
			?>
			<th><?php echo $this->lang->line('sales_total'); ?></th>
		</tr>

		<?php
		foreach($cart as $line=>$item)
		{
			if($item['print_option'] == PRINT_YES)
			{
			?>
				<tr class="item-row">
					<td><?php echo $item['item_number']; ?></td>
					<?php if($include_hsn): ?>
						<td style='text-align:center;'><?php echo $item['hsn_code']; ?></td>
					<?php endif; ?>
					<td class="item-name"><?php echo $item['name']; ?></td>
					<td style='text-align:center;'><?php echo to_quantity_decimals($item['quantity']); ?></td>
					<td><?php echo to_currency($item['price']); ?></td>
					<td style='text-align:center;'><?php echo ($item['discount_type']==FIXED)?to_currency($item['discount']):to_decimals($item['discount']) . '%';?></td>
					<?php if($discount > 0): ?>
						<td style='text-align:center;'><?php echo to_currency($item['discounted_total'] / $item['quantity']); ?></td>
					<?php endif; ?>
					<td style='border-right: solid 1px; text-align:right;'><?php echo to_currency($item['discounted_total']); ?></td>
				</tr>
				<?php
				if($item['is_serialized'] || $item['allow_alt_description'] && !empty($item['description']))
				{
				?>
					<tr class="item-row">
						<td><?php echo $item['hsn_code']; ?></td>
						<td class="item-description" colspan="<?php echo $invoice_columns-2; ?>">
							<?php echo $item['description']; ?>
						</td>
						<td style='text-align:center;'><?php echo $item['serialnumber']; ?></td>
					</tr>
				<?php
				}
			}
		}
		?>

		<tr>
			<td class="blank" colspan="<?php echo $invoice_columns; ?>" align="center"><?php echo '&nbsp;'; ?></td>
		</tr>

		<tr>
			<td colspan="<?php echo $invoice_columns-3; ?>" class="blank-bottom"> </td>
			<td colspan="2" class="total-line"><?php echo $this->lang->line('sales_sub_total'); ?></td>
			<td class="total-value" id="subtotal"><?php echo to_currency($subtotal); ?></td>
		</tr>

		<?php
		foreach($taxes as $tax_group_index=>$tax)
		{
		?>
			<tr>
				<td colspan="<?php echo $invoice_columns-3; ?>" class="blank"> </td>
				<td colspan="2" class="total-line"><?php echo (float)$tax['tax_rate'] . '% ' . $tax['tax_group']; ?></td>
				<td class="total-value" id="taxes"><?php echo to_currency_tax($tax['sale_tax_amount']); ?></td>
			</tr>
		<?php
		}
		?>

		<tr>
			<td colspan="<?php echo $invoice_columns-3; ?>" class="blank"> </td>
			<td colspan="2" class="total-line"><?php echo $this->lang->line('sales_total'); ?></td>
			<td class="total-value" id="total"><?php echo to_currency($total); ?></td>
		</tr>

		<?php
		$only_sale_check = FALSE;
		$show_giftcard_remainder = FALSE;
		foreach($payments as $payment_id=>$payment)
		{
			$only_sale_check |= $payment['payment_type'] == $this->lang->line('sales_check');
			$splitpayment = explode(':', $payment['payment_type']);
			$show_giftcard_remainder |= $splitpayment[0] == $this->lang->line('sales_giftcard');
		?>
			<tr>
				<td colspan="<?php echo $invoice_columns-3; ?>" class="blank"> </td>
				<td colspan="2" class="total-line"><?php echo $splitpayment[0]; ?></td>
				<td class="total-value" id="paid"><?php echo to_currency( $payment['payment_amount'] * -1 ); ?></td>
			</tr>
		<?php
		}

		if(isset($cur_giftcard_value) && $show_giftcard_remainder)
		{
		?>
			<tr>
				<td colspan="<?php echo $invoice_columns-3; ?>" class="blank"> </td>
				<td colspan="2" class="total-line"><?php echo $this->lang->line('sales_giftcard_balance'); ?>/td>
				<td class="total-value" id="giftcard"><?php echo to_currency($cur_giftcard_value); ?></td>
			</tr>
			<?php
		}

		if(!empty($payments))
		{
		?>
		<tr>
			<td colspan="<?php echo $invoice_columns-3; ?>" ><?php echo $this->lang->line('sales_authorized_signature');?>:</td>
			<td colspan="2" class="total-line"><?php echo $this->lang->line($amount_change >= 0 ? ($only_sale_check ? 'sales_check_balance' : 'sales_change_due') : 'sales_amount_due') ; ?></td>
			<td class="total-value" id="change"><?php echo to_currency($amount_change); ?></td>
		</tr>
		<?php
		}
		?>
	</table>

	<div id="terms">
		<div id="sale_return_policy">
			<h5>
				<div><?php echo nl2br($this->config->item('payment_message')); ?></div>
				<div style='padding:4%;'><?php echo empty($comments) ? '' : $this->lang->line('sales_comments') . ': ' . $comments; ?></div>
				<div style='padding:4%;'><?php echo $this->config->item('invoice_default_comments'); ?></div>
			</h5>
			<div style='padding:2%;'><?php echo nl2br($this->config->item('return_policy')); ?></div>
		</div>
		<div id='barcode'>
			<img style='padding-top:4%;' src='data:image/png;base64,<?php echo $barcode; ?>' /><br>
			<?php echo $sale_id; ?>
		</div>
	</div>
</div>

<script type="text/javascript">
$(window).on("load", function()
{
	// install firefox addon in order to use this plugin
	if(window.jsPrintSetup)
	{
		<?php if(!$this->Appconfig->get('print_header'))
		{
		?>
			// set page header
			jsPrintSetup.setOption('headerStrLeft', '');
			jsPrintSetup.setOption('headerStrCenter', '');
			jsPrintSetup.setOption('headerStrRight', '');
		<?php
		}

		if(!$this->Appconfig->get('print_footer'))
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

<?php $this->load->view("partial/footer"); ?>
