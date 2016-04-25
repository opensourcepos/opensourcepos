<?php $this->load->view("partial/header"); ?>

<?php
if (isset($error))
{
	echo "<div class='alert alert-dismissible alert-danger'>".$error."</div>";
}
?>

<div id="register_wrapper">

<!-- Top register controls -->

	<?php echo form_open("receivings/change_mode", array('id'=>'mode_form', 'class'=>'form-horizontal panel panel-default')); ?>
		<div class="panel-body form-group">
			<ul>
				<li class="pull-left first_li">
					<label class="control-label"><?php echo $this->lang->line('recvs_mode'); ?></label>
				</li>
				<li class="pull-left">
					<?php echo form_dropdown('mode', $modes, $mode, array('onchange'=>"$('#mode_form').submit();", 'class'=>'selectpicker show-menu-arrow', 'data-style'=>'btn-default btn-sm', 'data-width'=>'fit')); ?>
				</li>

				<?php 
				if ($show_stock_locations)
				{
				?>
					<li class="pull-left">
						<label class="control-label"><?php echo $this->lang->line('recvs_stock_source'); ?></label>
					</li>
					<li class="pull-left">
						<?php echo form_dropdown('stock_source', $stock_locations, $stock_source, array('onchange'=>"$('#mode_form').submit();", 'class'=>'selectpicker show-menu-arrow', 'data-style'=>'btn-default btn-sm', 'data-width'=>'fit')); ?>
					</li>
					
					<?php
					if($mode=='requisition')
					{
					?>
						<li class="pull-left">
							<label class="control-label"><?php echo $this->lang->line('recvs_stock_destination'); ?></label>
						</li>
						<li class="pull-left">
							<?php echo form_dropdown('stock_destination', $stock_locations, $stock_destination, array('onchange'=>"$('#mode_form').submit();", 'class'=>'selectpicker show-menu-arrow', 'data-style'=>'btn-default btn-sm', 'data-width'=>'fit')); ?>
						</li>
				<?php
					}
				}
				?>
			</ul>
		</div>
	<?php echo form_close(); ?>

	<?php echo form_open("receivings/add", array('id'=>'add_item_form', 'class'=>'form-horizontal panel panel-default')); ?>
		<div class="panel-body form-group">
			<ul>
				<li class="pull-left first_li">
					<label for="item", class='control-label'>
						<?php
						if($mode=='receive' or $mode=='requisition')
						{
						?>
							<?php echo $this->lang->line('recvs_find_or_scan_item'); ?>
						<?php
						}
						else
						{
						?>
							<?php echo $this->lang->line('recvs_find_or_scan_item_or_receipt'); ?>
						<?php
						}
						?>			
					</label>
				</li>
				<li class="pull-left">
					<?php echo form_input(array('name'=>'item', 'id'=>'item', 'class'=>'form-control input-sm', 'size'=>'50', 'tabindex'=>'1')); ?>
				</li>
				<li class="pull-right">
					<button id='new_item_button' class='btn btn-info btn-sm pull-right modal-dlg modal-btn-submit' data-href='<?php echo site_url("items/view"); ?>'
						title='<?php echo $this->lang->line('sales_new_item'); ?>'>
						<span class="glyphicon glyphicon-tag"></span><?php echo $this->lang->line('sales_new_item'); ?>
					</button>
				</li>
			</ul>
		</div>
	<?php echo form_close(); ?>
	
