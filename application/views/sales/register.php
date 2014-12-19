<?php $this->load->view("partial/header"); ?>
<div id="page_title" style="margin-bottom: 8px;"><?php echo $this->lang->line('sales_register'); ?></div>
<?php
if(isset($error))
{
	echo "<div class='error_message'>".$error."</div>";
}

if (isset($warning))
{
	echo "<div class='warning_mesage'>".$warning."</div>";
}

if (isset($success))
{
	echo "<div class='success_message'>".$success."</div>";
}
?>
<div id="register_wrapper">
<?php echo form_open("sales/change_mode",array('id'=>'mode_form')); ?>
	<span><?php echo $this->lang->line('sales_mode') ?></span>
<?php echo form_dropdown('mode',$modes,$mode,'onchange="$(\'#mode_form\').submit();"'); ?>
<?php if (count($stock_locations) > 1): ?>
<span><?php echo $this->lang->line('sales_stock_location') ?></span>
<?php echo form_dropdown('stock_location',$stock_locations,$stock_location,'onchange="$(\'#mode_form\').submit();"'); ?>
<?php endif; ?>
<div id="show_suspended_sales_button">
	<?php echo anchor("sales/suspended/width:425",
	"<div class='small_button'><span style='font-size:73%;'>".$this->lang->line('sales_suspended_sales')."</span></div>",
	array('class'=>'thickbox none','title'=>$this->lang->line('sales_suspended_sales')));
	?>
</div>
	</form>
<?php echo form_open("sales/add",array('id'=>'add_item_form')); ?>
<label id="item_label" for="item">

<?php
echo $this->lang->line('sales_find_or_scan_item_or_receipt');
?>
</label>

<?php echo form_input(array('name'=>'item','id'=>'item','size'=>'40'));?>
<div id="new_item_button_register" >
		<?php echo anchor("items/view/-1/width:360",
		"<div class='small_button'><span>".$this->lang->line('sales_new_item')."</span></div>",
		array('class'=>'thickbox none','title'=>$this->lang->line('sales_new_item')));
		?>
	</div>
	</form>
	<table id="register">
		<thead>
			<tr>
				<th style="width: 11%;"><?php echo $this->lang->line('common_delete'); ?></th>
				<th style="width: 30%;"><?php echo $this->lang->line('sales_item_number'); ?></th>
				<th style="width: 30%;"><?php echo $this->lang->line('sales_item_name'); ?></th>
				<th style="width: 11%;"><?php echo $this->lang->line('sales_price'); ?></th>
				<th style="width: 11%;"><?php echo $this->lang->line('sales_quantity'); ?></th>
				<th style="width: 11%;"><?php echo $this->lang->line('sales_discount'); ?></th>
				<th style="width: 15%;"><?php echo $this->lang->line('sales_total'); ?></th>
				<th style="width: 11%;"><?php echo $this->lang->line('sales_edit'); ?></th>
			</tr>
		</thead>
		<tbody id="cart_contents">
