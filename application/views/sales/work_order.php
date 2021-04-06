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
				$.get('<?php echo site_url() . "/sales/send_pdf/" . $sale_id_num . "/work_order"; ?>',
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
		<a href="javascript:void(0);"><div class="btn btn-info btn-sm", id="show_email_button"><?php echo '<span class="glyphicon glyphicon-envelope">&nbsp</span>' . $this->lang->line('sales_send_work_order'); ?></div></a>
	<?php endif; ?>
	<?php echo anchor("sales", '<span class="glyphicon glyphicon-shopping-cart">&nbsp</span>' . $this->lang->line('sales_register'), array('class'=>'btn btn-info btn-sm', 'id'=>'show_sales_button')); ?>
	<?php echo anchor("sales/discard_suspended_sale", '<span class="glyphicon glyphicon-remove">&nbsp</span>' . $this->lang->line('sales_discard'), array('class'=>'btn btn-danger btn-sm', 'id'=>'discard_work_order_button')); ?>
</div>

<div id="page-wrap">
	<div id="header"><?php echo $sales_work_order; ?></div>
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
				<td class="meta-head"><?php echo $work_order_number_label;?> </td>
				<td><?php echo $work_order_number; ?></td>
			</tr>
			<tr>
				<td class="meta-head"><?php echo $this->lang->line('common_date'); ?></td>
				<td><?php echo $transaction_date; ?></td>
			</tr>
			<?php
			if($print_price_info)
			{
			?>
				<tr>
					<td class="meta-head"><?php echo $this->lang->line('sales_amount_due'); ?></td>
					<td><?php echo to_currency($total); ?></td>
				</tr>
			<?php
			}
			?>
		</table>
	</div>

	<table id="items">
		<tr>
			<th><?php echo $this->lang->line('sales_item_number'); ?></th>
			<th><?php echo $this->lang->line('sales_item_name'); ?></th>
			<th><?php echo $this->lang->line('sales_quantity'); ?></th>
			<th><?php echo $this->lang->line('sales_price'); ?></th>
			<th><?php echo $this->lang->line('sales_discount'); ?></th>
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
					<td class="item-name"><?php echo $item['name']; ?></td>
					<td style='text-align:center;'><?php echo to_quantity_decimals($item['quantity']); ?></td>
					<td><?php if($print_price_info) echo to_currency($item['price']); ?></td>
					<td style='text-align:center;'><?php echo ($item['discount_type']==FIXED)?to_currency($item['discount']):to_decimals($item['discount']) . '%';?></td>
					<td style='border-right: solid 1px; text-align:right;'><?php if($print_price_info) echo to_currency($item['discounted_total']); ?></td>
				</tr>

				<?php
				if($item['is_serialized'] || $item['allow_alt_description'] && !empty($item['description']))
				{
				?>
					<tr class="item-row">
						<td></td>
						<td class="item-name" colspan="4"><?php echo $item['description']; ?></td>
						<td style='text-align:center;'><?php echo $item['serialnumber']; ?></td>
					</tr>
				<?php
				}
			}
		}
		?>
		<tr>
			<td class="blank" colspan="6" align="center"><?php echo '&nbsp;'; ?></td>
		</tr>
		<?php if($print_price_info) { ?>
			<tr>
				<td colspan="3" class="blank-bottom"> </td>
				<td colspan="2" class="total-line"><?php echo $this->lang->line('sales_sub_total'); ?></td>
				<td class="total-value" id="subtotal"><?php echo to_currency($subtotal); ?></td>
			</tr>
			<?php
			foreach($taxes as $tax_group_index=>$tax)
			{
				?>
				<tr>
					<td colspan="3" class="blank"> </td>
					<td colspan="2" class="total-line"><?php echo (float)$tax['tax_rate'] . '% ' . $tax['tax_group']; ?></td>
					<td class="total-value" id="taxes"><?php echo to_currency_tax($tax['sale_tax_amount']); ?></td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td colspan="3" class="blank"> </td>
				<td colspan="2" class="total-line"><?php echo $this->lang->line('sales_total'); ?></td>
				<td class="total-value" id="total"><?php echo to_currency($total); ?></td>
			</tr>
		<?php } ?>
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
				<td colspan="3" class="blank"> </td>
				<td colspan="2" class="total-line"><?php echo $splitpayment[0]; ?></td>
				<td class="total-value" id="paid"><?php echo to_currency( $payment['payment_amount'] ); ?></td>
			</tr>
			<?php
		}
		?>
	</table>
	<div id="terms">
		<div id="sale_return_policy">
			<h5>
				<div style='padding:4%;'><?php echo empty($comments) ? '' : $this->lang->line('sales_comments') . ': ' . $comments; ?></div>
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
