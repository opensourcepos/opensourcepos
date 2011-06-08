<?php
echo form_open('items/save_inventory/'.$item_info->item_id,array('id'=>'item_form'));
?>
<fieldset id="inv_item_basic_info">
<legend><?php echo $this->lang->line("items_basic_information"); ?></legend>

<table align="center" border="0" bgcolor="#CCCCCC">
<div class="field_row clearfix">
<tr>
<td>	
<?php echo form_label($this->lang->line('items_item_number').':', 'name',array('class'=>'wide')); ?>
</td>
<td>
	<?php $inumber = array (
		'name'=>'item_number',
		'id'=>'item_number',
		'value'=>$item_info->item_number,
		'style'       => 'border:none',
		'readonly' => 'readonly'
	);
	
		echo form_input($inumber)
	?>
</td>
</tr>
<tr>
<td>	
<?php echo form_label($this->lang->line('items_name').':', 'name',array('class'=>'wide')); ?>
</td>
<td>	
	<?php $iname = array (
		'name'=>'name',
		'id'=>'name',
		'value'=>$item_info->name,
		'style'       => 'border:none',
		'readonly' => 'readonly'
	);
		echo form_input($iname);
		?>
</td>
</tr>
<tr>
<td>	
<?php echo form_label($this->lang->line('items_category').':', 'category',array('class'=>'wide')); ?>
</td>
<td>	
	<?php $cat = array (
		
		'name'=>'category',
		'id'=>'category',
		'value'=>$item_info->category,
		'style'       => 'border:none',
		'readonly' => 'readonly'
		);
	
		echo form_input($cat);
		?>
</td>
</tr>
<tr>
<td>
<?php echo form_label($this->lang->line('items_current_quantity').':', 'quantity',array('class'=>'wide')); ?>
</td>
<td>
	<?php $qty = array (
	
		'name'=>'quantity',
		'id'=>'quantity',
		'value'=>$item_info->quantity,
		'style'       => 'border:none',
		'readonly' => 'readonly'
		);
	
		echo form_input($qty);
	?>
</td>
</tr>
</div>	
</table>

<div class="field_row clearfix">
  <div class='form_field'></div>
</div>

<div class="field_row clearfix">
  <div class='form_field'></div>
</div>
</fieldset>
<?php 
echo form_close();
?>
<table border="0" align="center">
<tr bgcolor="#FF0033" align="center" style="font-weight:bold"><td colspan="4">Inventory Data Tracking</td></tr>
<tr align="center" style="font-weight:bold"><td width="15%">Date</td><td width="25%">Employee</td><td width="15%">In/Out Qty</td><td width="45%">Remarks</td></tr>
<?php
foreach($this->Inventory->get_inventory_data_for_item($item_info->item_id)->result_array() as $row)
{
?>
<tr bgcolor="#CCCCCC" align="center">
<td><?php echo $row['trans_date'];?></td>
<td><?php
	$person_id = $row['trans_user'];
	$employee = $this->Employee->get_info($person_id);
	echo $employee->first_name." ".$employee->last_name;
	?>
</td>
<td align="right"><?php echo $row['trans_inventory'];?></td>
<td><?php echo $row['trans_comment'];?></td>
</tr>

<?php
}
?>
</table>