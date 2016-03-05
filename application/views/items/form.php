<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open('items/save/'.$item_info->item_id, array('id'=>'item_form', 'enctype'=>'multipart/form-data', 'class' => 'form-horizontal')); ?>
	<fieldset id="item_basic_info">
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_item_number'), 'item_number', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-barcode"></span></span>
					<?php echo form_input(array(
							'name'=>'item_number',
							'id'=>'item_number',
							'class'=>'form-control input-sm',
							'value'=>$item_info->item_number)
							);?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_name'), 'name', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<?php echo form_input(array(
						'name'=>'name',
						'id'=>'name',
						'class'=>'form-control input-sm',
						'value'=>$item_info->name)
						);?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_category'), 'category', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-tag"></span></span>
					<?php echo form_input(array(
							'name'=>'category',
							'id'=>'category',
							'class'=>'form-control input-sm',
							'value'=>$item_info->category)
							);?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_supplier'), 'supplier', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<?php echo form_dropdown('supplier_id', $suppliers, $selected_supplier, array('class'=>'form-control'));?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_cost_price'), 'cost_price', array('class'=>'required control-label col-xs-3')); ?>
			<div class="col-xs-4">
				<div class="input-group input-group-sm">
					<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php echo form_input(array(
							'name'=>'cost_price',
							'id'=>'cost_price',
							'class'=>'form-control input-sm',
							'value'=>$item_info->cost_price)
							);?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_unit_price'), 'unit_price', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-4'>
				<div class="input-group input-group-sm">
					<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php echo form_input(array(
							'name'=>'unit_price',
							'id'=>'unit_price',
							'class'=>'form-control input-sm',
							'value'=>$item_info->unit_price)
							);?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_tax_1'), 'tax_percent_1', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-sm-3'>
				<?php echo form_input(array(
						'name'=>'tax_names[]',
						'id'=>'tax_name_1',
						'class'=>'form-control input-sm',
						'value'=> isset($item_tax_info[0]['name']) ? $item_tax_info[0]['name'] : $this->config->item('default_tax_1_name'))
						);?>
			</div>
			<div class="col-sm-3">
				<div class="input-group input-group-sm">
					<?php echo form_input(array(
							'name'=>'tax_percents[]',
							'id'=>'tax_percent_name_1',
							'class'=>'form-control input-sm',
							'value'=> isset($item_tax_info[0]['percent']) ? $item_tax_info[0]['percent'] : $default_tax_1_rate)
							);?>
					<span class="input-group-addon input-sm"><b>%</b></span>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_tax_2'), 'tax_percent_2', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-sm-3'>
				<?php echo form_input(array(
						'name'=>'tax_names[]',
						'id'=>'tax_name_2',
						'class'=>'form-control input-sm',
						'value'=> isset($item_tax_info[1]['name']) ? $item_tax_info[1]['name'] : $this->config->item('default_tax_2_name'))
						);?>
			</div>
			<div class="col-sm-3">
				<div class="input-group input-group-sm">
					<?php echo form_input(array(
							'name'=>'tax_percents[]',
							'class'=>'form-control input-sm',
							'id'=>'tax_percent_name_2',
							'value'=> isset($item_tax_info[1]['percent']) ? $item_tax_info[1]['percent'] : $default_tax_2_rate)
							);?>
					<span class="input-group-addon input-sm"><b>%</b></span>
				</div>
			</div>
		</div>

		<?php
		foreach($stock_locations as $key=>$location_detail)
		{
		?>
			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('items_quantity').' '.$location_detail['location_name'] ,
					$key.'_quantity',
					array('class'=>'required control-label col-xs-3')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
							'name'=>$key.'_quantity',
							'id'=>$key.'_quantity',
							'class'=>'required quantity form-control',
							'value'=>isset($item_info->item_id)?$location_detail['quantity']:0)
							);?>
				</div>
			</div>
		<?php
		}
		?>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_receiving_quantity'), 'receiving_quantity', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-2'>
				<?php echo form_input(array(
						'name'=>'receiving_quantity',
						'id'=>'receiving_quantity',
						'class'=>'required form-control input-sm',
						'value'=>$item_info->receiving_quantity)
						);?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_reorder_level'), 'reorder_level', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-2'>
				<?php echo form_input(array(
						'name'=>'reorder_level',
						'id'=>'reorder_level',
						'class'=>'form-control input-sm',
						'value'=>!isset($item_info->item_id) ? 0 : $item_info->reorder_level)
						);?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_description'), 'description', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<?php echo form_textarea(array(
						'name'=>'description',
						'id'=>'description',
						'class'=>'form-control input-sm',
						'value'=>$item_info->description)
						);?>
			</div>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_image'), 'item_image', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<div class="fileinput fileinput-new input-group" data-provides="fileinput">
					<div class="form-control" data-trigger="fileinput"><i class="glyphicon glyphicon-file fileinput-exists"></i><span class="fileinput-filename"></span></div>
					<span class="input-group-addon input-sm btn btn-default btn-file"><span class="fileinput-new"><?php echo $this->lang->line("common_import_select_file"); ?></span><span class="fileinput-exists"><?php echo $this->lang->line("common_import_change_file"); ?></span><input type="file" id="item_image" name="item_image" accept="image/*"></span>
					<a href="#" class="input-group-addon input-sm btn btn-default fileinput-exists" data-dismiss="fileinput"><?php echo $this->lang->line("common_import_remove_file"); ?></a>
				</div>
			</div>
		</div>

