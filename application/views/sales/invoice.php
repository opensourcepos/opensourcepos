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
		$.get('<?php echo site_url() . "sales/send_pdf/" . $sale_id_num; ?>',
			function(response)
			{
				$.notify(response.message, { type: response.success ? 'success' : 'danger'} );
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
	<div id="header"><?php echo $this->lang->line('sales_invoice'); ?></div>
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
				<td><textarea rows="5" cols="6"><?php echo $invoice_number; ?></textarea></td>
			</tr>
			<tr>
				<td class="meta-head"><?php echo $this->lang->line('common_date'); ?></td>
				<td><textarea rows="5" cols="6"><?php echo $transaction_date; ?></textarea></td>
			</tr>
			<tr>
				<td class="meta-head"><?php echo $this->lang->line('sales_invoice_total'); ?></td>
				<td><textarea rows="5" cols="6"><?php echo to_currency($total); ?></textarea></td>
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
						<td style='text-align:center;'><textarea rows="4" cols="6"><?php echo $item['hsn_code']; ?></textarea>
						</td>
					<?php endif; ?>
					<td class="item-name"><div><?php echo ($item['is_serialized'] || $item['allow_alt_description']) && !empty($item['description']) ? $item['description'] : $item['name'] . ' ' . $item['attribute_values']; ?></div></td>
					<td style='text-align:center;'><textarea rows="5" cols="6"><?php echo to_quantity_decimals($item['quantity']); ?></textarea>
					</td>
					<td><textarea rows="4" cols="6"><?php echo to_currency($item['price']); ?></textarea></td>
					<td style='text-align:center;'><textarea rows="4" cols="6"><?php echo ($item['discount_type']==FIXED)?to_currency($item['discount']):$item['discount'] . '%';?></textarea>
					</td>
					<?php if($discount > 0): ?>
						<td style='text-align:center;'><textarea rows="4" cols="6"><?php echo to_currency($item['discounted_total'] / $item['quantity']); ?></textarea>
						</td>
					<?php endif; ?>
					<td style='border-right: solid 1px; text-align:right;'><textarea rows="4" cols="6"><?php echo to_currency($item['discounted_total']); ?></textarea>
					</td>
				</tr>
				<?php
				if($item['is_serialized'])
				{
				?>
					<tr class="item-row">
						<td class="item-description" colspan="<?php echo $invoice_columns-1; ?>"></td>
						<td style='text-align:center;'><textarea><?php echo $item['serialnumber']; ?></textarea></td>
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
			<td colspan="2" class="total-line"><textarea rows="5" cols="6"><?php echo $this->lang->line('sales_sub_total'); ?></textarea></td>
			<td class="total-value"><textarea rows="5" cols="6" id="subtotal"><?php echo to_currency($subtotal); ?></textarea></td>
		</tr>

		<?php
		foreach($taxes as $tax_group_index=>$tax)
		{
		?>
			<tr>
				<td colspan="<?php echo $invoice_columns-3; ?>" class="blank"> </td>
				<td colspan="2" class="total-line"><textarea rows="5" cols="6"><?php echo (float)$tax['tax_rate'] . '% ' . $tax['tax_group']; ?></textarea></td>
				<td class="total-value"><textarea rows="5" cols="6" id="taxes"><?php echo to_currency_tax($tax['sale_tax_amount']); ?></textarea></td>
			</tr>
		<?php
		}
		?>

		<tr>
			<td colspan="<?php echo $invoice_columns-3; ?>" class="blank"> </td>
			<td colspan="2" class="total-line"><textarea rows="5" cols="6"><?php echo $this->lang->line('sales_total'); ?></textarea></td>
			<td class="total-value"><textarea rows="5" cols="6" id="total"><?php echo to_currency($total); ?></textarea></td>
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
				<td colspan="2" class="total-line"><textarea rows="5" cols="6"><?php echo $splitpayment[0]; ?></textarea></td>
				<td class="total-value"><textarea rows="5" cols="6" id="paid"><?php echo to_currency( $payment['payment_amount'] * -1 ); ?></textarea></td>
			</tr>
		<?php
		}

		if(isset($cur_giftcard_value) && $show_giftcard_remainder)
		{
		?>
			<tr>
				<td colspan="<?php echo $invoice_columns-3; ?>" class="blank"> </td>
				<td colspan="2" class="total-line"><textarea rows="5" cols="6"><?php echo $this->lang->line('sales_giftcard_balance'); ?></textarea></td>
				<td class="total-value"><textarea rows="5" cols="6" id="giftcard"><?php echo to_currency($cur_giftcard_value); ?></textarea></td>
			</tr>
			<?php
		}

		if(!empty($payments))
		{
		?>
		<tr>
			<td colspan="<?php echo $invoice_columns-3; ?>" class="blank"> </td>
			<td colspan="2" class="total-line"> <textarea rows="5" cols="6"><?php echo $this->lang->line($amount_change >= 0 ? ($only_sale_check ? 'sales_check_balance' : 'sales_change_due') : 'sales_amount_due') ; ?></textarea></td>
			<td class="total-value"><textarea rows="5" cols="6" id="change"><?php echo to_currency($amount_change); ?></textarea></td>
		</tr>
		<?php
		}
		?>

	</table>

	<div id="terms">
		<div id="sale_return_policy">
			<h5>
				<textarea rows="5" cols="6"><?php echo nl2br($this->config->item('payment_message')); ?></textarea>
				<textarea rows="5" cols="6"><?php echo empty($comments) ? '' : $this->lang->line('sales_comments') . ': ' . $comments; ?></textarea>
				<textarea rows="5" cols="6"><?php echo $this->config->item('invoice_default_comments'); ?></textarea>
			</h5>
			<?php echo nl2br($this->config->item('return_policy')); ?>
		</div>
		<div id='barcode'>
			<img src='data:image/png;base64,<?php echo $barcode; ?>' /><br>
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
