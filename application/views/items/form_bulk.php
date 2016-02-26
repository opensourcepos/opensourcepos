<div id="required_fields_message"><?php echo $this->lang->line('items_edit_fields_you_want_to_update'); ?></div>
<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open('items/bulk_update/',array('id'=>'item_form', 'class'=>'form-horizontal')); ?>
	<fieldset id="item_basic_info">
		<legend><?php echo $this->lang->line("items_basic_information"); ?></legend>

		<div class="form-group form-group-sm">	
			<?php echo form_label($this->lang->line('items_name'), 'name',array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<?php echo form_input(array(
					'name'=>'name',
					'id'=>'name',
					'class'=>'form-control')
				);?>
			</div>
		</div>

		<div class="form-group form-group-sm">	
			<?php echo form_label($this->lang->line('items_category'), 'category',array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<?php echo form_input(array(
					'name'=>'category',
					'id'=>'category',
					'class'=>'form-control')
				);?>
			</div>
		</div>

		<div class="form-group form-group-sm">	
			<?php echo form_label($this->lang->line('items_supplier'), 'supplier',array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<?php echo form_dropdown('supplier_id', $suppliers, '', 'class="form-control"');?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_cost_price'), 'cost_price',array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-3'>
				<div class="input-group input-group-sm">
					<span class="input-group input-group-addon"><?php echo $this->config->item('currency_symbol'); ?></span>
					<?php echo form_input(array(
							'name'=>'cost_price',
							'id'=>'cost_price',
							'class'=>'form-control')
					);?>
				</div>
			</div>
		</div>

		<div class="form-group form-group">
			<?php echo form_label($this->lang->line('items_unit_price'), 'unit_price',array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-3'>
				<div class="input-group input-group-sm">
					<span class="input-group input-group-addon"><?php echo $this->config->item('currency_symbol'); ?></span>
					<?php echo form_input(array(
							'name'=>'unit_price',
							'id'=>'unit_price',
							'class'=>'form-control')
					);?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_tax_1'), 'tax_percent_1',array('class'=>'control-label col-xs-3')); ?>
			<div class='col-sm-3'>
				<?php echo form_input(array(
						'name'=>'tax_names[]',
						'id'=>'tax_name_1',
						'class'=>'form-control',
						'value'=> isset($item_tax_info[0]['name']) ? $item_tax_info[0]['name'] : $this->config->item('items_sales_tax_1'))
				);?>
			</div>
			<div class="col-sm-3">
				<div class="input-group input-group-sm">
					<?php echo form_input(array(
							'name'=>'tax_percents[]',
							'id'=>'tax_percent_name_1',
							'class'=>'form-control',
							'value'=> isset($item_tax_info[0]['percent']) ? $item_tax_info[0]['percent'] : '')
					);?>
					<span class="input-group input-group-addon">%</span>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_tax_2'), 'tax_percent_2',array('class'=>'control-label col-xs-3')); ?>
			<div class='col-sm-3'>
				<?php echo form_input(array(
						'name'=>'tax_names[]',
						'id'=>'tax_name_2',
						'class'=>'form-control',
						'value'=> isset($item_tax_info[1]['name']) ? $item_tax_info[1]['name'] : $this->config->item('items_sales_tax_2'))
				);?>
			</div>
			<div class="col-sm-3">
				<div class="input-group input-group-sm">
					<?php echo form_input(array(
							'name'=>'tax_percents[]',
							'class'=>'form-control',
							'id'=>'tax_percent_name_2',
							'value'=> isset($item_tax_info[1]['percent']) ? $item_tax_info[1]['percent'] : '')
					);?>
					<span class="input-group input-group-addon">%</span>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">	
			<?php echo form_label($this->lang->line('items_reorder_level'), 'reorder_level',array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-3'>
				<?php echo form_input(array(
						'name'=>'reorder_level',
						'id'=>'reorder_level',
						'class'=>'form-control')
				);?>
			</div>
		</div>

		<div class="form-group form-group-sm">	
			<?php echo form_label($this->lang->line('items_description'), 'description',array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-5'>
				<?php echo form_textarea(array(
						'name'=>'description',
						'id'=>'description',
						'class'=>'form-control')
				);?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_allow_alt_description'), 'allow_alt_description',array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-5'>
				<?php echo form_dropdown('allow_alt_description', $allow_alt_description_choices, '', 'class="form-control"');?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_is_serialized'), 'is_serialized',array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-5'>
				<?php echo form_dropdown('is_serialized', $serialization_choices, '', 'class="form-control"');?>
			</div>
		</div>
	</fieldset>
<?php echo form_close(); ?>

<script type='text/javascript'>
//validation and submit handling
$(document).ready(function()
{	
	$("#category").autocomplete("<?php echo site_url('items/suggest_category');?>",{max:100,minChars:0,delay:10});
    $("#category").result(function(event, data, formatted)
    {
    });
	$("#category").search();
	
	$('#item_form').validate({
		submitHandler:function(form)
		{
			if(confirm("<?php echo $this->lang->line('items_confirm_bulk_edit') ?>"))
			{
				//Get the selected ids and create hidden fields to send with ajax submit.
				var selected_item_ids=get_selected_values();
				for(k=0;k<selected_item_ids.length;k++)
				{
					$(form).append("<input type='hidden' name='item_ids[]' value='"+selected_item_ids[k]+"' />");
				}
				
				
				$(form).ajaxSubmit({
				success:function(response)
				{
					dialog_support.hide();
					post_bulk_form_submit(response);
				},
				dataType:'json'
				});
			}

		},
		errorLabelContainer: "#error_message_box",
		errorClass: "has-error",
		wrapper: "li",
		rules: 
		{
			unit_price:
			{
				number:true
			},
			tax_percent:
			{
				number:true
			},
			quantity:
			{
				number:true
			},
			reorder_level:
			{
				number:true
			}
   		},
		messages: 
		{
			unit_price:
			{
				number:"<?php echo $this->lang->line('items_unit_price_number'); ?>"
			},
			tax_percent:
			{
				number:"<?php echo $this->lang->line('items_tax_percent_number'); ?>"
			},
			quantity:
			{
				number:"<?php echo $this->lang->line('items_quantity_number'); ?>"
			},
			reorder_level:
			{
				number:"<?php echo $this->lang->line('items_reorder_level_number'); ?>"
			}

		}
	});
});
</script>