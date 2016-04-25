<?php $this->load->view("partial/header"); ?>

<?php
if (isset($error))
{
	echo "<div class='alert alert-dismissible alert-danger'>".$error."</div>";
}

if (!empty($warning))
{
	echo "<div class='alert alert-dismissible alert-warning'>".$warning."</div>";
}

if (isset($success))
{
	echo "<div class='alert alert-dismissible alert-success'>".$success."</div>";
}
?>

<div id="register_wrapper">

<!-- Top register controls -->

	<?php echo form_open("sales/change_mode", array('id'=>'mode_form', 'class'=>'form-horizontal panel panel-default')); ?>
		<div class="panel-body form-group">
			<ul>
				<li class="pull-left first_li">
					<label class="control-label"><?php echo $this->lang->line('sales_mode'); ?></label>
				</li>
				<li class="pull-left">
					<?php echo form_dropdown('mode', $modes, $mode, array('onchange'=>"$('#mode_form').submit();", 'class'=>'selectpicker show-menu-arrow', 'data-style'=>'btn-default btn-sm', 'data-width'=>'fit')); ?>
				</li>

				<?php
				if (count($stock_locations) > 1)
				{
				?>
					<li class="pull-left">
						<label class="control-label"><?php echo $this->lang->line('sales_stock_location'); ?></label>
					</li>
					<li class="pull-left">
						<?php echo form_dropdown('stock_location', $stock_locations, $stock_location, array('onchange'=>"$('#mode_form').submit();", 'class'=>'selectpicker show-menu-arrow', 'data-style'=>'btn-default btn-sm', 'data-width'=>'fit')); ?>
					</li>
				<?php
				}
				?>

				<li class="pull-right">

					<button class='btn btn-default btn-sm modal-dlg' id='show_suspended_sales_button'
							data-href='<?php echo site_url($controller_name."/suspended"); ?>'
							title='<?php echo $this->lang->line('sales_suspended_sales'); ?>'>
						<span class="glyphicon glyphicon-star"></span><?php echo $this->lang->line('sales_suspended_sales'); ?>
					</button>

				</li>
			
				<?php
				if ($this->Employee->has_grant('reports_sales', $this->session->userdata('person_id')))
				{
				?>
					<li class="pull-right">
						<?php echo anchor("sales/manage", $this->lang->line('sales_takings'), 
									array('class'=>'btn btn-primary btn-sm', 'id'=>'sales_takings_button', 'title'=>$this->lang->line('sales_takings'))); ?>
					</li>
				<?php
				}
				?>
			</ul>
		</div>
	<?php echo form_close(); ?>

	<?php echo form_open("sales/add", array('id'=>'add_item_form', 'class'=>'form-horizontal panel panel-default')); ?>
		<div class="panel-body form-group">
			<ul>
				<li class="pull-left first_li">
					<label for="item", class='control-label'><?php echo $this->lang->line('sales_find_or_scan_item_or_receipt'); ?></label>
				</li>
				<li class="pull-left">
					<?php echo form_input(array('name'=>'item', 'id'=>'item', 'class'=>'form-control input-sm', 'size'=>'50', 'tabindex'=>'1')); ?>
					<span class="ui-helper-hidden-accessible" role="status"></span>
				</li>
				<li class="pull-right">
					<button id='new_item_button' class='btn btn-info btn-sm pull-right modal-dlg modal-btn-submit' data-href='<?php echo site_url("items/view"); ?>'
							title='<?php echo $this->lang->line($controller_name . '_new_item'); ?>'>
						<span class="glyphicon glyphicon-tag"></span><?php echo $this->lang->line($controller_name. '_new_item'); ?>
					</button>
				</li>
			</ul>
		</div>
	<?php echo form_close(); ?>

