<?php $this->load->view("partial/header"); ?>

<div id="page_title" style="margin-bottom: 8px;"><?php echo $this->lang->line('sales_register'); ?></div>

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
	<?php echo form_open("sales/change_mode", array('id'=>'mode_form', 'class'=>'form-horizontal panel panel-default')); ?>
		<div class="panel-body form-group">
			<ul>
				<li class="float_left">
					<label class="control-label"><?php echo $this->lang->line('sales_mode'); ?></label>
				</li>
				<li class="float_left">
					<?php echo form_dropdown('mode', $modes, $mode, array('onchange'=>"$('#mode_form').submit();", 'class'=>'selectpicker show-menu-arrow', 'data-style'=>'btn-default btn-sm', 'data-width'=>'fit')); ?>
				</li>

				<?php
				if (count($stock_locations) > 1)
				{
				?>
					<li class="float_left">
						<label class="control-label"><?php echo $this->lang->line('sales_stock_location'); ?></label>
					</li>
					<li class="float_left">
						<?php echo form_dropdown('stock_location', $stock_locations, $stock_location, array('onchange'=>"$('#mode_form').submit();", 'class'=>'selectpicker show-menu-arrow', 'data-style'=>'btn-default btn-sm', 'data-width'=>'fit')); ?>
					</li>
				<?php
				}
				?>

				<li class="float_right">
					<?php echo anchor("sales/suspended", $this->lang->line('sales_suspended_sales'),
								array('class'=>'btn btn-default btn-sm modal-dlg none', 'id'=>'show_suspended_sales_button', 'title'=>$this->lang->line('sales_suspended_sales'))); ?>
				</li>
			
				<?php
				if ($this->Employee->has_grant('reports_sales', $this->session->userdata('person_id')))
				{
				?>
					<li class="float_right">
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
				<li class="float_left"><label for="item", class='control-label'><?php echo $this->lang->line('sales_find_or_scan_item_or_receipt'); ?></label></li>
				<li class="float_left">
					<?php echo form_input(array('name'=>'item', 'id'=>'item', 'class'=>'form-control input-sm', 'size'=>'50', 'tabindex'=>'1')); ?>
					<span class="ui-helper-hidden-accessible" role="status"></span>
				</li>

				<li class="float_right">
					<?php echo anchor("items/view/-1", $this->lang->line('sales_new_item'),
							array('class'=>'btn btn-info btn-sm modal-dlg modal-btn-new modal-btn-submit', 'id'=>'new_item_button', 'title'=>$this->lang->line('sales_new_item'))); ?>
				</li>
			</ul>
		</div>
	<?php echo form_close(); ?>

<!-- Sale Items List -->
	
	<table id="register">
		<thead>
			<tr>
				<th style="width: 10%;"><?php echo $this->lang->line('common_delete'); ?></th>
				<th style="width: 15%;"><?php echo $this->lang->line('sales_item_number'); ?></th>
				<th style="width: 25%;"><?php echo $this->lang->line('sales_item_name'); ?></th>
				<th style="width: 10%;"><?php echo $this->lang->line('sales_price'); ?></th>
				<th style="width: 10%;"><?php echo $this->lang->line('sales_quantity'); ?></th>
				<th style="width: 10%;"><?php echo $this->lang->line('sales_discount'); ?></th>
				<th style="width: 10%;"><?php echo $this->lang->line('sales_total'); ?></th>
				<th style="width: 10%;"><?php echo $this->lang->line('sales_edit'); ?></th>
			</tr>
		</thead>
		<tbody id="cart_contents">
			<?php
			if(count($cart)==0)
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

					echo form_open("sales/edit_item/$line", array('class'=>'form-horizontal'));
			?>
						<tr>
							<td><?php echo anchor("sales/delete_item/$line", '<span class="glyphicon glyphicon-trash"></span>');?></td>
							<td><?php echo $item['item_number']; ?></td>
							<td style="align: center;"><?php echo base64_decode($item['name']); ?><br /> [<?php echo $item['in_stock'] ?> in <?php echo $item['stock_name']; ?>]
								<?php echo form_hidden('location', $item['item_location']); ?>
							</td>

							<?php if ($items_module_allowed)
							{
							?>
								<td><?php echo form_input(array('name'=>'price', 'class'=>'form-control input-sm', 'value'=>$item['price']));?></td>
							<?php
							}
							else
							{
							?>
								<td><?php echo to_currency($item['price']); ?></td>
								<?php echo form_hidden('price',$item['price']); ?>
							<?php
							}
							?>

							<td>
							<?php
								if($item['is_serialized']==1)
								{
									echo $item['quantity'];
									echo form_hidden('quantity',$item['quantity']);
								}
								else
								{								
									echo form_input(array('name'=>'quantity', 'class'=>'form-control input-sm', 'value'=>$item['quantity'], 'tabindex'=>$tabindex));
								}
							?>
							</td>

							<td><?php echo form_input(array('name'=>'discount', 'class'=>'form-control input-sm', 'value'=>$item['discount']));?></td>
							<td><?php echo to_currency($item['price']*$item['quantity']-$item['price']*$item['quantity']*$item['discount']/100); ?></td>
							<td><?php echo form_submit(array('name'=>'edit_item', 'value'=>$this->lang->line('sales_edit_item'), 'class'=>'btn btn-default btn-xs'));?></td>
						</tr>
						<tr>
							<?php 
							if($item['allow_alt_description']==1)
							{
							?>
								<td style="color: #2F4F4F;"><?php echo $this->lang->line('sales_description_abbrv').':';?></td>
							<?php 
							}
							?>

							<td colspan='2' style="text-align: left;">
								<?php
								if($item['allow_alt_description']==1)
								{
									echo form_input(array('name'=>'description', 'class'=>'form-control input-sm', 'value'=>base64_decode($item['description'])));
								}
								else
								{
									if (base64_decode($item['description'])!='')
									{
										echo base64_decode($item['description']);
										echo form_hidden('description', base64_decode($item['description']));
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
									echo $this->lang->line('sales_serial').':';
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
					$tabindex = $tabindex + 1;					
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
			echo '<label class="control-label">' . $this->lang->line("sales_customer") . ': <b>' . $customer . '</b></label><br />';
			echo anchor("sales/remove_customer",'['.$this->lang->line('common_remove').' '.$this->lang->line('customers_customer').']');
		}
		else
		{
			echo form_open("sales/select_customer", array('id'=>'select_customer_form', 'class'=>'form-horizontal'));
		?>
				<div class="form-group" style="margin: 0">
					<label id="customer_label" for="customer" class="control-label" style="margin-bottom: 1em;"><?php echo $this->lang->line('sales_select_customer'); ?></label>
					<?php echo form_input(array('name'=>'customer', 'id'=>'customer', 'class'=>'form-control input-sm', 'value'=>$this->lang->line('sales_start_typing_customer_name')));?>
				</div>
			<?php echo form_close(); ?>

			<h4 style="margin-top: 0.4em; margin-bottom: 0;"><?php echo $this->lang->line('common_or'); ?></h4>
			
			<?php echo anchor("customers/view/-1", $this->lang->line('sales_new_customer'),
						array('class'=>'btn btn-info btn-sm modal-dlg modal-btn-submit none', 'id'=>'new_customer_button', 'title'=>$this->lang->line('sales_new_customer'))); ?>
		<?php
		}
		?>

		<table id="sale_details">
			<tr>
				<th style="width: 55%;"><?php echo $this->lang->line('sales_sub_total'); ?>:</th>
				<th style="width: 45%; text-align: right;"><?php echo to_currency($this->config->item('tax_included') ? $tax_exclusive_subtotal : $subtotal); ?></th>
			</tr>
			
			<?php
			foreach($taxes as $name=>$value)
			{
			?>
				<tr>
					<th style='width: 55%;'><?php echo $name; ?>:</th>
					<th style="width: 45%; text-align: right;"><?php echo to_currency($value); ?></th>
				</tr>
			<?php
			}
			?>

			<tr>
				<th style='width: 55%;'><?php echo $this->lang->line('sales_total'); ?>:</th>
				<th style="width: 45%; text-align: right;"><?php echo to_currency($total); ?></th>
			</tr>
		</table>
	
		<?php
		// Only show this part if there are Items already in the sale.
		if(count($cart) > 0)
		{
		?>
			<?php echo form_open("sales/cancel_sale", array('id'=>'cancel_sale_form', 'class'=>'form-horizontal')); ?>
				<div id="cancel_sale">
					<div class='btn btn-sm btn-danger pull-left' id='cancel_sale_button'><?php echo $this->lang->line('sales_cancel_sale'); ?></div>
					
					<div class='btn btn-sm btn-default pull-right' id='suspend_sale_button'><?php echo $this->lang->line('sales_suspend_sale'); ?></div>
				</div>
			<?php echo form_close(); ?>

			<?php
			// Only show this part if there is at least one payment entered.
			if(count($payments) > 0)
			{
				echo form_open("sales/complete", array('id'=>'finish_sale_form', 'class'=>'form-horizontal'));
			?>
					<div id="finish_sale">
						<label id="comment_label" for="comment"><?php echo $this->lang->line('common_comments'); ?>:</label>
						<?php echo form_textarea(array('name'=>'comment', 'id'=>'comment', 'class'=>'form-control input-sm', 'value'=>$comment, 'rows'=>'4', 'cols'=>'23')); ?>
						<?php
						if(!empty($customer_email))
						{
							echo $this->lang->line('sales_email_receipt'). ': '
								. form_checkbox(array(
								'name'    => 'email_receipt',
								'id'      => 'email_receipt',
								'value'   => '1',
								'checked' => (boolean)$email_receipt,
								)).'<br />('.$customer_email.')<br />';
						}
						 
						if ($payments_cover_total)
						{					
							echo "<div class='btn btn-sm btn-success pull-right' id='finish_sale_button' tabindex='3'><span>".$this->lang->line('sales_complete_sale')."</span></div>";
						}
						?>
					</div>
			<?php 
				echo form_close();
			}
			?>

			<table width="100%">
				<tr>
					<th style="width: 55%;"><?php echo $this->lang->line('sales_payments_total').':';?></th>
					<th style="width: 45%; text-align: right;"><?php echo to_currency($payments_total); ?></th>
				</tr>
				<tr>
					<th style="width: 55%;"><?php echo $this->lang->line('sales_amount_due').':';?></th>
					<th style="width: 45%; text-align: right;"><?php echo to_currency($amount_due); ?></th>
				</tr>
			</table>

			<div id="payment_details" class="panel-footer">
				<div>
					<?php echo form_open("sales/add_payment", array('id'=>'add_payment_form', 'class'=>'form-horizontal')); ?>
						<table width="100%">
							<tr>
								<td><?php echo $this->lang->line('sales_print_after_sale'); ?></td>
								<td>
									<?php
									if ($print_after_sale)
									{
										echo form_checkbox(array('name'=>'sales_print_after_sale', 'id'=>'sales_print_after_sale', 'class'=>'checkbox', 'checked'=>'checked'));
									}
									else
									{
										echo form_checkbox(array('name'=>'sales_print_after_sale', 'id'=>'sales_print_after_sale', 'class'=>'checkbox'));
									}
									?>
								</td>
							</tr>
							<?php
							if ($mode == "sale") 
							{
							?>
							<tr>
								<td><?php echo $this->lang->line('sales_invoice_enable'); ?></td>
								<td>
									<?php if ($invoice_number_enabled)
									{
										echo form_checkbox(array('name'=>'sales_invoice_enable', 'id'=>'sales_invoice_enable', 'class'=>'checkbox', 'checked'=>'checked'));
									}
									else
									{
										echo form_checkbox(array('name'=>'sales_invoice_enable', 'id'=>'sales_invoice_enable', 'class'=>'checkbox'));
									}
									?>
								</td>
							</tr>
							<tr>
								<td><?php echo $this->lang->line('sales_invoice_number').':   ';?></td>
								<td>
									<?php echo form_input(array('name'=>'sales_invoice_number', 'id'=>'sales_invoice_number', 'class'=>'form-control input-sm', 'value'=>$invoice_number, 'size'=>10));?>
								</td>
							</tr>
							<?php 
							}
							?>
							<tr>
								<td><?php echo $this->lang->line('sales_payment').':   ';?></td>
								<td>
									<?php echo form_dropdown('payment_type', $payment_options, array(), array('id'=>'payment_types', 'class'=>'form-control input-sm')); ?>
								</td>
							</tr>
							<tr>
								<td><span id="amount_tendered_label"><?php echo $this->lang->line('sales_amount_tendered') . ': '; ?></span></td>
								<td>
									<?php echo form_input(array('name'=>'amount_tendered', 'id'=>'amount_tendered', 'class'=>'form-control input-sm', 'value'=>to_currency_no_money($amount_due), 'size'=>'10', 'tabindex'=>4)); ?>
								</td>
							</tr>
						</table>
						
						<div class='btn btn-sm btn-success pull-right' id='add_payment_button'><?php echo $this->lang->line('sales_add_payment'); ?></div>
					<?php echo form_close(); ?>
				</div>

				<?php
				// Only show this part if there is at least one payment entered.
				if(count($payments) > 0)
				{
				?>
					<table id="register">
						<thead>
							<tr>
								<th style="width: 11%;"><?php echo $this->lang->line('common_delete'); ?></th>
								<th style="width: 60%;"><?php echo $this->lang->line('sales_payment_type'); ?></th>
								<th style="width: 18%;"><?php echo $this->lang->line('sales_payment_amount'); ?></th>
							</tr>
						</thead>
			
						<tbody id="payment_contents">
							<?php
							foreach($payments as $payment_id=>$payment)
							{
								echo form_open("sales/edit_payment/$payment_id", array('id'=>'edit_payment_form'.$payment_id));
								?>
									<tr>
										<td><?php echo anchor( "sales/delete_payment/$payment_id", '<span class="glyphicon glyphicon-trash"></span>' ); ?></td>
										<td><?php echo $payment['payment_type']; ?></td>
										<td style="text-align: right;"><?php echo to_currency( $payment['payment_amount'] ); ?></td>
									</tr>
								<?php 
								echo form_close();
							}
							?>
						</tbody>
					</table>
					<br />
				<?php
				}
				?>
			</div>
		<?php
		}
		?>
	</div>
</div>

<div class="clearfix" style="margin-bottom: 30px;">&nbsp;</div>

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

	$('#item, #customer').click(clear_fields).dblclick(function(event) {
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

	$("#sales_print_after_sale").change(function() {
		$.post('<?php echo site_url("sales/set_print_after_sale");?>', {sales_print_after_sale: $(this).is(":checked")});
	});
	
	$("#sales_invoice_enable").change(function() {
		var enabled = enable_invoice_number();
		$.post('<?php echo site_url("sales/set_invoice_number_enabled");?>', {sales_invoice_number_enabled: enabled});
	});
	
	$('#email_receipt').change(function() 
	{
		$.post('<?php echo site_url("sales/set_email_receipt");?>', {email_receipt: $('#email_receipt').is(':checked') ? '1' : '0'});
	});
	
	
    $("#finish_sale_button").click(function()
    {
    	if (confirm('<?php echo $this->lang->line("sales_confirm_finish_sale"); ?>'))
    	{
    		$('#finish_sale_form').submit();
    	}
    });

	$("#suspend_sale_button").click(function()
	{ 	
		if (confirm('<?php echo $this->lang->line("sales_confirm_suspend_sale"); ?>'))
    	{
			$('#cancel_sale_form').attr('action', '<?php echo site_url("sales/suspend"); ?>');
    		$('#cancel_sale_form').submit();
    	}
	});

    $("#cancel_sale_button").click(function()
    {
    	if (confirm('<?php echo $this->lang->line("sales_confirm_cancel_sale"); ?>'))
    	{
    		$('#cancel_sale_form').submit();
    	}
    });

	$("#add_payment_button").click(function()
	{
	   $('#add_payment_form').submit();
    });

	$("#payment_types").change(check_payment_type_gifcard).ready(check_payment_type_gifcard)
	
	$("#amount_tendered").keyup(function(event){
		if(event.which == 13) {
			$('#add_payment_form').submit();
		}
	});	
	
    $("#finish_sale_button").keypress(function( event ) {
		if ( event.which == 13 ) {
			if (confirm('<?php echo $this->lang->line("sales_confirm_finish_sale"); ?>'))
			{
				$('#finish_sale_form').submit();
			}
		}
	});	    
});

function post_item_form_submit(response, stay_open)
{
	if(response.success)
	{
        var $stock_location = $("select[name='stock_location']").val();
        $("#item_location").val($stock_location);
		$("#item").val(response.item_id);
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

function post_person_form_submit(response)
{
	if(response.success)
	{
		$("#customer").val(response.person_id);
		$("#select_customer_form").submit();
	}
}

function check_payment_type_gifcard()
{
	if ($("#payment_types").val() == "<?php echo $this->lang->line('sales_giftcard'); ?>")
	{
		$("#amount_tendered_label").html("<?php echo $this->lang->line('sales_giftcard_number'); ?>");
		$("#amount_tendered").val('').focus();
	}
	else
	{
		$("#amount_tendered_label").html("<?php echo $this->lang->line('sales_amount_tendered'); ?>");
		$("#amount_tendered").val('<?php echo $amount_due; ?>');
	}
}

</script>

<?php $this->load->view("partial/footer"); ?>
