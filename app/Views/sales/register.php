<?php
/**
 * @var string $controller_name
 * @var array $modes
 * @var array $mode
 * @var array $empty_tables
 * @var array $selected_table
 * @var array $stock_locations
 * @var array $stock_location
 * @var array $cart
 * @var bool $items_module_allowed
 * @var bool $change_price
 * @var int $customer_id
 * @var int $customer_discount_type
 * @var float $customer_discount
 * @var float $customer_total
 * @var string $customer_required
 * @var float|int $item_count
 * @var float|int $total_units
 * @var float $subtotal
 * @var array $taxes
 * @var float $total
 * @var float $payments_total
 * @var float $amount_due
 * @var bool $payments_cover_total
 * @var array $payment_options
 * @var array $selected_payment_type
 * @var bool $pos_mode
 * @var array $payments
 * @var string $mode_label
 * @var string $comment
 * @var bool $print_after_sale
 * @var bool $email_receipt
 * @var bool $price_work_orders
 * @var string $invoice_number
 * @var int $cash_mode
 * @var float $non_cash_total
 * @var float $cash_amount_due
 */

use App\Models\Employee;

?>
<?php echo view('partial/header') ?>

<?php
if(isset($error))
{
	echo "<div class='alert alert-dismissible alert-danger'>$error</div>";
}

if(!empty($warning))
{
	echo "<div class='alert alert-dismissible alert-warning'>$warning</div>";
}

if(isset($success))
{
	echo "<div class='alert alert-dismissible alert-success'>$success</div>";
}
?>

<div id="register_wrapper">