<!-- Sale Items List -->
	
	<table class="sales_table_100" id="register">
		<thead>
			<tr>
				<th style="width: 5%;"><?php echo $this->lang->line('common_delete'); ?></th>
				<th style="width: 15%;"><?php echo $this->lang->line('sales_item_number'); ?></th>
				<th style="width: 35%;"><?php echo $this->lang->line('sales_item_name'); ?></th>
				<th style="width: 10%;"><?php echo $this->lang->line('sales_price'); ?></th>
				<th style="width: 10%;"><?php echo $this->lang->line('sales_quantity'); ?></th>
				<th style="width: 10%;"><?php echo $this->lang->line('sales_discount'); ?></th>
				<th style="width: 10%;"><?php echo $this->lang->line('sales_total'); ?></th>
				<th style="width: 5%;"><?php echo $this->lang->line('sales_update'); ?></th>
			</tr>
		</thead>

		<tbody id="cart_contents">
			<?php
			if(count($cart) == 0)
			{
			?>
				<tr>
					<td colspan='8'>
						<div class='alert alert-dismissible alert-info'><?php echo $this->lang->line('sales_no_items_in_cart'); ?></div>
					</td>
				</tr>
			<?php
			}
			else
			{				
				$tabindex = 2;				
				foreach(array_reverse($cart, true) as $line=>$item)
				{					
					if($tabindex == 3) 
					{
						$tabindex = 5;
					}
			?>
					<?php echo form_open("sales/edit_item/$line", array('class'=>'form-horizontal', 'id'=>'cart_'.$line)); ?>
						<tr>
							<td><?php echo anchor("sales/delete_item/$line", '<span class="glyphicon glyphicon-trash"></span>');?></td>
							<td><?php echo $item['item_number']; ?></td>
							<td style="align: center;">
								<?php echo $item['name']; ?><br /> <?php echo '[' . to_quantity_decimals($item['in_stock']) . ' in ' . $item['stock_name'] . ']'; ?>
								<?php echo form_hidden('location', $item['item_location']); ?>
							</td>

							<?php
							if ($items_module_allowed)
							{
							?>
								<td><?php echo form_input(array('name'=>'price', 'class'=>'form-control input-sm', 'value'=>to_currency_no_money($item['price'])));?></td>
							<?php
							}
							else
							{
							?>
								<td>
									<?php echo to_currency($item['price']); ?>
									<?php echo form_hidden('price', $item['price']); ?>
								</td>
							<?php
							}
							?>

							<td>
								<?php
								if($item['is_serialized']==1)
								{
									echo to_quantity_decimals($item['quantity']);
									echo form_hidden('quantity', $item['quantity']);
								}
								else
								{								
									echo form_input(array('name'=>'quantity', 'class'=>'form-control input-sm', 'value'=>to_quantity_decimals($item['quantity']), 'tabindex'=>$tabindex));
								}
								?>
							</td>

							<td><?php echo form_input(array('name'=>'discount', 'class'=>'form-control input-sm', 'value'=>$item['discount']));?></td>
							<td><?php echo to_currency($item['price']*$item['quantity']-$item['price']*$item['quantity']*$item['discount']/100); ?></td>
							<td><a href="javascript:document.getElementById('<?php echo 'cart_'.$line ?>').submit();" title=<?php echo $this->lang->line('sales_update')?> ><span class="glyphicon glyphicon-refresh"></span></a></td>
						</tr>
						<tr>
							<?php 
							if($item['allow_alt_description']==1)
							{
							?>
								<td style="color: #2F4F4F;"><?php echo $this->lang->line('sales_description_abbrv');?></td>
							<?php 
							}
							?>

							<td colspan='2' style="text-align: left;">
								<?php
								if($item['allow_alt_description']==1)
								{
									echo form_input(array('name'=>'description', 'class'=>'form-control input-sm', 'value'=>$item['description']));
								}
								else
								{
									if ($item['description']!='')
									{
										echo $item['description'];
										echo form_hidden('description', $item['description']);
									}
									else
									{
										echo $this->lang->line('sales_no_description');
										echo form_hidden('description','');
									}
								}
								?>
							</td>
							<td>&nbsp;</td>
							<td style="color: #2F4F4F;">
								<?php
								if($item['is_serialized']==1)
								{
									echo $this->lang->line('sales_serial');
								}
								?>
							</td>
							<td colspan='4' style="text-align: left;">
								<?php
								if($item['is_serialized']==1)
								{
									echo form_input(array('name'=>'serialnumber', 'class'=>'form-control input-sm', 'value'=>$item['serialnumber']));
								}
								else
								{
									echo form_hidden('serialnumber', '');
								}
								?>
							</td>
						</tr>
					<?php echo form_close(); ?>
			<?php					
					$tabindex++;					
				}
			}
			?>
		</tbody>
	</table>
