<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open('items/save_inventory/'.$item_info->item_id, array('id'=>'item_form')); ?>
	<fieldset id="item_basic_info">

		<div class="field_row clearfix">
		<?php echo form_label($this->lang->line('items_item_number').':', 'name', array('class'=>'wide')); ?>
			<div class="form_field">
			<?php $inumber = array (
				'name'=>'item_number',
				'id'=>'item_number',
				'value'=>$item_info->item_number,
				'style'       => 'border:none',
				'readonly' => 'readonly'
			);
				echo form_input($inumber);
			?>
			</div>
		</div>

		<div class="field_row clearfix">
			<?php echo form_label($this->lang->line('items_name').':', 'name', array('class'=>'wide')); ?>
			<div class='form_field'>
			<?php $iname = array (
				'name'=>'name',
				'id'=>'name',
				'value'=>$item_info->name,
				'style'       => 'border:none',
				'readonly' => 'readonly'
			);
			echo form_input($iname);
			?>
			</div>
		</div>

		<div class="field_row clearfix">
			<?php echo form_label($this->lang->line('items_category').':', 'category', array('class'=>'wide')); ?>
			<div class='form_field'>
			<?php $cat = array (
				'name'=>'category',
				'id'=>'category',
				'value'=>$item_info->category,
				'style'       => 'border:none',
				'readonly' => 'readonly'
				);
				echo form_input($cat);
				?>
			</div>
		</div>

		<div class="field_row clearfix">
		<?php echo form_label($this->lang->line('items_stock_location').':', 'stock_location', array('class'=>'wide')); ?>
			<div class='form_field'>
			<?php
					echo form_dropdown('stock_location',$stock_locations,current($stock_locations),'onchange="fill_quantity(this.value)"');
			?>
			</div>
		</div>

		<div class="field_row clearfix">
		<?php echo form_label($this->lang->line('items_current_quantity').':', 'quantity', array('class'=>'wide')); ?>
			<div class='form_field'>
			<?php $qty = array (

				'name'=>'quantity',
				'id'=>'quantity',
				'value'=>current($item_quantities),
				'style'       => 'border:none',
				'readonly' => 'readonly'
				);

				echo form_input($qty);
			?>
			</div>
		</div>

		<div class="field_row clearfix">
		<?php echo form_label($this->lang->line('items_add_minus').':', 'quantity', array('class'=>'required wide')); ?>
			<div class='form_field'>
			<?php echo form_input(array(
				'name'=>'newquantity',
				'id'=>'newquantity'
				)
			);?>
			</div>
		</div>

		<div class="field_row clearfix">
		<?php echo form_label($this->lang->line('items_inventory_comments').':', 'description', array('class'=>'wide')); ?>
			<div class='form_field'>
			<?php echo form_textarea(array(
				'name'=>'trans_comment',
				'id'=>'trans_comment',
				'rows'=>'3',
				'cols'=>'17')
			);?>
			</div>
		</div>
	</fieldset>
<?php echo form_close(); ?>

<script type='text/javascript'>

//validation and submit handling
$(document).ready(function()
{		
	$('#item_form').validate({
		submitHandler:function(form)
		{
			$(form).ajaxSubmit({
			success:function(response)
			{
				dialog_support.hide();
				post_item_form_submit(response);
			},
			dataType:'json'
		});

		},
		errorLabelContainer: "#error_message_box",
 		wrapper: "li",
		rules: 
		{
			newquantity:
			{
				required:true,
				number:true
			}
   		},
		messages: 
		{
			
			newquantity:
			{
				required:"<?php echo $this->lang->line('items_quantity_required'); ?>",
				number:"<?php echo $this->lang->line('items_quantity_number'); ?>"
			}
		}
	});
});


function fill_quantity(val) 
{   
    var item_quantities= <?php echo json_encode($item_quantities ); ?>;
    document.getElementById("quantity").value = item_quantities[val];
}
</script>