<!-- Receiving Items List -->

	<table class="sales_table_100" id="register">
		<thead>
			<tr>
				<th style="width:5%;"><?php echo $this->lang->line('common_delete'); ?></th>
				<th style="width:45%;"><?php echo $this->lang->line('recvs_item_name'); ?></th>
				<th style="width:10%;"><?php echo $this->lang->line('recvs_cost'); ?></th>
				<th style="width:10%;"><?php echo $this->lang->line('recvs_quantity'); ?></th>
				<th style="width:5%;"></th>
				<th style="width:10%;"><?php echo $this->lang->line('recvs_discount'); ?></th>
				<th style="width:10%;"><?php echo $this->lang->line('recvs_total'); ?></th>
				<th style="width:5%;"><?php echo $this->lang->line('recvs_update'); ?></th>
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
				foreach(array_reverse($cart, true) as $line=>$item)
				{
			?>
					<?php echo form_open("receivings/edit_item/$line", array('class'=>'form-horizontal', 'id'=>'cart_'.$line)); ?>
						<tr>
							<td><?php echo anchor("receivings/delete_item/$line", '<span class="glyphicon glyphicon-trash"></span>');?></td>
							<td style="align:center;">
								<?php echo $item['name']; ?><br /> <?php echo '[' . to_quantity_decimals($item['in_stock']) . ' in ' . $item['stock_name'] . ']'; ?>
								<?php echo form_hidden('location', $item['item_location']); ?>
							</td>

							<?php 
							if ($items_module_allowed && $mode !='requisition')
							{
							?>
								<td><?php echo form_input(array('name'=>'price', 'class'=>'form-control input-sm', 'value'=>to_currency_no_money($item['price'])));?></td>
							<?php
							}
							else
							{
							?>
								<td>
									<?php echo $item['price']; ?>
									<?php echo form_hidden('price', $item['price']); ?>
								</td>
							<?php
							}
							?>
							
							<td><?php echo form_input(array('name'=>'quantity', 'class'=>'form-control input-sm', 'value'=>to_quantity_decimals($item['quantity']))); ?></td>
							<?php
							if ($item['receiving_quantity'] > 1) 
							{
							?>
								<td><?php echo 'x'.to_quantity_decimals($item['receiving_quantity']); ?></td>	
							<?php 
							}
							else
							{
							?>
								<td></td>
							<?php 
							}
							?>
						
							<?php       
							if ($items_module_allowed && $mode!='requisition')
							{
							?>
								<td><?php echo form_input(array('name'=>'discount', 'class'=>'form-control input-sm', 'value'=>$item['discount']));?></td>
							<?php
							}
							else
							{
							?>
								<td><?php echo $item['discount']; ?></td>
								<?php echo form_hidden('discount',$item['discount']); ?>
							<?php
							}
							?>
							<td><?php echo to_currency($item['price']*$item['quantity']-$item['price']*$item['quantity']*$item['discount']/100); ?></td>
							<td><a href="javascript:document.getElementById('<?php echo 'cart_'.$line ?>').submit();" title=<?php echo $this->lang->line('recvs_update')?> ><span class="glyphicon glyphicon-refresh"></span></a></td>
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
									echo form_input(array('name'=>'description', 'class'=>'form-control input-sm', 'value'=>$item['description']));
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
							<td colspan='6'></td>
						</tr>
					<?php echo form_close(); ?>
			<?php
				}
			}
			?>
		</tbody>
	</table>
</div>

<!-- Overall Receiving -->

