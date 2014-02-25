<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
<ul id="error_message_box"></ul>
<?php
echo form_open('item_kits/save/'.$item_kit_info->item_kit_id,array('id'=>'item_kit_form'));
?>
<fieldset id="item_kit_info">
<legend><?php echo $this->lang->line("item_kits_info"); ?></legend>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('item_kits_name').':', 'name',array('class'=>'wide required')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'name',
		'id'=>'name',
		'value'=>$item_kit_info->name)
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('item_kits_description').':', 'description',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_textarea(array(
		'name'=>'description',
		'id'=>'description',
		'value'=>$item_kit_info->description,
		'rows'=>'5',
		'cols'=>'17')
	);?>
	</div>
</div>


<div class="field_row clearfix">
<?php echo form_label($this->lang->line('item_kits_add_item').':', 'item',array('class'=>'wide')); ?>
	<div class='form_field'>
		<?php echo form_input(array(
			'name'=>'item',
			'id'=>'item'
		));?>
	</div>
</div>

<table id="item_kit_items">
	<tr>
		<th><?php echo $this->lang->line('common_delete');?></th>
		<th><?php echo $this->lang->line('item_kits_item');?></th>
		<th><?php echo $this->lang->line('item_kits_quantity');?></th>
	</tr>
	
	<?php foreach ($this->Item_kit_items->get_info($item_kit_info->item_kit_id) as $item_kit_item) {?>
		<tr>
			<?php
			$item_info = $this->Item->get_info($item_kit_item['item_id']);
			?>
			<td><a href="#" onclick='return deleteItemKitRow(this);'>X</a></td>
			<td><?php echo $item_info->name; ?></td>
			<td><input class='quantity' id='item_kit_item_<?php echo $item_kit_item['item_id'] ?>' type='text' size='3' name=item_kit_item[<?php echo $item_kit_item['item_id'] ?>] value='<?php echo $item_kit_item['quantity'] ?>'/></td>
		</tr>
	<?php } ?>
</table>
<?php
echo form_submit(array(
	'name'=>'submit',
	'id'=>'submit',
	'value'=>$this->lang->line('common_submit'),
	'class'=>'submit_button float_right')
);
?>
</fieldset>
<?php
echo form_close();
?>
<script type='text/javascript'>

$("#item").autocomplete('<?php echo site_url("items/item_search"); ?>',
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
	$("#item").val("");
	
	if ($("#item_kit_item_"+data[0]).length ==1)
	{
		$("#item_kit_item_"+data[0]).val(parseFloat($("#item_kit_item_"+data[0]).val()) + 1);
	}
	else
	{
		$("#item_kit_items").append("<tr><td><a href='#' onclick='return deleteItemKitRow(this);'>X</a></td><td>"+data[1]+"</td><td><input class='quantity' id='item_kit_item_"+data[0]+"' type='text' size='3' name=item_kit_item["+data[0]+"] value='1'/></td></tr>");
	}
});


//validation and submit handling
$(document).ready(function()
{
	$('#item_kit_form').validate({
		submitHandler:function(form)
		{
			$(form).ajaxSubmit({
			success:function(response)
			{
				tb_remove();
				post_item_kit_form_submit(response);
			},
			dataType:'json'
		});

		},
		errorLabelContainer: "#error_message_box",
 		wrapper: "li",
		rules:
		{
			name:"required",
			category:"required"
		},
		messages:
		{
			name:"<?php echo $this->lang->line('items_name_required'); ?>",
			category:"<?php echo $this->lang->line('items_category_required'); ?>"
		}
	});
});

function deleteItemKitRow(link)
{
	$(link).parent().parent().remove();
	return false;
}
</script>