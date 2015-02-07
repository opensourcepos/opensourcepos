<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
<ul id="error_message_box" class="error_message_box"></ul>
<?php
echo form_open('items/save/'.$item_info->item_id,array('id'=>'item_form', 'enctype'=>'multipart/form-data'));
?>
<fieldset id="item_basic_info">
<legend><?php echo $this->lang->line("items_basic_information"); ?></legend>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('items_item_number').':', 'item_number',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'item_number',
		'id'=>'item_number',
		'value'=>$item_info->item_number)
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('items_name').':', 'name',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'name',
		'id'=>'name',
		'value'=>$item_info->name)
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('items_category').':', 'category',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'category',
		'id'=>'category',
		'value'=>$item_info->category)
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('items_supplier').':', 'supplier',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_dropdown('supplier_id', $suppliers, $selected_supplier);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('items_cost_price').':', 'cost_price',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'cost_price',
		'size'=>'8',
		'id'=>'cost_price',
		'value'=>$item_info->cost_price)
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('items_unit_price').':', 'unit_price',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'unit_price',
		'size'=>'8',
		'id'=>'unit_price',
		'value'=>$item_info->unit_price)
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('items_tax_1').':', 'tax_percent_1',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'tax_names[]',
		'id'=>'tax_name_1',
		'size'=>'8',
		'value'=> isset($item_tax_info[0]['name']) ? $item_tax_info[0]['name'] : $this->config->item('default_tax_1_name'))
	);?>
	</div>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'tax_percents[]',
		'id'=>'tax_percent_name_1',
		'size'=>'3',
		'value'=> isset($item_tax_info[0]['percent']) ? $item_tax_info[0]['percent'] : $default_tax_1_rate)
	);?>
	%
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('items_tax_2').':', 'tax_percent_2',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'tax_names[]',
		'id'=>'tax_name_2',
		'size'=>'8',
		'value'=> isset($item_tax_info[1]['name']) ? $item_tax_info[1]['name'] : $this->config->item('default_tax_2_name'))
	);?>
	</div>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'tax_percents[]',
		'id'=>'tax_percent_name_2',
		'size'=>'3',
		'value'=> isset($item_tax_info[1]['percent']) ? $item_tax_info[1]['percent'] : $default_tax_2_rate)
	);?>
	%
	</div>
</div>

