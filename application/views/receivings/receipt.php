<?php $this->load->view("partial/header"); ?>

<?php
if (isset($error_message)) {
	echo "<div class='alert alert-dismissible alert-danger'>" . $error_message . "</div>";
	exit;
}

$this->load->view('partial/print_receipt', array('print_after_sale', $print_after_sale, 'selected_printer' => 'receipt_printer'));
?>

<div class="print_hide" id="control_buttons" style="text-align:right">
	<a href="javascript:printdoc();">
		<div class="btn btn-primary" , id="show_print_button"><?= '<i class="bi bi-printer pe-1"></i>' . $this->lang->line('common_print'); ?></div>
	</a>
	<?= anchor("receivings", '<i class="bi bi-save pe-1"></i>' . $this->lang->line('receivings_register'), array('class' => 'btn btn-primary', 'id' => 'show_sales_button')); ?>
</div>

<div id="receipt_wrapper">
	<div id="receipt_header">
		<?php
		if ($this->config->item('company_logo') != '') {
		?>
			<div id="company_name"><img id="image" src="<?= base_url('uploads/' . $this->config->item('company_logo')); ?>" alt="company_logo" /></div>
		<?php
		}
		?>

		<?php
		if ($this->config->item('receipt_show_company_name')) {
		?>
			<div id="company_name"><?= $this->config->item('company'); ?></div>
		<?php
		}
		?>

		<div id="company_address"><?= nl2br($this->config->item('address')); ?></div>
		<div id="company_phone"><?= $this->config->item('phone'); ?></div>
		<div id="sale_receipt"><?= $this->lang->line('receivings_receipt'); ?></div>
		<div id="sale_time"><?= $transaction_time ?></div>
	</div>

	<div id="receipt_general_info">
		<?php
		if (isset($supplier)) {
		?>
			<div id="customer"><?= $this->lang->line('suppliers_supplier') . ": " . $supplier; ?></div>
		<?php
		}
		?>
		<div id="sale_id"><?= $this->lang->line('receivings_id') . ": " . $receiving_id; ?></div>
		<?php
		if (!empty($reference)) {
		?>
			<div id="reference"><?= $this->lang->line('receivings_reference') . ": " . $reference; ?></div>
		<?php
		}
		?>
		<div id="employee"><?= $this->lang->line('employees_employee') . ": " . $employee; ?></div>
	</div>

	<table id="receipt_items">
		<tr>
			<th style="width:40%;"><?= $this->lang->line('items_item'); ?></th>
			<th style="width:20%;"><?= $this->lang->line('common_price'); ?></th>
			<th style="width:20%;"><?= $this->lang->line('sales_quantity'); ?></th>
			<th style="width:15%;text-align:right;"><?= $this->lang->line('sales_total'); ?></th>
		</tr>

		<?php
		foreach (array_reverse($cart, TRUE) as $line => $item) {
		?>
			<tr>
				<td><?= $item['name'] . ' ' . $item['attribute_values']; ?></td>
				<td><?= to_currency($item['price']); ?></td>
				<td><?= to_quantity_decimals($item['quantity']) . " " . ($show_stock_locations ? " [" . $item['stock_name'] . "]" : "");
					?>&nbsp;&nbsp;&nbsp;x <?= $item['receiving_quantity'] != 0 ? to_quantity_decimals($item['receiving_quantity']) : 1; ?></td>
				<td>
					<div class="total-value"><?= to_currency($item['total']); ?></div>
				</td>
			</tr>
			<tr>
				<td><?= $item['serialnumber']; ?></td>
			</tr>
			<?php
			if ($item['discount'] > 0) {
			?>
				<tr>
					<?php
					if ($item['discount_type'] == FIXED) {
					?>
						<td colspan="3" class="discount"><?= to_currency($item['discount']) . " " . $this->lang->line("sales_discount") ?></td>
					<?php
					} elseif ($item['discount_type'] == PERCENT) {
					?>
						<td colspan="3" class="discount"><?= to_decimals($item['discount']) . " " . $this->lang->line("sales_discount_included") ?></td>
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
			<td colspan="3" style='text-align:right;border-top:2px solid #000000;'><?= $this->lang->line('sales_total'); ?></td>
			<td style='border-top:2px solid #000000;'>
				<div class="total-value"><?= to_currency($total); ?></div>
			</td>
		</tr>
		<?php
		if ($mode != 'requisition') {
		?>
			<tr>
				<td colspan="3" style='text-align:right;'><?= $this->lang->line('sales_payment'); ?></td>
				<td>
					<div class="total-value"><?= $payment_type; ?></div>
				</td>
			</tr>

			<?php if (isset($amount_change)) {
			?>
				<tr>
					<td colspan="3" style='text-align:right;'><?= $this->lang->line('sales_amount_tendered'); ?></td>
					<td>
						<div class="total-value"><?= to_currency($amount_tendered); ?></div>
					</td>
				</tr>

				<tr>
					<td colspan="3" style='text-align:right;'><?= $this->lang->line('sales_change_due'); ?></td>
					<td>
						<div class="total-value"><?= $amount_change; ?></div>
					</td>
				</tr>
			<?php
			}
			?>
		<?php
		}
		?>
	</table>

	<div id="sale_return_policy">
		<?= nl2br($this->config->item('return_policy')); ?>
	</div>

	<div id='barcode'>
		<img src='data:image/png;base64,<?= $barcode; ?>' /><br>
		<?= $receiving_id; ?>
	</div>
</div>
<?php $this->load->view("partial/footer"); ?>