<div id="overall_sale" class="panel panel-default">
	<div class="panel-body">
		<?php
		if(isset($supplier))
		{
			echo $this->lang->line("recvs_supplier").': <b>'.$supplier. '</b><br />';
			echo anchor("receivings/delete_supplier",'['.$this->lang->line('common_delete').' '.$this->lang->line('suppliers_supplier').']');
		}
		else
		{
		?>
			<?php echo form_open("receivings/select_supplier", array('id'=>'select_supplier_form', 'class'=>'form-horizontal')); ?>
				<div class="form-group" id="select_customer">
					<label id="supplier_label" for="supplier" class="control-label" style="margin-bottom: 1em; margin-top: -1em;"><?php echo $this->lang->line('recvs_select_supplier'); ?></label>
					<?php echo form_input(array('name'=>'supplier', 'id'=>'supplier', 'class'=>'form-control input-sm', 'value'=>$this->lang->line('recvs_start_typing_supplier_name'))); ?>

					<button id='new_supplier_button' class='btn btn-info btn-sm modal-dlg modal-btn-submit' data-href='<?php echo site_url("suppliers/view"); ?>'
							title='<?php echo $this->lang->line('recvs_new_supplier'); ?>'>
						<span class="glyphicon glyphicon-user"></span><?php echo $this->lang->line('recvs_new_supplier'); ?>
					</button>

				</div>
			<?php echo form_close(); ?>
		<?php
		}
		?>

		<table class="sales_table_100" id="sale_totals">
			<tr>
				<?php
				if($mode != 'requisition')
				{
				?>
					<th style="width: 55%;"><?php echo $this->lang->line('sales_total'); ?></th>
					<th style="width: 45%; text-align: right;"><?php echo to_currency($total); ?></th>
				<?php 
				}
				else
				{
				?>
					<th style="width: 55%;"></th>
					<th style="width: 45%; text-align: right;"></th>
				<?php 
				}
				?>
			</tr>
		</table>

		<?php
		if(count($cart) > 0)
		{
		?>
			<div id="finish_sale">
				<?php
				if($mode == 'requisition')
				{
				?>
					<?php echo form_open("receivings/requisition_complete", array('id'=>'finish_receiving_form', 'class'=>'form-horizontal')); ?>
						<div class="form-group form-group-sm">
							<label id="comment_label" for="comment"><?php echo $this->lang->line('common_comments'); ?></label>
							<?php echo form_textarea(array('name'=>'comment', 'id'=>'comment', 'class'=>'form-control input-sm', 'value'=>$comment, 'rows'=>'4')); ?>

							<div class="btn btn-sm btn-danger pull-left" id='cancel_receiving_button'><?php echo $this->lang->line('recvs_cancel_receiving'); ?></div>
							
							<div class="btn btn-sm btn-success pull-right" id='finish_receiving_button'><?php echo $this->lang->line('recvs_complete_receiving'); ?></div>
						</div>
					<?php echo form_close(); ?>
				<?php
				}
				else
				{
				?>
					<?php echo form_open("receivings/complete", array('id'=>'finish_receiving_form', 'class'=>'form-horizontal')); ?>
						<div class="form-group form-group-sm">
							<label id="comment_label" for="comment"><?php echo $this->lang->line('common_comments'); ?></label>
							<?php echo form_textarea(array('name'=>'comment', 'id'=>'comment', 'class'=>'form-control input-sm', 'value'=>$comment, 'rows'=>'4'));?>

							<table class="sales_table_100" id="payment_details">
								<tr>
									<td><?php echo $this->lang->line('recvs_print_after_sale'); ?></td>
									<td>
										<?php echo form_checkbox(array('name'=>'recv_print_after_sale', 'id'=>'recv_print_after_sale', 'class'=>'checkbox', 'value'=>1, 'checked'=>$print_after_sale)); ?>
									</td>
								</tr>

								<?php
								if ($mode == "receive" && $this->config->item('invoice_enable') == TRUE) 
								{
								?>
									<tr>
										<td><?php echo $this->lang->line('recvs_invoice_enable'); ?></td>
										<td>
											<?php echo form_checkbox(array('name'=>'recv_invoice_enable', 'id'=>'recv_invoice_enable', 'class'=>'checkbox', 'value'=>1, 'checked'=>$invoice_number_enabled)); ?>
										</td>
									</tr>
									<tr>
										<td><?php echo $this->lang->line('recvs_invoice_number');?></td>
										<td>
											<?php echo form_input(array('name'=>'recv_invoice_number', 'id'=>'recv_invoice_number', 'class'=>'form-control input-sm', 'value'=>$invoice_number, 'size'=>5));?>
										</td>
									</tr>
								<?php 
								}
								?>
								<tr>
									<td><?php echo $this->lang->line('sales_payment'); ?></td>
									<td>
										<?php echo form_dropdown('payment_type', $payment_options, array(), array('id'=>'payment_types', 'class'=>'selectpicker show-menu-arrow', 'data-style'=>'btn-default btn-sm', 'data-width'=>'auto')); ?>
									</td>
								</tr>

								<tr>
									<td><?php echo $this->lang->line('sales_amount_tendered'); ?></td>
									<td>
										<?php echo form_input(array('name'=>'amount_tendered', 'value'=>'', 'class'=>'form-control input-sm', 'size'=>'5')); ?>
									</td>
								</tr>
							</table>

							<div class='btn btn-sm btn-danger pull-left' id='cancel_receiving_button'><?php echo $this->lang->line('recvs_cancel_receiving') ?></div>
							
							<div class='btn btn-sm btn-success pull-right' id='finish_receiving_button'><?php echo $this->lang->line('recvs_complete_receiving') ?></div>
						</div>
					<?php echo form_close(); ?>
				<?php
				}
				?>
			</div>
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
		source: '<?php echo site_url("receivings/item_search"); ?>',
    	minChars:0,
       	delay:10,
       	autoFocus: false,
		select:	function (a, ui) {
			$(this).val(ui.item.value);
			$("#add_item_form").submit();
		}
    });

    $('#item').focus();

	$('#item').blur(function()
    {
    	$(this).attr('value',"<?php echo $this->lang->line('sales_start_typing_item_name'); ?>");
    });

	$('#comment').keyup(function() 
	{
		$.post('<?php echo site_url("receivings/set_comment");?>', {comment: $('#comment').val()});
	});

	<?php
	if ($this->config->item('invoice_enable') == TRUE) 
	{
	?>
		$('#recv_invoice_number').keyup(function() 
		{
			$.post('<?php echo site_url("receivings/set_invoice_number");?>', {recv_invoice_number: $('#recv_invoice_number').val()});
		});

		$("#recv_print_after_sale").change(function()
		{
			$.post('<?php echo site_url("receivings/set_print_after_sale");?>', {recv_print_after_sale: $(this).is(":checked")});
		});

		var enable_invoice_number = function() 
		{
			var enabled = $("#recv_invoice_enable").is(":checked");
			$("#recv_invoice_number").prop("disabled", !enabled).parents('tr').show();
			return enabled;
		}

		enable_invoice_number();

		$("#recv_invoice_enable").change(function()
		{
			var enabled = enable_invoice_number();
			$.post('<?php echo site_url("receivings/set_invoice_number_enabled");?>', {recv_invoice_number_enabled: enabled});
			
		});
	<?php
	}
	?>

	$('#item,#supplier').click(function()
    {
    	$(this).attr('value','');
    });

    $("#supplier").autocomplete(
    {
		source: '<?php echo site_url("suppliers/suggest"); ?>',
    	minChars:0,
    	delay:10,
		select: function (a, ui) {
			$(this).val(ui.item.value);
			$("#select_supplier_form").submit();
		}
    });

	dialog_support.init("a.modal-dlg, button.modal-dlg");

	$('#supplier').blur(function()
    {
    	$(this).attr('value',"<?php echo $this->lang->line('recvs_start_typing_supplier_name'); ?>");
    });

    $("#finish_receiving_button").click(function()
    {
   		$('#finish_receiving_form').submit();
    });

    $("#cancel_receiving_button").click(function()
    {
    	if (confirm('<?php echo $this->lang->line("recvs_confirm_cancel_receiving"); ?>'))
    	{
			$('#finish_receiving_form').attr('action', '<?php echo site_url("receivings/cancel_receiving"); ?>');
    		$('#finish_receiving_form').submit();
    	}
    });

	table_support.handle_submit = function(resource, response, stay_open)
	{
		if(response.success)
		{
			if (resource.match(/suppliers$/))
			{
				$("#supplier").attr("value",response.id);
				$("#select_supplier_form").submit();
			}
			else
			{
				$("#item").attr("value",response.id);
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

</script>

<?php $this->load->view("partial/footer"); ?>