<?php
/*
		<div class="form-group form-group-sm">	
			<?php echo form_label($this->lang->line('items_image'), 'items_image', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<div class="fileinput fileinput-new" data-provides="fileinput">
					<div class="fileinput-new thumbnail" style="width: 100px; height: 100px;">	
						<img data-src="holder.js/100%x100%" alt="<?php echo $this->lang->line('items_image'); ?>" 
							src="<?php isset($item_info->pic_id) ? base_url('uploads/item_pics/') . '/' . $item_info->pic_id : ''?>" 
							style="max-height: 100%; max-width: 100%;">
					</div>
					<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 100px; max-height: 100px;"></div>
					<div>
						<span class="btn btn-default btn-sm btn-file">
							<span class="fileinput-new"><?php echo $this->lang->line("config_company_select_image"); ?></span>
							<span class="fileinput-exists"><?php echo $this->lang->line("config_company_change_image"); ?></span>
							<input type="file" name="items_image">
						</span>
						<a href="#" class="btn btn-default btn-sm fileinput-exists" data-dismiss="fileinput"><?php echo $this->lang->line("config_company_remove_image"); ?></a>
					</div>
				</div>
			</div>
		</div>
*/
?>		
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_allow_alt_description'), 'allow_alt_description', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-1'>
				<?php echo form_checkbox(array(
						'name'=>'allow_alt_description',
						'id'=>'allow_alt_description',
						'value'=>1,
						'checked'=>($item_info->allow_alt_description)? 1 : 0)
						);?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_is_serialized'), 'is_serialized', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-1'>
				<?php echo form_checkbox(array(
						'name'=>'is_serialized',
						'id'=>'is_serialized',
						'value'=>1,
						'checked'=>($item_info->is_serialized)? 1 : 0)
						);?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_is_deleted'), 'is_deleted', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-1'>
				<?php echo form_checkbox(array(
						'name'=>'is_deleted',
						'id'=>'is_deleted',
						'value'=>1,
						'checked'=>($item_info->deleted)? 1 : 0)
						);?>
			</div>
		</div>

		<?php
		for ($i = 1; $i <= 10; $i++)
		{
		?>
			<?php
			if($this->config->item('custom'.$i.'_name') != null)
			{
				$item_arr = (array)$item_info;
			?>
				<div class="form-group form-group-sm">
					<?php echo form_label($this->config->item('custom'.$i.'_name'), 'custom'.$i, array('class'=>'control-label col-xs-3')); ?>
					<div class='col-xs-6'>
						<?php echo form_input(array(
								'name'=>'custom'.$i,
								'id'=>'custom'.$i,
								'class'=>'form-control input-sm',
								'value'=>$item_arr['custom'.$i])
								);?>
					</div>
				</div>
		<?php
			}
		}
		?>
	</fieldset>
