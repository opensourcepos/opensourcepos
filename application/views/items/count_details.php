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
<?php echo form_label($this->lang->line('items_stock_location').':', 'stock_location',array('class'=>'wide')); ?>
</td>
<td>
    <?php 
        echo form_dropdown('stock_location',$stock_locations,current($stock_locations),'onchange="display_stock(this.value)"');
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
		'value'=>current($item_quantities),
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
<?php
$inventory_array = $this->Inventory->get_inventory_data_for_item($item_info->item_id)->result_array();
$employee_name = array();
foreach( $inventory_array as $row)
{
    $person_id = $row['trans_user'];
    $employee = $this->Employee->get_info($person_id);
    array_push($employee_name, $employee->first_name." ".$employee->last_name);
}
?>
<table id="inventory_result" border="0" align="center">
<tr bgcolor="#FF0033" align="center" style="font-weight:bold"><td colspan="4">Inventory Data Tracking</td></tr>
<tr align="center" style="font-weight:bold"><td width="15%">Date</td><td width="25%">Employee</td><td width="15%">In/Out Qty</td><td width="45%">Remarks</td></tr>
</table>

<script type='text/javascript'>
$(document).ready(function()
{
    display_stock(<?php echo json_encode(key($stock_locations)); ?>);
});

function display_stock(location_id)
{
    var item_quantities= <?php echo json_encode($item_quantities ); ?>;
    document.getElementById("quantity").value = item_quantities[location_id];
    
    var inventory_data = <?php echo json_encode($inventory_array); ?>;
    var employee_data = <?php echo json_encode($employee_name); ?>;
    
    var table = document.getElementById("inventory_result");
    //Remove old query
    var rowCount = table.rows.length;
    for (var index = rowCount; index > 2; index--)
    {
        table.deleteRow(index-1);       
    }
    
    //Add new query
    for (var index = 0; index < inventory_data.length; index++) 
    {                
        var data = inventory_data[index];
        if(data['trans_location'] == location_id)
        {
            var tr = document.createElement('TR');
            tr.setAttribute("bgColor","#CCCCCC");
            tr.setAttribute("align","#center");
            
            var td = document.createElement('TD');
            td.appendChild(document.createTextNode(data['trans_date']));
            tr.appendChild(td);
            
            td = document.createElement('TD');
            td.appendChild(document.createTextNode(employee_data[index]));
            tr.appendChild(td);
            
            td = document.createElement('TD');
            td.setAttribute("align","right");
            td.appendChild(document.createTextNode(data['trans_inventory']));
            tr.appendChild(td);
            
            td = document.createElement('TD');            
            td.appendChild(document.createTextNode(data['trans_comment']));
            tr.appendChild(td);

            table.appendChild(tr);
        }
    }
   
}  
</script>