<?php
if(count($cart)==0)
{
?>
            <tr>
				<td colspan='8'>
					<div class='warning_message' style='padding: 7px;'><?php echo $this->lang->line('sales_no_items_in_cart'); ?></div>
				</td>
			</tr>
<?php
}
else
{
	foreach(array_reverse($cart, true) as $line=>$item)
	{
		echo form_open("sales/edit_item/$line");
	?>
		<tr>
				<td><?php echo anchor("sales/delete_item/$line",'['.$this->lang->line('common_delete').']');?></td>
				<td><?php echo $item['item_number']; ?></td>
				<td style="align: center;"><?php echo $item['name']; ?><br /> [<?php echo $item['in_stock'] ?> in <?php echo $item['stock_name']; ?>]
				<?php echo form_hidden('location', $item['item_location']); ?>
				</td>


		<?php if ($items_module_allowed)
		{
		?>
			<td><?php echo form_input(array('name'=>'price','value'=>$item['price'],'size'=>'6'));?></td>

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
        		echo form_input(array('name'=>'quantity','value'=>$item['quantity'],'size'=>'2'));
        	}
		?>
		</td>

			<td><?php echo form_input(array('name'=>'discount','value'=>$item['discount'],'size'=>'3'));?></td>
			<td><?php echo to_currency($item['price']*$item['quantity']-$item['price']*$item['quantity']*$item['discount']/100); ?></td>
			<td><?php echo form_submit("edit_item", $this->lang->line('sales_edit_item'));?></td>
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
			<td colspan=2 style="text-align: left;">
			<?php
	        	if($item['allow_alt_description']==1)
	        	{
	        		echo form_input(array('name'=>'description','value'=>$item['description'],'size'=>'20'));
	        	}
	        	else
	        	{
					if ($item['description']!='')
					{
						echo $item['description'];
	        			echo form_hidden('description',$item['description']);
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
			<td colspan="4" style="text-align: left;">
			<?php
	        	if($item['is_serialized']==1)
	        	{
	        		echo form_input(array('name'=>'serialnumber','value'=>$item['serialnumber'],'size'=>'20'));
				}
				else
				{
					echo form_hidden('serialnumber', '');
				}
			?>
			</td>
		</tr>
		<tr style="height: 3px">
			<td colspan=8 style="background-color: white"></td>
		</tr>
	</form>
	<?php
	}
}
?>
</tbody>
	</table>
</div>


<div id="overall_sale">
	<?php
	if(isset($customer))
	{
		echo $this->lang->line("sales_customer").': <b>'.$customer. '</b><br />';
		echo anchor("sales/remove_customer",'['.$this->lang->line('common_remove').' '.$this->lang->line('customers_customer').']');
	}
	else
	{
		echo form_open("sales/select_customer",array('id'=>'select_customer_form')); ?>
		<label id="customer_label" for="customer"><?php echo $this->lang->line('sales_select_customer'); ?></label>
		<?php echo form_input(array('name'=>'customer','id'=>'customer','size'=>'30','value'=>$this->lang->line('sales_start_typing_customer_name')));?>
		</form>
	<div style="margin-top: 5px; text-align: center;">
		<h3 style="margin: 5px 0 5px 0"><?php echo $this->lang->line('common_or'); ?></h3>
		<?php echo anchor("customers/view/-1/width:350",
		"<div class='small_button' style='margin:0 auto;'><span>".$this->lang->line('sales_new_customer')."</span></div>",
		array('class'=>'thickbox none','title'=>$this->lang->line('sales_new_customer')));
		?>
		</div>
		
	<div class="clearfix">&nbsp;</div>
		<?php
	}
	?>

	<div id='sale_details'>
		<div class="float_left" style="width: 55%;"><?php echo $this->lang->line('sales_sub_total'); ?>:</div>
		<div class="float_left" style="width: 45%; font-weight: bold;"><?php echo to_currency($subtotal); ?></div>

		<?php foreach($taxes as $name=>$value) { ?>
		<div class="float_left" style='width: 55%;'><?php echo $name; ?>:</div>
		<div class="float_left" style="width: 45%; font-weight: bold;"><?php echo to_currency($value); ?></div>
		<?php }; ?>

		<div class="float_left" style='width: 55%;'><?php echo $this->lang->line('sales_total'); ?>:</div>
		<div class="float_left" style="width: 45%; font-weight: bold;"><?php echo to_currency($total); ?></div>
	</div>




	<?php
	// Only show this part if there are Items already in the sale.
	if(count($cart) > 0)
	{
	?>

    	<div id="Cancel_sale">
		<?php echo form_open("sales/cancel_sale",array('id'=>'cancel_sale_form')); ?>
		<div class='small_button' id='cancel_sale_button'
			style='margin-top: 5px;'>
			<span><?php echo $this->lang->line('sales_cancel_sale'); ?></span>
		</div>
		</form>
	</div>
	<div class="clearfix" style="margin-bottom: 1px;">&nbsp;</div>
		<?php
		// Only show this part if there is at least one payment entered.
		if(count($payments) > 0)
		{
		?>
			<div id="finish_sale">
				<?php echo form_open("sales/complete",array('id'=>'finish_sale_form')); ?>
				<label id="comment_label" for="comment"><?php echo $this->lang->line('common_comments'); ?>:</label>
				<?php echo form_textarea(array('name'=>'comment', 'id' => 'comment', 'value'=>$comment,'rows'=>'4','cols'=>'23'));?>
				<br />
		<br />
				
				<?php
				
				if(!empty($customer_email))
				{
					echo $this->lang->line('sales_email_receipt'). ': '. form_checkbox(array(
					    'name'        => 'email_receipt',
					    'id'          => 'email_receipt',
					    'value'       => '1',
					    'checked'     => (boolean)$email_receipt,
					    )).'<br />('.$customer_email.')<br />';
				}
				 
				if ($payments_cover_total)
				{
					echo "<div class='small_button' id='finish_sale_button' style='float:left;margin-top:5px;'><span>".$this->lang->line('sales_complete_sale')."</span></div>";
				}
				echo "<div class='small_button' id='suspend_sale_button' style='float:right;margin-top:5px;'><span>".$this->lang->line('sales_suspend_sale')."</span></div>";
				?>
			</div>
	</form>
		<?php
		}
		?>



    <table width="100%">
		<tr>
			<td style="width: 55%;"><div class="float_left"><?php echo $this->lang->line('sales_payments_total').':';?></div></td>
			<td style="width: 45%; text-align: right;"><div class="float_left"
					style="text-align: right; font-weight: bold;"><?php echo to_currency($payments_total); ?></div></td>
		</tr>
		<tr>
			<td style="width: 55%;"><div class="float_left"><?php echo $this->lang->line('sales_amount_due').':';?></div></td>
			<td style="width: 45%; text-align: right;"><div class="float_left"
					style="text-align: right; font-weight: bold;"><?php echo to_currency($amount_due); ?></div></td>
		</tr>
	</table>

	<div id="payment_details">

		<div>

			<?php echo form_open("sales/add_payment",array('id'=>'add_payment_form')); ?>
			<table width="100%">
				<?php if ($mode == "sale") 
				{
				?>
				<tr>
					<td>
						<?php echo $this->lang->line('sales_invoice_enable'); ?>
					</td>
					<td>
						<?php if ($invoice_number_enabled)
						{
							echo form_checkbox(array('name'=>'sales_invoice_enable','id'=>'sales_invoice_enable','size'=>10,'checked'=>'checked'));
						}
						else
						{
							echo form_checkbox(array('name'=>'sales_invoice_enable','id'=>'sales_invoice_enable','size'=>10));
						}
						?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo $this->lang->line('sales_invoice_number').':   ';?>
					</td>
					<td>
						<?php echo form_input(array('name'=>'sales_invoice_number','id'=>'sales_invoice_number','value'=>$invoice_number,'size'=>10));?>
					</td>
				</tr>
				<?php 
				}
				?>
				<tr>
					<td>
					<?php echo $this->lang->line('sales_payment').':   ';?>
					</td>
					<td>
					<?php echo form_dropdown( 'payment_type', $payment_options, array(), 'id="payment_types"' ); ?>
					</td>
				</tr>
				<tr>
					<td><span id="amount_tendered_label"><?php echo $this->lang->line( 'sales_amount_tendered' ).': '; ?></span>
					</td>
					<td>
				<?php echo form_input( array( 'name'=>'amount_tendered', 'id'=>'amount_tendered', 'value'=>to_currency_no_money($amount_due), 'size'=>'10' ) );	?>
			</td>
				</tr>
			</table>
			<div class='small_button' id='add_payment_button'
				style='float: left; margin-top: 5px;'>
				<span><?php echo $this->lang->line('sales_add_payment'); ?></span>
			</div>
		</form>
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
				echo form_open("sales/edit_payment/$payment_id",array('id'=>'edit_payment_form'.$payment_id));
				?>
	            <tr>
					<td><?php echo anchor( "sales/delete_payment/$payment_id", '['.$this->lang->line('common_delete').']' ); ?></td>

					<td><?php echo $payment['payment_type']; ?></td>
					<td style="text-align: right;"><?php echo to_currency( $payment['payment_amount'] ); ?></td>


				</tr>
				</form>
				<?php
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
<div class="clearfix" style="margin-bottom: 30px;">&nbsp;</div>

<script type="text/javascript" language="javascript">
$(document).ready(function()
{
    $("#item").autocomplete('<?php echo site_url("sales/item_search"); ?>',
    {
    	minChars:0,
    	max:100,
    	selectFirst: false,
       	delay:10,
    	formatItem: function(row) {
			return row[1];
		}
    });

    $("#item").result(function(event, data, formatted)
    {
		$("#add_item_form").submit();
    });

    $('#item').blur(function()
    {
        $(this).val("<?php echo $this->lang->line('sales_start_typing_item_name'); ?>");
    });

    $('#item, #customer').focus(function()
    {
        if ($(this).val().match("<?php echo $this->lang->line('sales_start_typing_item_name') . '|' . 
        	$this->lang->line('sales_start_typing_customer_name'); ?>"))
        {
            $(this).val('');
        }
    });

    $("#customer").autocomplete('<?php echo site_url("sales/customer_search"); ?>',
    {
    	minChars:0,
    	delay:10,
    	max:100,
    	formatItem: function(row) {
			return row[1];
		}
    });

    $("#customer").result(function(event, data, formatted)
    {
		$("#select_customer_form").submit();
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
		if (enabled)
		{
			$("#sales_invoice_number").removeAttr("disabled").parents('tr').show();
		}
		else
		{
			$("#sales_invoice_number").attr("disabled", "disabled").parents('tr').hide();
		}
		return enabled;
	}

	enable_invoice_number();
	
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
			$('#finish_sale_form').attr('action', '<?php echo site_url("sales/suspend"); ?>');
    		$('#finish_sale_form').submit();
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
});

function post_item_form_submit(response)
{
	if(response.success)
	{
        var $stock_location = $("select[name='stock_location']").val();
        $("#item_location").val($stock_location);
		$("#item").val(response.item_id);
		$("#add_item_form").submit();
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
		$("#amount_tendered").val('');
		$("#amount_tendered").focus();
	}
	else
	{
		$("#amount_tendered_label").html("<?php echo $this->lang->line('sales_amount_tendered'); ?>");		
	}
}

</script>
<?php $this->load->view("partial/footer"); ?>