<?php
foreach($stock_locations as $key=>$location_detail)
{
?>
    <div class="field_row clearfix">
    <?php echo form_label($this->lang->line('items_quantity').' '.$location_detail['location_name'] .':', 
                            $key.'_quantity',
                            array('class'=>'required wide')); ?>
    	<div class='form_field'>
    	<?php echo form_input(array(
    		'name'=>$key.'_quantity',
    		'id'=>$key.'_quantity',
    		'size'=>'8',
    		'value'=>$location_detail['quantity'])
    	);?>
    	</div>
    </div>
<?php
}
?>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('items_receiving_quantity').':', 'receiving_quantity',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'receiving_quantity',
		'id'=>'receiving_quantity',
		'size'=>'8',
		'value'=>$item_info->receiving_quantity)
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('items_reorder_level').':', 'reorder_level',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'reorder_level',
		'id'=>'reorder_level',
		'size'=>'8',
		'value'=>$item_info->reorder_level)
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('items_description').':', 'description',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_textarea(array(
		'name'=>'description',
		'id'=>'description',
		'value'=>$item_info->description,
		'rows'=>'5',
		'cols'=>'17')
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('items_image').':', 'item_image',array('class'=>'wide')); ?>
    <div class='form_field'>
        <?php echo form_upload('item_image');?>
    </div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('items_allow_alt_description').':', 'allow_alt_description',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_checkbox(array(
		'name'=>'allow_alt_description',
		'id'=>'allow_alt_description',
		'value'=>1,
		'checked'=>($item_info->allow_alt_description)? 1  :0)
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('items_is_serialized').':', 'is_serialized',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_checkbox(array(
		'name'=>'is_serialized',
		'id'=>'is_serialized',
		'value'=>1,
		'checked'=>($item_info->is_serialized)? 1 : 0)
	);?>
	</div>
</div>

<!-- Parq 131215 Start -->
<div class="field_row clearfix">
<?php echo form_label($this->lang->line('items_is_deleted').':', 'is_deleted',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_checkbox(array(
		'name'=>'is_deleted',
		'id'=>'is_deleted',
		'value'=>1,
		'checked'=>($item_info->deleted)? 1 : 0)
	);?>
	</div>
</div>
<!-- Parq End -->



<!--  GARRISON ADDED 4/21/2013 -->
<div class="field_row clearfix">	
<?php
if($this->config->item('custom1_name') != NULL)
{
	echo form_label($this->config->item('custom1_name').':', 'custom1',array('class'=>'wide')); ?>
	<div class='form_field'>
		<?php echo form_input(array(
			'name'=>'custom1',
			'id'=>'custom1',
			'value'=>$item_info->custom1)
		);?>
		</div>
	</div>
<?php }//end if?>

<div class="field_row clearfix">
<?php
if($this->config->item('custom2_name') != NULL)
{
	echo form_label($this->config->item('custom2_name').':', 'custom2',array('class'=>'wide')); ?>
	<div class='form_field'>
		<?php echo form_input(array(
			'name'=>'custom2',
			'id'=>'custom2',
			'value'=>$item_info->custom2)
		);?>
		</div>
	</div>
<?php }//end if?>

<div class="field_row clearfix">
<?php
if($this->config->item('custom3_name') != NULL)
{
	echo form_label($this->config->item('custom3_name').':', 'custom3',array('class'=>'wide')); ?>
	<div class='form_field'>
		<?php echo form_input(array(
			'name'=>'custom3',
			'id'=>'custom3',
			'value'=>$item_info->custom3)
		);?>
		</div>
	</div>
<?php }//end if?>

<div class="field_row clearfix">
<?php
if($this->config->item('custom4_name') != NULL)
{
	echo form_label($this->config->item('custom4_name').':', 'custom4',array('class'=>'wide')); ?>
	<div class='form_field'>
		<?php echo form_input(array(
			'name'=>'custom4',
			'id'=>'custom4',
			'value'=>$item_info->custom4)
		);?>
		</div>
	</div>
<?php }//end if?>

<div class="field_row clearfix">
<?php
if($this->config->item('custom5_name') != NULL)
{
	echo form_label($this->config->item('custom5_name').':', 'custom5',array('class'=>'wide')); ?>
	<div class='form_field'>
		<?php echo form_input(array(
			'name'=>'custom5',
			'id'=>'custom5',
			'value'=>$item_info->custom5)
		);?>
		</div>
	</div>
<?php }//end if?>

<div class="field_row clearfix">
<?php
if($this->config->item('custom6_name') != NULL)
{
	echo form_label($this->config->item('custom6_name').':', 'custom6',array('class'=>'wide')); ?>
	<div class='form_field'>
		<?php echo form_input(array(
			'name'=>'custom6',
			'id'=>'custom6',
			'value'=>$item_info->custom6)
		);?>
		</div>
	</div>
<?php }//end if?>

<div class="field_row clearfix">
<?php
if($this->config->item('custom7_name') != NULL)
{
	echo form_label($this->config->item('custom7_name').':', 'custom7',array('class'=>'wide')); ?>
	<div class='form_field'>
		<?php echo form_input(array(
			'name'=>'custom7',
			'id'=>'custom7',
			'value'=>$item_info->custom7)
		);?>
	</div>
	</div>
<?php }//end if?>

<div class="field_row clearfix">
<?php
if($this->config->item('custom8_name') != NULL)
{
	echo form_label($this->config->item('custom8_name').':', 'custom8',array('class'=>'wide')); ?>
	<div class='form_field'>
		<?php echo form_input(array(
			'name'=>'custom8',
			'id'=>'custom8',
			'value'=>$item_info->custom8)
		);?>
		</div>
	</div>
<?php }//end if?>

<div class="field_row clearfix">
<?php
if($this->config->item('custom9_name') != NULL)
{
	echo form_label($this->config->item('custom9_name').':', 'custom9',array('class'=>'wide')); ?>
	<div class='form_field'>
		<?php echo form_input(array(
			'name'=>'custom9',
			'id'=>'custom9',
			'value'=>$item_info->custom9)
		);?>
		</div>
	</div>
<?php }//end if?>

<div class="field_row clearfix">
<?php
if($this->config->item('custom10_name') != NULL)
{
	echo form_label($this->config->item('custom10_name').':', 'custom10',array('class'=>'wide')); ?>
	<div class='form_field'>
		<?php echo form_input(array(
			'name'=>'custom10',
			'id'=>'custom10',
			'value'=>$item_info->custom10)
		);?>
		</div>
	</div>
<?php }//end if?>

<!--   END GARRISON ADDED -->

<?php
echo form_submit(array(
	'name'=>'submit_form',
	'id'=>'submit_form',
	'value'=>$this->lang->line('common_submit'),
	'class'=>'submit_button float_right')
);
?>
</fieldset>
<?php
echo form_close();
?>
<script type='text/javascript'>

//validation and submit handling
$(document).ready(function()
{
	var no_op = function(event, data, formatted){};
	$("#category").autocomplete("<?php echo site_url('items/suggest_category');?>",{max:100,minChars:0,delay:10}).result(no_op).search();

	<?php for ($i = 0; $i < 11; $i++) 
	{ 
	?>
	$("#custom"+<?php echo $i; ?>).autocomplete("<?php echo site_url('items/suggest_custom'.$i);?>",{max:100,minChars:0,delay:10}).result(no_op).search();
	<?php 
	}
	?>

	
	$('#item_form').validate({
		submitHandler:function(form)
		{
			$(form).ajaxSubmit({
			success:function(response)
			{
				tb_remove();
				post_item_form_submit(response);
			},
			dataType:'json'
		});

		},
		errorLabelContainer: "#error_message_box",
 		wrapper: "li",
		rules:
		{
			name:"required",
			category:"required",
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
	});
});
</script>