<!-- Top register controls -->
	<?php echo form_open(esc("$controller_name/change_mode"), ['id' => 'mode_form', 'class' => 'form-horizontal panel panel-default']) ?>
		<div class="panel-body form-group">
			<ul>
				<li class="pull-left first_li">
					<label class="control-label"><?php echo lang('Sales.mode') ?></label>
				</li>
				<li class="pull-left">
					<?php echo form_dropdown('mode', $modes, $mode, ['onchange' => "$('#mode_form').submit();", 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit']) ?>
				</li>
				<?php
				if($config['dinner_table_enable'] == TRUE)
				{
				?>
					<li class="pull-left first_li">
						<label class="control-label"><?php echo lang('Sales.table') ?></label>
					</li>
					<li class="pull-left">
						<?php echo form_dropdown('dinner_table', $empty_tables, $selected_table, ['onchange'=>"$('#mode_form').submit();", 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit']) ?>
					</li>
				<?php
				}
				if(count($stock_locations) > 1)
				{
				?>
					<li class="pull-left">
						<label class="control-label"><?php echo lang('Sales.stock_location') ?></label>
					</li>
					<li class="pull-left">
						<?php echo form_dropdown('stock_location', $stock_locations, $stock_location, ['onchange' => "$('#mode_form').submit();", 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit']) ?>
					</li>
				<?php
				}
				?>

				<li class="pull-right">
					<button class='btn btn-default btn-sm modal-dlg' id='show_suspended_sales_button' data-href="<?php echo esc("$controller_name/suspended") ?>"
							title="<?php echo lang('Sales.suspended_sales') ?>">
						<span class="glyphicon glyphicon-align-justify">&nbsp</span><?php echo lang('Sales.suspended_sales') ?>
					</button>
				</li>

				<?php
				$employee = model(Employee::class);
				if($employee->has_grant('reports_sales', session('person_id')))
				{
				?>
					<li class="pull-right">
						<?php echo anchor(esc("$controller_name/manage"), '<span class="glyphicon glyphicon-list-alt">&nbsp</span>' . lang('Sales.takings'),
									array('class' => 'btn btn-primary btn-sm', 'id' => 'sales_takings_button', 'title' => lang('Sales.takings'))) ?>
					</li>
				<?php
				}
				?>
			</ul>
		</div>
	<?php echo form_close() ?>

	<?php $tabindex = 0; ?>

	<?php echo form_open(esc("$controller_name/add"), ['id' => 'add_item_form', 'class' => 'form-horizontal panel panel-default']) ?>
		<div class="panel-body form-group">
			<ul>
				<li class="pull-left first_li">
					<label for="item" class='control-label'><?php echo lang('Sales.find_or_scan_item_or_receipt') ?></label>
				</li>
				<li class="pull-left">
					<?php echo form_input (['name' => 'item', 'id' => 'item', 'class' => 'form-control input-sm', 'size' => '50', 'tabindex' => ++$tabindex]) ?>
					<span class="ui-helper-hidden-accessible" role="status"></span>
				</li>
				<li class="pull-right">
					<button id='new_item_button' class='btn btn-info btn-sm pull-right modal-dlg' data-btn-new="<?php echo lang('Common.new') ?>" data-btn-submit="<?php echo lang('Common.submit') ?>" data-href="<?php echo esc(site_url("items/view"), 'url') ?>"
							title="<?php echo lang("$controller_name.new_item") ?>">
						<span class="glyphicon glyphicon-tag">&nbsp</span><?php echo lang("$controller_name.new_item") ?>
					</button>
				</li>
			</ul>
		</div>
	<?php echo form_close() ?>


<!-- Sale Items List -->

	<table class="sales_table_100" id="register">
		<thead>
			<tr>
				<th style="width: 5%; "><?php echo lang('Common.delete') ?></th>
				<th style="width: 15%;"><?php echo lang('Sales.item_number') ?></th>
				<th style="width: 30%;"><?php echo lang('Sales.item_name') ?></th>
				<th style="width: 10%;"><?php echo lang('Sales.price') ?></th>
				<th style="width: 10%;"><?php echo lang('Sales.quantity') ?></th>
				<th style="width: 15%;"><?php echo lang('Sales.discount') ?></th>
				<th style="width: 10%;"><?php echo lang('Sales.total') ?></th>
				<th style="width: 5%; "><?php echo lang('Sales.update') ?></th>
			</tr>
		</thead>

		<tbody id="cart_contents">
			<?php
			if(count($cart) == 0)
			{
			?>
				<tr>
					<td colspan='8'>
						<div class='alert alert-dismissible alert-info'><?php echo lang('Sales.no_items_in_cart') ?></div>
					</td>
				</tr>
			<?php
			}
			else
			{
				foreach(array_reverse($cart, TRUE) as $line => $item)
				{
			?>
					<?php echo form_open(esc("$controller_name/editItem/$line"), ['class' => 'form-horizontal', 'id' => "cart_$line"]) ?>
						<tr>
							<td>
								<?php
									echo anchor(esc("$controller_name/deleteItem/$line"), '<span class="glyphicon glyphicon-trash"></span>');
									echo form_hidden('location', $item['item_location']);
									echo form_input (['type' => 'hidden', 'name' => 'item_id', 'value'=>$item['item_id']]);
								?>
							</td>
							<?php
							if($item['item_type'] == ITEM_TEMP)
							{
							?>
								<td><?php echo form_input (['name' => 'item_number', 'id' => 'item_number','class' => 'form-control input-sm', 'value'=>$item['item_number'], 'tabindex'=>++$tabindex]) ?></td>
								<td style="align: center;">
									<?php echo form_input (['name' => 'name','id' => 'name', 'class' => 'form-control input-sm', 'value'=>$item['name'], 'tabindex'=>++$tabindex]) ?>
								</td>
							<?php
							}
							else
							{
							?>
								<td><?php echo esc($item['item_number']) ?></td>
								<td style="align: center;">
									<?php echo esc($item['name']) . ' '. implode(' ', [$item['attribute_values'], $item['attribute_dtvalues']]) ?>
									<br/>
									<?php if ($item['stock_type'] == '0'): echo '[' . to_quantity_decimals($item['in_stock']) . ' in ' . $item['stock_name'] . ']'; endif; ?>
								</td>
							<?php
							}
							?>

							<td>
								<?php
								if($items_module_allowed && $change_price)
								{
									echo form_input (['name' => 'price', 'class' => 'form-control input-sm', 'value' => to_currency_no_money($item['price']), 'tabindex' => ++$tabindex, 'onClick' => 'this.select();']);
								}
								else
								{
									echo to_currency($item['price']);
									echo form_hidden('price', to_currency_no_money($item['price']));
								}
								?>
							</td>

							<td>
								<?php
								if($item['is_serialized'])
								{
									echo to_quantity_decimals($item['quantity']);
									echo form_hidden('quantity', $item['quantity']);
								}
								else
								{
									echo form_input (['name' => 'quantity', 'class' => 'form-control input-sm', 'value' => to_quantity_decimals($item['quantity']), 'tabindex' => ++$tabindex, 'onClick' => 'this.select();']);
								}
								?>
							</td>

							<td>
								<div class="input-group">
									<?php echo form_input (['name' => 'discount', 'class' => 'form-control input-sm', 'value' => $item['discount_type'] ? to_currency_no_money($item['discount']) : to_decimals($item['discount']), 'tabindex' => ++$tabindex, 'onClick' => 'this.select();']) ?>
									<span class="input-group-btn">
										<?php echo form_checkbox (['id' => 'discount_toggle', 'name' => 'discount_toggle', 'value' => 1, 'data-toggle' => "toggle",'data-size' => 'small', 'data-onstyle' => 'success', 'data-on' => '<b>' . $config['currency_symbol'] . '</b>', 'data-off' => '<b>%</b>', 'data-line' => esc($line), 'checked' => $item['discount_type']]) ?>
									</span>
								</div>
							</td>

							<td>
								<?php
								if($item['item_type'] == ITEM_AMOUNT_ENTRY)	//TODO: === ?
								{
									echo form_input (['name' => 'discounted_total', 'class' => 'form-control input-sm', 'value' => to_currency_no_money($item['discounted_total']), 'tabindex' => ++$tabindex, 'onClick' => 'this.select();']);
								}
								else
								{
									echo to_currency($item['discounted_total']);
								}
								?>
							</td>

							<td><a href="javascript:document.getElementById('<?php echo "cart_$line" ?>').submit();" title=<?php echo lang('Sales.update') ?> ><span class="glyphicon glyphicon-refresh"></span></a></td>
						</tr>
						<tr>
							<?php
							if($item['item_type'] == ITEM_TEMP)
							{
							?>
								<td><?php echo form_input (['type' => 'hidden', 'name' => 'item_id', 'value' => $item['item_id']]) ?></td>
								<td style="align: center;" colspan="6">
									<?php echo form_input (['name' => 'item_description', 'id' => 'item_description', 'class' => 'form-control input-sm', 'value' => esc($item['description']), 'tabindex' => ++$tabindex]) ?>
								</td>
								<td> </td>
							<?php
							}
							else
							{
							?>
								<td> </td>
								<?php
								if($item['allow_alt_description'])
								{
								?>
									<td style="color: #2F4F4F;"><?php echo lang('Sales.description_abbrv') ?></td>
								<?php
								}
								?>

								<td colspan='2' style="text-align: left;">
									<?php
									if($item['allow_alt_description'])
									{
										echo form_input (['name' => 'description', 'class' => 'form-control input-sm', 'value' => esc($item['description']), 'onClick' => 'this.select();']);
									}
									else
									{
										if($item['description'] != '')
										{
											echo $item['description'];
											echo form_hidden('description', esc($item['description']));
										}
										else
										{
											echo lang('Sales.no_description');
											echo form_hidden('description','');
										}
									}
									?>
								</td>
								<td>&nbsp;</td>
								<td style="color: #2F4F4F;">
									<?php
									if($item['is_serialized'])
									{
										echo lang('Sales.serial');
									}
									?>
								</td>
								<td colspan='4' style="text-align: left;">
									<?php
									if($item['is_serialized'])
									{
										echo form_input (['name' => 'serialnumber', 'class' => 'form-control input-sm', 'value' => esc($item['serialnumber']), 'onClick' => 'this.select();']);
									}
									else
									{
										echo form_hidden('serialnumber', '');
									}
									?>
								</td>
							<?php
							}
							?>
						</tr>
					<?php echo form_close() ?>
			<?php
				}
			}
			?>
		</tbody>
	</table>
</div>

<!-- Overall Sale -->

<div id="overall_sale" class="panel panel-default">
	<div class="panel-body">
		<?php echo form_open(esc("$controller_name/select_customer"), ['id' => 'select_customer_form', 'class' => 'form-horizontal']) ?>
			<?php
			if(isset($customer))
			{
			?>
				<table class="sales_table_100">
					<tr>
						<th style="width: 55%;"><?php echo lang('Sales.customer') ?></th>
						<th style="width: 45%; text-align: right;"><?php echo anchor(esc("customers/view/$customer_id"), esc($customer), ['class' => 'modal-dlg', 'data-btn-submit' => lang('Common.submit'), 'title' => lang('Customers.update')]) ?></th>
					</tr>
					<?php
					if(!empty($customer_email))
					{
					?>
						<tr>
							<th style="width: 55%;"><?php echo lang('Sales.customer_email') ?></th>
							<th style="width: 45%; text-align: right;"><?php echo esc($customer_email) ?></th>
						</tr>
					<?php
					}
					?>
					<?php
					if(!empty($customer_address))
					{
					?>
						<tr>
							<th style="width: 55%;"><?php echo lang('Sales.customer_address') ?></th>
							<th style="width: 45%; text-align: right;"><?php echo esc($customer_address) ?></th>
						</tr>
					<?php
					}
					?>
					<?php
					if(!empty($customer_location))
					{
					?>
						<tr>
							<th style="width: 55%;"><?php echo lang('Sales.customer_location') ?></th>
							<th style="width: 45%; text-align: right;"><?php echo esc($customer_location) ?></th>
						</tr>
					<?php
					}
					?>
					<tr>
						<th style="width: 55%;"><?php echo lang('Sales.customer_discount') ?></th>
						<th style="width: 45%; text-align: right;"><?php echo ($customer_discount_type == FIXED) ? to_currency($customer_discount) : $customer_discount . '%' ?></th>
					</tr>
					<?php if($config['customer_reward_enable'] == TRUE): ?>
					<?php
					if(!empty($customer_rewards))
					{
					?>
						<tr>
							<th style="width: 55%;"><?php echo lang('Sales.rewards_package') ?></th>
							<th style="width: 45%; text-align: right;"><?php echo esc($customer_rewards['package_name']) ?></th>
						</tr>
						<tr>
							<th style="width: 55%;"><?php echo lang('Customers.available_points') ?></th>
							<th style="width: 45%; text-align: right;"><?php echo esc($customer_rewards['points']) ?></th>
						</tr>
					<?php
					}
					?>
					<?php endif; ?>
					<tr>
						<th style="width: 55%;"><?php echo lang('Sales.customer_total') ?></th>
						<th style="width: 45%; text-align: right;"><?php echo to_currency($customer_total) ?></th>
					</tr>
					<?php
					if(!empty($mailchimp_info))
					{
					?>
						<tr>
							<th style="width: 55%;"><?php echo lang('Sales.customer_mailchimp_status') ?></th>
							<th style="width: 45%; text-align: right;"><?php echo esc($mailchimp_info['status']) ?></th>
						</tr>
					<?php
					}
					?>
				</table>

				<?php echo anchor(
					"$controller_name/removeCustomer",
					'<span class=\'glyphicon glyphicon-remove\'>&nbsp</span>' . lang('Common.remove') . ' ' . lang('Customers.customer'),
						['class' => 'btn btn-danger btn-sm', 'id' => 'remove_customer_button', 'title' => lang('Common.remove') . ' ' . lang('Customers.customer')]
					)
				?>
			<?php
			}
			else
			{
			?>
				<div class="form-group" id="select_customer">
					<label id="customer_label" for="customer" class="control-label" style="margin-bottom: 1em; margin-top: -1em;"><?php echo lang('Sales.select_customer') . esc(" $customer_required") ?></label>
					<?php echo form_input (['name' => 'customer', 'id' => 'customer', 'class' => 'form-control input-sm', 'value' => lang('Sales.start_typing_customer_name')]) ?>

					<button class='btn btn-info btn-sm modal-dlg' data-btn-submit="<?php echo lang('Common.submit') ?>" data-href="<?php echo "customers/view" ?>"
							title="<?php echo lang("$controller_name.new_customer") ?>">
						<span class="glyphicon glyphicon-user">&nbsp</span><?php echo lang("$controller_name.new_customer") ?>
					</button>
					<button class='btn btn-default btn-sm modal-dlg' id='show_keyboard_help' data-href="<?php echo site_url("$controller_name/sales_keyboard_help") ?>"
							title="<?php echo lang('Sales.key_title'); ?>">
						<span class="glyphicon glyphicon-share-alt">&nbsp</span><?php echo lang('Sales.key_help'); ?>
					</button>

				</div>
			<?php
			}
			?>
		<?php echo form_close() ?>

		<table class="sales_table_100" id="sale_totals">
			<tr>
				<th style="width: 55%;"><?php echo lang('Sales.quantity_of_items', [$item_count]) ?></th>
				<th style="width: 45%; text-align: right;"><?php echo $total_units ?></th>
			</tr>
			<tr>
				<th style="width: 55%;"><?php echo lang('Sales.sub_total') ?></th>
				<th style="width: 45%; text-align: right;"><?php echo to_currency($subtotal) ?></th>
			</tr>

			<?php
			foreach($taxes as $tax_group_index=>$tax)
			{
			?>
				<tr>
					<th style="width: 55%;"><?php echo (float)$tax['tax_rate'] . '% ' . $tax['tax_group'] ?></th>
					<th style="width: 45%; text-align: right;"><?php echo to_currency_tax($tax['sale_tax_amount']) ?></th>
				</tr>
			<?php
			}
			?>

			<tr>
				<th style="width: 55%; font-size: 150%"><?php echo lang('Sales.total') ?></th>
				<th style="width: 45%; font-size: 150%; text-align: right;"><span id="sale_total"><?php echo to_currency($total) ?></span></th>
			</tr>
		</table>

		<?php
		// Only show this part if there are Items already in the register
		if(count($cart) > 0)
		{
		?>
			<table class="sales_table_100" id="payment_totals">
				<tr>
					<th style="width: 55%;"><?php echo lang('Sales.payments_total') ?></th>
					<th style="width: 45%; text-align: right;"><?php echo to_currency($payments_total) ?></th>
				</tr>
				<tr>
					<th style="width: 55%; font-size: 120%"><?php echo lang('Sales.amount_due') ?></th>
					<th style="width: 45%; font-size: 120%; text-align: right;"><span id="sale_amount_due"><?php echo to_currency($amount_due) ?></span></th>
				</tr>
			</table>

			<div id="payment_details">
				<?php
				// Show Complete sale button instead of Add Payment if there is no amount due left
				if($payments_cover_total)
				{
				?>
					<?php echo form_open(esc("$controller_name/addPayment"), ['id' => 'add_payment_form', 'class' => 'form-horizontal']) ?>
						<table class="sales_table_100">
							<tr>
								<td><?php echo lang('Sales.payment') ?></td>
								<td>
									<?php echo form_dropdown('payment_type', $payment_options, $selected_payment_type, ['id' => 'payment_types', 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit', 'disabled' => 'disabled']) ?>
								</td>
							</tr>
							<tr>
								<td><span id="amount_tendered_label"><?php echo lang('Sales.amount_tendered') ?></span></td>
								<td>
									<?php echo form_input (['name' => 'amount_tendered', 'id' => 'amount_tendered', 'class' => 'form-control input-sm disabled', 'disabled' => 'disabled', 'value' => '0', 'size' => '5', 'tabindex' => ++$tabindex, 'onClick' => 'this.select();']) ?>
								</td>
							</tr>
						</table>
					<?php echo form_close() ?>

					<?php
					// Only show this part if in sale or return mode
					if($pos_mode)
					{
						$due_payment = FALSE;

						if(count($payments) > 0)
						{
							foreach($payments as $payment_id => $payment)
							{
								if($payment['payment_type'] == lang('Sales.due'))
								{
									$due_payment = TRUE;
								}
							}
						}

						if(!$due_payment || ($due_payment && isset($customer)))	//TODO: $due_payment is not needed because the first clause insures that it will always be true if it gets to this point.  Can be shortened to if(!$due_payment || isset($customer))
						{
					?>
							<div class='btn btn-sm btn-success pull-right' id='finish_sale_button' tabindex="<?php echo ++$tabindex ?>"><span class="glyphicon glyphicon-ok">&nbsp</span><?php echo lang('Sales.complete_sale') ?></div>
					<?php
						}
					}
					?>
				<?php
				}
				else
				{
				?>
					<?php echo form_open(esc("$controller_name/addPayment"), ['id' => 'add_payment_form', 'class' => 'form-horizontal']) ?>
						<table class="sales_table_100">
							<tr>
								<td><?php echo lang('Sales.payment') ?></td>
								<td>
									<?php echo form_dropdown('payment_type', $payment_options,  $selected_payment_type, ['id' => 'payment_types', 'class' => 'selectpicker show-menu-arrow', 'data-style' => 'btn-default btn-sm', 'data-width' => 'fit']) ?>
								</td>
							</tr>
							<tr>
								<td><span id="amount_tendered_label"><?php echo lang('Sales.amount_tendered') ?></span></td>
								<td>
									<?php echo form_input (['name' => 'amount_tendered', 'id' => 'amount_tendered', 'class' => 'form-control input-sm non-giftcard-input', 'value' => to_currency_no_money($amount_due), 'size' => '5', 'tabindex' => ++$tabindex, 'onClick' => 'this.select();']) ?>
									<?php echo form_input (['name' => 'amount_tendered', 'id' => 'amount_tendered', 'class' => 'form-control input-sm giftcard-input', 'disabled' => true, 'value' => to_currency_no_money($amount_due), 'size' => '5', 'tabindex' => ++$tabindex]) ?>
								</td>
							</tr>
						</table>
					<?php echo form_close() ?>

					<div class='btn btn-sm btn-success pull-right' id='add_payment_button' tabindex="<?php echo ++$tabindex ?>"><span class="glyphicon glyphicon-credit-card">&nbsp</span><?php echo lang('Sales.add_payment') ?></div>
				<?php
				}
				?>

				<?php
				// Only show this part if there is at least one payment entered.
				if(count($payments) > 0)
				{
				?>
					<table class="sales_table_100" id="register">
						<thead>
							<tr>
								<th style="width: 10%;"><?php echo lang('Common.delete') ?></th>
								<th style="width: 60%;"><?php echo lang('Sales.payment_type') ?></th>
								<th style="width: 20%;"><?php echo lang('Sales.payment_amount') ?></th>
							</tr>
						</thead>

						<tbody id="payment_contents">
							<?php
							foreach($payments as $payment_id => $payment)
							{
							?>
								<tr>
									<td><?php echo anchor(esc("$controller_name/delete_payment/$payment_id"), '<span class="glyphicon glyphicon-trash"></span>') ?></td>
									<td><?php echo esc($payment['payment_type']) ?></td>
									<td style="text-align: right;"><?php echo to_currency($payment['payment_amount']) ?></td>
								</tr>
							<?php
							}
							?>
						</tbody>
					</table>
				<?php
				}
				?>
			</div>

			<?php echo form_open(esc("$controller_name/cancel"), ['id' => 'buttons_form']) ?>
				<div class="form-group" id="buttons_sale">
					<div class='btn btn-sm btn-default pull-left' id='suspend_sale_button'><span class="glyphicon glyphicon-align-justify">&nbsp</span><?php echo lang('Sales.suspend_sale') ?></div>
					<?php
					// Only show this part if the payment covers the total
					if(!$pos_mode && isset($customer))
					{
					?>
						<div class='btn btn-sm btn-success' id='finish_invoice_quote_button'><span class="glyphicon glyphicon-ok">&nbsp</span><?php echo esc($mode_label) ?></div>
					<?php
					}
					?>

					<div class='btn btn-sm btn-danger pull-right' id='cancel_sale_button'><span class="glyphicon glyphicon-remove">&nbsp</span><?php echo lang('Sales.cancel_sale') ?></div>
				</div>
			<?php echo form_close() ?>

			<?php
			// Only show this part if the payment cover the total
			if($payments_cover_total || !$pos_mode)
			{
			?>
				<div class="container-fluid">
					<div class="no-gutter row">
						<div class="form-group form-group-sm">
							<div class="col-xs-12">
								<?php echo form_label(lang('Common.comments'), 'comments', ['class' => 'control-label', 'id' => 'comment_label', 'for' => 'comment']) ?>
								<?php echo form_textarea (['name' => 'comment', 'id' => 'comment', 'class' => 'form-control input-sm', 'value' => esc($comment), 'rows' => '2']) ?>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="form-group form-group-sm">
							<div class="col-xs-6">
								<label for="sales_print_after_sale" class="control-label checkbox">
									<?php echo form_checkbox (['name' => 'sales_print_after_sale', 'id' => 'sales_print_after_sale', 'value' => 1, 'checked' => $print_after_sale]) ?>
									<?php echo lang('Sales.print_after_sale') ?>
								</label>
							</div>

							<?php
							if(!empty($customer_email))
							{
							?>
								<div class="col-xs-6">
									<label for="email_receipt" class="control-label checkbox">
										<?php echo form_checkbox (['name' => 'email_receipt', 'id' => 'email_receipt', 'value'=>1, 'checked' => $email_receipt]) ?>
										<?php echo lang('Sales.email_receipt') ?>
									</label>
								</div>
							<?php
							}
							?>
							<?php
							if($mode == 'sale_work_order')
							{
							?>
								<div class="col-xs-6">
									<label for="price_work_orders" class="control-label checkbox">
									<?php echo form_checkbox (['name' => 'price_work_orders', 'id' => 'price_work_orders', 'value' => 1, 'checked' => $price_work_orders]) ?>
									<?php echo lang('Sales.include_prices') ?>
									</label>
								</div>
							<?php
							}
							?>
						</div>
					</div>
					<?php
					if(($mode == 'sale_invoice') && $config['invoice_enable'] == TRUE)
					{
					?>
						<div class="row">
							<div class="form-group form-group-sm">
								<div class="col-xs-6">
									<label for="sales_invoice_number" class="control-label checkbox">
										<?php echo lang('Sales.invoice_enable') ?>
									</label>
								</div>

								<div class="col-xs-6">
									<div class="input-group input-group-sm">
										<span class="input-group-addon input-sm">#</span>
										<?php echo form_input (['name' => 'sales_invoice_number', 'id' => 'sales_invoice_number', 'class' => 'form-control input-sm', 'value' => esc($invoice_number)]) ?>
									</div>
								</div>
							</div>
						</div>
					<?php
					}
					?>
				</div>
			<?php
			}
			?>
		<?php
		}
		?>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function()
{
	const redirect = function() {
		window.location.href = "<?php echo site_url('sales'); ?>";
	};

	$("#remove_customer_button").click(function()
	{
		$.post("<?php echo site_url('sales/removeCustomer'); ?>", redirect);
	});

	$(".delete_item_button").click(function()
	{
		const item_id = $(this).data('item-id');
		$.post("<?php echo site_url('sales/deleteItem/'); ?>" + item_id, redirect);
	});

	$(".delete_payment_button").click(function() {
		const item_id = $(this).data('payment-id');
		$.post("<?php echo site_url('sales/deletePayment/'); ?>" + item_id, redirect);
	});

	$("input[name='item_number']").change(function() {
		var item_id = $(this).parents('tr').find("input[name='item_id']").val();
		var item_number = $(this).val();
		$.ajax({
			url: "<?php echo site_url('sales/change_item_number') ?>",
			method: 'post',
			data: {
				'item_id': item_id,
				'item_number': item_number,
			},
			dataType: 'json'
		});
	});

	$("input[name='name']").change(function() {
		var item_id = $(this).parents('tr').find("input[name='item_id']").val();
		var item_name = $(this).val();
		$.ajax({
			url: "<?php echo site_url('sales/change_item_name') ?>",
			method: 'post',
			data: {
				'item_id': item_id,
				'item_name': item_name,
			},
			dataType: 'json'
		});
	});

	$("input[name='item_description']").change(function() {
		var item_id = $(this).parents('tr').find("input[name='item_id']").val();
		var item_description = $(this).val();
		$.ajax({
			url: "<?php echo site_url('sales/change_item_description') ?>",
			method: 'post',
			data: {
				'item_id': item_id,
				'item_description': item_description,
			},
			dataType: 'json'
		});
	});

	$('#item').focus();

	$('#item').blur(function() {
		$(this).val("<?php echo lang('Sales.start_typing_item_name') ?>");
	});

	$('#item').autocomplete( {
		source: "<?php echo esc("$controller_name/itemSearch") ?>",
		minChars: 0,
		autoFocus: false,
		delay: 500,
		select: function (a, ui) {
			$(this).val(ui.item.value);
			$('#add_item_form').submit();
			return false;
		}
	});

	$('#item').keypress(function (e) {
		if(e.which == 13) {
			$('#add_item_form').submit();
			return false;
		}
	});

	var clear_fields = function() {
		if($(this).val().match("<?php echo lang('Sales.start_typing_item_name') . '|' . lang('Sales.start_typing_customer_name') ?>"))
		{
			$(this).val('');
		}
	};

	$('#item, #customer').click(clear_fields).dblclick(function(event) {
		$(this).autocomplete('search');
	});

	$('#customer').blur(function() {
		$(this).val("<?php echo lang('Sales.start_typing_customer_name') ?>");
	});

	$('#customer').autocomplete( {
		source: "<?php echo site_url('customers/suggest') ?>",
		minChars: 0,
		delay: 10,
		select: function (a, ui) {
			$(this).val(ui.item.value);
			$('#select_customer_form').submit();
			return false;
		}
	});

	$('#customer').keypress(function (e) {
		if(e.which == 13) {
			$('#select_customer_form').submit();
			return false;
		}
	});

	$('.giftcard-input').autocomplete( {
		source: "<?php echo site_url('giftcards/suggest') ?>",
		minChars: 0,
		delay: 10,
		select: function (a, ui) {
			$(this).val(ui.item.value);
			$('#add_payment_form').submit();
			return false;
		}
	});

	$('#comment').keyup(function() {
		$.post("<?php echo esc(site_url("$controller_name/set_comment"), 'url') ?>", {comment: $('#comment').val()});
	});

	<?php
	if($config['invoice_enable'] == TRUE)
	{
	?>
		$('#sales_invoice_number').keyup(function() {
			$.post("<?php echo esc(site_url("$controller_name/set_invoice_number"), 'url') ?>", {sales_invoice_number: $('#sales_invoice_number').val()});
		});

	<?php
	}
	?>

	$('#sales_print_after_sale').change(function() {
		$.post("<?php echo esc(site_url("$controller_name/set_print_after_sale"), 'url') ?>", {sales_print_after_sale: $(this).is(':checked')});
	});

	$('#price_work_orders').change(function() {
		$.post("<?php echo esc(site_url("$controller_name/set_price_work_orders"), 'url') ?>", {price_work_orders: $(this).is(':checked')});
	});

	$('#email_receipt').change(function() {
		$.post("<?php echo esc(site_url("$controller_name/set_email_receipt"), 'url') ?>", {email_receipt: $(this).is(':checked')});
	});

	$('#finish_sale_button').click(function() {
		$('#buttons_form').attr('action', "<?= "$controller_name/complete" ?>");
		$('#buttons_form').submit();
	});

	$('#finish_invoice_quote_button').click(function() {
		$('#buttons_form').attr('action', "<?= "$controller_name/complete" ?>");
		$('#buttons_form').submit();
	});

	$('#suspend_sale_button').click(function() {
		$('#buttons_form').attr('action', "<?php echo esc(site_url("$controller_name/suspend"), 'url') ?>");
		$('#buttons_form').submit();
	});

	$('#cancel_sale_button').click(function() {
		if(confirm("<?php echo lang('Sales.confirm_cancel_sale') ?>"))
		{
			$('#buttons_form').attr('action', "<?php echo esc(site_url("$controller_name/cancel"), 'url') ?>");
			$('#buttons_form').submit();
		}
	});

	$('#add_payment_button').click(function() {
		$('#add_payment_form').submit();
	});

	$('#payment_types').change(check_payment_type).ready(check_payment_type);

	$('#cart_contents input').keypress(function(event) {
		if(event.which == 13)
		{
			$(this).parents('tr').prevAll('form:first').submit();
		}
	});

	$('#amount_tendered').keypress(function(event) {
		if(event.which == 13)
		{
			$('#add_payment_form').submit();
		}
	});

	$('#finish_sale_button').keypress(function(event) {
		if(event.which == 13)
		{
			$('#finish_sale_form').submit();
		}
	});

	dialog_support.init('a.modal-dlg, button.modal-dlg');

	table_support.handle_submit = function(resource, response, stay_open) {
		$.notify( { message: response.message }, { type: response.success ? 'success' : 'danger'} )

		if(response.success)
		{
			if(resource.match(/customers$/))
			{
				$('#customer').val(response.id);
				$('#select_customer_form').submit();
			}
			else
			{
				var $stock_location = $("select[name='stock_location']").val();
				$('#item_location').val($stock_location);
				$('#item').val(response.id);
				if(stay_open)
				{
					$('#add_item_form').ajaxSubmit();
				}
				else
				{
					$('#add_item_form').submit();
				}
			}
		}
	}

	$('[name="price"],[name="quantity"],[name="discount"],[name="description"],[name="serialnumber"],[name="discounted_total"]').change(function() {
		$(this).parents('tr').prevAll('form:first').submit()
	});

	$('[name="discount_toggle"]').change(function() {
		var input = $('<input>').attr('type', 'hidden').attr('name', 'discount_type').val(($(this).prop('checked'))?1:0);
		$('#cart_'+ $(this).attr('data-line')).append($(input));
		$('#cart_'+ $(this).attr('data-line')).submit();
	});
});

function check_payment_type()
{
	var cash_mode = <?php echo json_encode($cash_mode) ?>;

	if($("#payment_types").val() == "<?php echo lang('Sales.giftcard') ?>")
	{
		$("#sale_total").html("<?php echo to_currency($total) ?>");
		$("#sale_amount_due").html("<?php echo to_currency($amount_due) ?>");
		$("#amount_tendered_label").html("<?php echo lang('Sales.giftcard_number') ?>");
		$("#amount_tendered:enabled").val('').focus();
		$(".giftcard-input").attr('disabled', false);
		$(".non-giftcard-input").attr('disabled', true);
		$(".giftcard-input:enabled").val('').focus();
	}
	else if(($("#payment_types").val() == "<?php echo lang('Sales.cash') ?>" && cash_mode == '1'))
	{
		$("#sale_total").html("<?php echo to_currency($non_cash_total) ?>");
		$("#sale_amount_due").html("<?php echo to_currency($cash_amount_due) ?>");
		$("#amount_tendered_label").html("<?php echo lang('Sales.amount_tendered') ?>");
		$("#amount_tendered:enabled").val("<?php echo to_currency_no_money($cash_amount_due) ?>");
		$(".giftcard-input").attr('disabled', true);
		$(".non-giftcard-input").attr('disabled', false);
	}
	else
	{
		$("#sale_total").html("<?php echo to_currency($non_cash_total) ?>");
		$("#sale_amount_due").html("<?php echo to_currency($amount_due) ?>");
		$("#amount_tendered_label").html("<?php echo lang('Sales.amount_tendered') ?>");
		$("#amount_tendered:enabled").val("<?php echo to_currency_no_money($amount_due) ?>");
		$(".giftcard-input").attr('disabled', true);
		$(".non-giftcard-input").attr('disabled', false);
	}
}

// Add Keyboard Shortcuts/Hotkeys to Sale Register
document.body.onkeyup = function(e)
{
	switch(event.altKey && event.keyCode)
	{
		case 49: // Alt + 1 Items Seach
			$("#item").focus();
			$("#item").select();
			break;
		case 50: // Alt + 2 Customers Search
			$("#customer").focus();
			$("#customer").select();
			break;
		case 51: // Alt + 3 Suspend Current Sale
			$("#suspend_sale_button").click();
			break;
		case 52: // Alt + 4 Check Suspended
			$("#show_suspended_sales_button").click();
			break;
		case 53: // Alt + 5 Edit Amount Tendered Value
			$("#amount_tendered").focus();
			$("#amount_tendered").select();
			break;
		case 54: // Alt + 6 Add Payment
			$("#add_payment_button").click();
			break;
		case 55: // Alt + 7 Add Payment and Complete Sales/Invoice
			$("#add_payment_button").click();
			window.location.href = "<?= 'sales/complete' ?>";
			break;
		case 56: // Alt + 8 Finish Quote/Invoice without payment
			$("#finish_invoice_quote_button").click();
			break;
		case 57: // Alt + 9 Open Shortcuts Help Modal
			$("#show_keyboard_help").click();
			break;
	}

	switch(event.keyCode)
	{
		case 27: // ESC Cancel Current Sale
			$("#cancel_sale_button").click();
			break;
	}
}

</script>

<?php echo view('partial/footer') ?>