</div>

<!-- Overall Sale -->

<div id="overall_sale" class="panel panel-default">
	<div class="panel-body">
		<?php
		if(isset($customer))
		{
		?>
			<table class="sales_table_100">
				<tr>
					<th style='width: 55%;'><?php echo $this->lang->line("sales_customer"); ?></th>
					<th style="width: 45%; text-align: right;"><?php echo $customer; ?></th>
				</tr>
				<?php
				if(!empty($customer_email))
				{
				?>
					<tr>
						<th style='width: 55%;'><?php echo $this->lang->line("sales_customer_email"); ?></th>
						<th style="width: 45%; text-align: right;"><?php echo $customer_email; ?></th>
					</tr>
				<?php
				}
				?>
				<?php
				if(!empty($customer_address))
				{
				?>
					<tr>
						<th style='width: 55%;'><?php echo $this->lang->line("sales_customer_address"); ?></th>
						<th style="width: 45%; text-align: right;"><?php echo $customer_address; ?></th>
					</tr>
				<?php
				}
				?>
				<?php
				if(!empty($customer_location))
				{
				?>
					<tr>
						<th style='width: 55%;'><?php echo $this->lang->line("sales_customer_location"); ?></th>
						<th style="width: 45%; text-align: right;"><?php echo $customer_location; ?></th>
					</tr>
				<?php
				}
				?>
				<tr>
					<th style='width: 55%;'><?php echo $this->lang->line("sales_customer_discount"); ?></th>
					<th style="width: 45%; text-align: right;"><?php echo $customer_discount_percent . ' %'; ?></th>
				</tr>
				<tr>
					<th style='width: 55%;'><?php echo $this->lang->line("sales_customer_total"); ?></th>
					<th style="width: 45%; text-align: right;"><?php echo to_currency($customer_total); ?></th>
				</tr>
			</table>

			<?php echo anchor("sales/remove_customer", $this->lang->line('common_remove').' '.$this->lang->line('customers_customer'),
								array('class'=>'btn btn-danger btn-xs', 'id'=>'remove_customer_button', 'title'=>$this->lang->line('common_remove').' '.$this->lang->line('customers_customer'))); ?>
		<?php
		}
		else
		{
		?>
			<?php echo form_open("sales/select_customer", array('id'=>'select_customer_form', 'class'=>'form-horizontal')); ?>
				<div class="form-group" id="select_customer">
					<label id="customer_label" for="customer" class="control-label" style="margin-bottom: 1em; margin-top: -1em;"><?php echo $this->lang->line('sales_select_customer'); ?></label>
					<?php echo form_input(array('name'=>'customer', 'id'=>'customer', 'class'=>'form-control input-sm', 'value'=>$this->lang->line('sales_start_typing_customer_name')));?>

					<button class='btn btn-info btn-sm modal-dlg modal-btn-submit' data-href='<?php echo site_url("customers/view"); ?>'
							title='<?php echo $this->lang->line($controller_name. '_new_customer'); ?>'>
						<span class="glyphicon glyphicon-user"></span><?php echo $this->lang->line($controller_name. '_new_customer'); ?>
					</button>

				</div>
			<?php echo form_close(); ?>
		<?php
		}
		?>

		<table class="sales_table_100" id="sale_totals">
			<tr>
				<th style="width: 55%;"><?php echo $this->lang->line('sales_sub_total'); ?></th>
				<th style="width: 45%; text-align: right;"><?php echo to_currency($this->config->item('tax_included') ? $tax_exclusive_subtotal : $subtotal); ?></th>
			</tr>
			
			<?php
			foreach($taxes as $name=>$value)
			{
			?>
				<tr>
					<th style='width: 55%;'><?php echo $name; ?></th>
					<th style="width: 45%; text-align: right;"><?php echo to_currency($value); ?></th>
				</tr>
			<?php
			}
			?>

			<tr>
				<th style='width: 55%;'><?php echo $this->lang->line('sales_total'); ?></th>
				<th style="width: 45%; text-align: right;"><?php echo to_currency($total); ?></th>
			</tr>
		</table>
	
		<?php
		// Only show this part if there are Items already in the sale.
		if(count($cart) > 0)
		{
		?>
			<table class="sales_table_100" id="payment_totals">
				<tr>
					<th style="width: 55%;"><?php echo $this->lang->line('sales_payments_total');?></th>
					<th style="width: 45%; text-align: right;"><?php echo to_currency($payments_total); ?></th>
				</tr>
				<tr>
					<th style="width: 55%;"><?php echo $this->lang->line('sales_amount_due');?></th>
					<th style="width: 45%; text-align: right;"><?php echo to_currency($amount_due); ?></th>
				</tr>
			</table>

			<div id="payment_details">
				<?php echo form_open("sales/add_payment", array('id'=>'add_payment_form', 'class'=>'form-horizontal')); ?>						
					<?php
					// Show Complete sale button instead of Add Payment if there is no amount due left
					if( $payments_cover_total )
					{
					?>
						<table class="sales_table_100">
							<tr>
								<td><?php echo $this->lang->line('sales_payment');?></td>
								<td>
									<?php echo form_dropdown('payment_type', $payment_options, array(), array('id'=>'payment_types', 'class'=>'selectpicker show-menu-arrow', 'data-style'=>'btn-default btn-sm', 'data-width'=>'auto', 'disabled'=>'')); ?>
								</td>
							</tr>
							<tr>
								<td><span id="amount_tendered_label"><?php echo $this->lang->line('sales_amount_tendered'); ?></span></td>
								<td>
									<?php echo form_input(array('name'=>'amount_tendered', 'id'=>'amount_tendered', 'class'=>'form-control input-sm disabled', 'disabled'=>'', 'value'=>to_currency_no_money($amount_due), 'size'=>'5', 'tabindex'=>3)); ?>
								</td>
							</tr>
						</table>
					
						<div class='btn btn-sm btn-success pull-right' id='finish_sale_button' tabindex='4'><?php echo $this->lang->line('sales_complete_sale'); ?></div>
					<?php
					}
					else
					{
					?>
						<table class="sales_table_100">
							<tr>
								<td><?php echo $this->lang->line('sales_payment');?></td>
								<td>
									<?php echo form_dropdown('payment_type', $payment_options, array(), array('id'=>'payment_types', 'class'=>'selectpicker show-menu-arrow', 'data-style'=>'btn-default btn-sm', 'data-width'=>'auto')); ?>
								</td>
							</tr>
							<tr>
								<td><span id="amount_tendered_label"><?php echo $this->lang->line('sales_amount_tendered'); ?></span></td>
								<td>
									<?php echo form_input(array('name'=>'amount_tendered', 'id'=>'amount_tendered', 'class'=>'form-control input-sm', 'value'=>to_currency_no_money($amount_due), 'size'=>'5', 'tabindex'=>3)); ?>
								</td>
							</tr>
						</table>

						<div class='btn btn-sm btn-success pull-right' id='add_payment_button' tabindex='4'><?php echo $this->lang->line('sales_add_payment'); ?></div>
					<?php
					}
					?>
				<?php echo form_close(); ?>

				<?php
				// Only show this part if there is at least one payment entered.
				if(count($payments) > 0)
				{
				?>
					<table class="sales_table_100" id="register">
						<thead>
							<tr>
								<th style="width: 10%;"><?php echo $this->lang->line('common_delete'); ?></th>
								<th style="width: 60%;"><?php echo $this->lang->line('sales_payment_type'); ?></th>
								<th style="width: 20%;"><?php echo $this->lang->line('sales_payment_amount'); ?></th>
							</tr>
						</thead>
			
						<tbody id="payment_contents">
							<?php
							foreach($payments as $payment_id=>$payment)
							{
							?>
								<tr>
									<td><?php echo anchor( "sales/delete_payment/$payment_id", '<span class="glyphicon glyphicon-trash"></span>' ); ?></td>
									<td><?php echo $payment['payment_type']; ?></td>
									<td style="text-align: right;"><?php echo to_currency( $payment['payment_amount'] ); ?></td>
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

			<?php echo form_open("sales/cancel", array('id'=>'buttons_form', 'class'=>'form-horizontal')); ?>
				<div class="form-group" id="buttons_sale">
					<div class='btn btn-sm btn-default pull-left' id='suspend_sale_button'><?php echo $this->lang->line('sales_suspend_sale'); ?></div>

					<div class='btn btn-sm btn-danger pull-right' id='cancel_sale_button'><?php echo $this->lang->line('sales_cancel_sale'); ?></div>
				</div>
				
				<?php
				// Only show this part if there is at least one payment entered.
				if (count($payments) > 0)
				{
				?>
					<div class="form-group form-group-sm">
						<?php echo form_label($this->lang->line('common_comments'), 'comments', array('class'=>'control-label', 'id'=>'comment_label', 'for'=>'comment')); ?>
						<?php echo form_textarea(array('name'=>'comment', 'id'=>'comment', 'class'=>'form-control input-sm', 'value'=>$comment, 'rows'=>'2')); ?>

						<table class="sales_table_100">
							<tr>
								<td style="width: 30%; text-align: left;">
									<?php echo form_label($this->lang->line('sales_print_after_sale'), 'print_after_sale', array('class'=>'control-label')); ?>
								</td>
								<td style="width: 20%; text-align: center; display: inline-block;">
									<?php echo form_checkbox(array('name'=>'sales_print_after_sale', 'id'=>'sales_print_after_sale', 'class'=>'checkbox', 'value'=>1, 'checked'=>$print_after_sale)); ?>
								</td>

								<?php 
								if(!empty($customer_email))
								{
								?>
									<td style="width: 30%; text-align: left;">
										<?php echo form_label($this->lang->line('sales_email_receipt'), 'email_receipt', array('class'=>'control-label')); ?>
									</td>
									<td style="width: 20%; text-align: center; display: inline-block;">
										<?php echo form_checkbox(array('name'=>'email_receipt', 'id'=>'email_receipt', 'class'=>'checkbox', 'value'=>1, 'checked'=>$email_receipt)); ?>
									</td>
								<?php
								}
								else
								{
								?>
									<td style="width: 30%; text-align: left;"></td>
									<td style="width: 20%; text-align: center; display: inline-block;"></td>
								<?php
								}
								?>
							</tr>
						
							<?php
							if ($mode == "sale" && $this->config->item('invoice_enable') == TRUE) 
							{
							?>
								<tr>
									<td style="width: 30%; text-align: left;">
										<?php echo form_label($this->lang->line('sales_invoice_enable'), 'invoice_enable', array('class'=>'control-label')); ?>
									</td>
									<td style="width: 20%; text-align: center; display: inline-block;">
										<?php echo form_checkbox(array('name'=>'sales_invoice_enable', 'id'=>'sales_invoice_enable', 'class'=>'checkbox', 'value'=>1, 'checked'=>$invoice_number_enabled)); ?>
									</td>
									<td style="width: 30%; text-align: left;">
										<?php echo form_label($this->lang->line('sales_invoice_number'), 'invoice_number', array('class'=>'control-label')); ?>
									</td>
									<td style="width: 20%; text-align: right;">
										<?php echo form_input(array('name'=>'sales_invoice_number', 'id'=>'sales_invoice_number', 'class'=>'form-control input-sm', 'value'=>$invoice_number, 'size'=>5));?>
									</td>
								</tr>
							<?php 
							}
							?>
						</table>
					</div>
				<?php
				}
				?>
			<?php echo form_close(); ?>
		<?php
		}
		?>
	</div>
</div>

<script type="text/javascript" language="javascript">
$(document).ready(function()
{
    $("#item").autocomplete(
    {
		source: '<?php echo site_url("sales/item_search"); ?>',
    	minChars:0,
    	autoFocus: false,
       	delay:10,
		select: function (a, ui) {
			$(this).val(ui.item.value);
			$("#add_item_form").submit();
		}
    });

	$('#item').focus();

    $('#item').blur(function()
    {
        $(this).val("<?php echo $this->lang->line('sales_start_typing_item_name'); ?>");
    });

    var clear_fields = function()
    {
        if ($(this).val().match("<?php echo $this->lang->line('sales_start_typing_item_name') . '|' . $this->lang->line('sales_start_typing_customer_name'); ?>"))
        {
            $(this).val('');
        }
    };

    $("#customer").autocomplete(
    {
		source: '<?php echo site_url("customers/suggest"); ?>',
    	minChars:0,
    	delay:10,
		select: function (a, ui) {
			$(this).val(ui.item.value);
			$("#select_customer_form").submit();
		}
    });

	$('#item, #customer').click(clear_fields).dblclick(function(event)
	{
		$(this).autocomplete("search");
	});

	$('#customer').blur(function()
    {
    	$(this).val("<?php echo $this->lang->line('sales_start_typing_customer_name'); ?>");
    });

	$('#comment').keyup(function() 
	{
		$.post('<?php echo site_url("sales/set_comment");?>', {comment: $('#comment').val()});
	});

	<?php
	if ($this->config->item('invoice_enable') == TRUE) 
	{
	?>
		$('#sales_invoice_number').keyup(function() 
		{
			$.post('<?php echo site_url("sales/set_invoice_number");?>', {sales_invoice_number: $('#sales_invoice_number').val()});
		});

		var enable_invoice_number = function() 
		{
			var enabled = $("#sales_invoice_enable").is(":checked");
			$("#sales_invoice_number").prop("disabled", !enabled).parents('tr').show();
			return enabled;
		}

		enable_invoice_number();
		
		$("#sales_invoice_enable").change(function()
		{
			var enabled = enable_invoice_number();
			$.post('<?php echo site_url("sales/set_invoice_number_enabled");?>', {sales_invoice_number_enabled: enabled});
		});
	<?php
	}
	?>

	$("#sales_print_after_sale").change(function()
	{
		$.post('<?php echo site_url("sales/set_print_after_sale");?>', {sales_print_after_sale: $(this).is(":checked")});
	});
	
	$('#email_receipt').change(function() 
	{
		$.post('<?php echo site_url("sales/set_email_receipt");?>', {email_receipt: $('#email_receipt').is(':checked') ? '1' : '0'});
	});
	
    $("#finish_sale_button").click(function()
    {
		$('#buttons_form').attr('action', '<?php echo site_url("sales/complete"); ?>');
		$('#buttons_form').submit();
    });

	$("#suspend_sale_button").click(function()
	{ 	
		$('#buttons_form').attr('action', '<?php echo site_url("sales/suspend"); ?>');
		$('#buttons_form').submit();
	});

    $("#cancel_sale_button").click(function()
    {
    	if (confirm('<?php echo $this->lang->line("sales_confirm_cancel_sale"); ?>'))
    	{
			$('#buttons_form').attr('action', '<?php echo site_url("sales/cancel"); ?>');
    		$('#buttons_form').submit();
    	}
    });

	$("#add_payment_button").click(function()
	{
		$('#add_payment_form').submit();
    });

	$("#payment_types").change(check_payment_type_giftcard).ready(check_payment_type_giftcard)
	
	$("#amount_tendered").keypress(function(event)
	{
		if( event.which == 13 )
		{
			$('#add_payment_form').submit();
		}
	});
	
    $("#finish_sale_button").keypress(function(event)
	{
		if ( event.which == 13 )
		{
			$('#finish_sale_form').submit();
		}
	});

	dialog_support.init("a.modal-dlg, button.modal-dlg");

	table_support.handle_submit = function(resource, response, stay_open)
	{
		debugger;;
		if(response.success) {
			if (resource.match(/customers$/))
			{
				$("#customer").val(response.id);
				$("#select_customer_form").submit();
			}
			else
			{
				var $stock_location = $("select[name='stock_location']").val();
				$("#item_location").val($stock_location);
				$("#item").val(response.id);
				if (stay_open)
				{
					$("#add_item_form").ajaxSubmit();
				}
				else
				{
					$("#add_item_form").submit();
				}
			}
		}
	}
});

function check_payment_type_giftcard()
{
	if ($("#payment_types").val() == "<?php echo $this->lang->line('sales_giftcard'); ?>")
	{
		$("#amount_tendered_label").html("<?php echo $this->lang->line('sales_giftcard_number'); ?>");
		$("#amount_tendered").val('').focus();
	}
	else
	{
		$("#amount_tendered_label").html("<?php echo $this->lang->line('sales_amount_tendered'); ?>");
		$("#amount_tendered").val('<?php echo to_currency_no_money($amount_due); ?>');
	}
}

</script>

<?php $this->load->view("partial/footer"); ?>