<?php echo form_close(); ?>

<script type='text/javascript'>
	//validation and submit handling
	$(document).ready(function()
	{
		$("#continue").click(function()
		{
			stay_open = true;
		});

		$("#submit").click(function()
		{
			stay_open = false;
		});

		var no_op = function(event, data, formatted){};
		$("#category").autocomplete("<?php echo site_url('items/suggest_category');?>",{max:100,minChars:0,delay:10}).result(no_op).search();

		<?php for ($i = 0; $i < 11; $i++)
		{
		?>
			$("#custom"+<?php echo $i; ?>).autocomplete("<?php echo site_url('items/suggest_custom'.$i);?>",{max:100,minChars:0,delay:10}).result(no_op).search();
		<?php
		}
		?>

		$.validator.addMethod("item_number", function(value, element)
		{
			return JSON.parse($.ajax(
				{
					type: 'POST',
					url: '<?php echo site_url($controller_name . "/check_item_number")?>',
					data: {'item_id' : '<?php echo $item_info->item_id; ?>', 'item_number' : $(element).val() },
					success: function(response)
					{
						success=response.success;
					},
					async:false,
					dataType: 'json'
				}).responseText).success;

		}, '<?php echo $this->lang->line("items_item_number_duplicate"); ?>');

		$('#item_form').validate($.extend({
			submitHandler:function(form, event)
			{
				$(form).ajaxSubmit({
					success:function(response)
					{
						var stay_open = dialog_support.clicked_id() != 'submit';
						if (stay_open) 
						{
							// set action of item_form to url without item id, so a new one can be created
							$("#item_form").attr("action", "<?php echo site_url("items/save/")?>");
							// use a whitelist of fields to minimize unintended side effects
							$(':text, :password, :file, #description, #item_form').not('.quantity, #reorder_level, #tax_name_1,' + 
									'#tax_percent_name_1, #reference_number, #name, #cost_price, #unit_price, #taxed_cost_price, #taxed_unit_price').val('');  
							// de-select any checkboxes, radios and drop-down menus
							$(':input', '#item_form').not('#item_category_id').removeAttr('checked').removeAttr('selected');
						}
						else
						{
							dialog_support.hide();
						}
						post_item_form_submit(response, stay_open);	
					},
					dataType:'json'
				});
			},
			rules:
			{
				name:"required",
				category:"required",
				item_number: { item_number: true },
				cost_price:
				{
					required:true,
					number:true
				},
				unit_price:
				{
					required:true,
					number:true
				},
				tax_percent:
				{
					required:true,
					number:true
				},
				reorder_level:
				{
					required:true,
					number:true
				}

			},
			messages:
			{
				name:"<?php echo $this->lang->line('items_name_required'); ?>",
				category:"<?php echo $this->lang->line('items_category_required'); ?>",
				cost_price:
				{
					required:"<?php echo $this->lang->line('items_cost_price_required'); ?>",
					number:"<?php echo $this->lang->line('items_cost_price_number'); ?>"
				},
				unit_price:
				{
					required:"<?php echo $this->lang->line('items_unit_price_required'); ?>",
					number:"<?php echo $this->lang->line('items_unit_price_number'); ?>"
				},
				tax_percent:
				{
					required:"<?php echo $this->lang->line('items_tax_percent_required'); ?>",
					number:"<?php echo $this->lang->line('items_tax_percent_number'); ?>"
				},
				reorder_level:
				{
					required:"<?php echo $this->lang->line('items_reorder_level_required'); ?>",
					number:"<?php echo $this->lang->line('items_reorder_level_number'); ?>"
				}
			}
		}, dialog_support.error));
	});
</script>

