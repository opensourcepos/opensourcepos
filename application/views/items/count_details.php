<?php echo form_open('items/save_inventory/'.$item_info->item_id, array('id'=>'item_form', 'class'=>'form-horizontal')); ?>
	<fieldset id="inv_item_basic_info">
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_item_number'), 'name', array('class'=>'control-label col-xs-3')); ?>
			<div class="col-xs-6">
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-barcode"></span></span>
					<?php echo form_input(array(
							'name'=>'item_number',
							'id'=>'item_number',
							'class'=>'form-control input-sm',
							'disabled'=>'',
							'value'=>$item_info->item_number)
							);?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_name'), 'name', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<?php echo form_input(array(
						'name'=>'name',
						'id'=>'name',
						'class'=>'form-control input-sm',
						'disabled'=>'',
						'value'=>$item_info->name)
						); ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_category'), 'category', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-tag"></span></span>
					<?php echo form_input(array(
							'name'=>'category',
							'id'=>'category',
							'class'=>'form-control input-sm',
							'disabled'=>'',
							'value'=>$item_info->category)
							);?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_stock_location'), 'stock_location', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<?php echo form_dropdown('stock_location', $stock_locations, current($stock_locations), array('onchange'=>'display_stock(this.value);', 'class'=>'form-control'));	?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_current_quantity'), 'quantity', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-3'>
				<?php echo form_input(array(
						'name'=>'quantity',
						'id'=>'quantity',
						'class'=>'form-control input-sm',
						'disabled'=>'',
						'value'=>to_quantity(current($item_quantities)))
						); ?>
			</div>
		</div>
	</fieldset>
<?php echo form_close(); ?>
	
<table class="table" style="font-size: 80%">
	<thead>
		<tr bgcolor="#999">
			<th style="text-align: center" colspan="4">Inventory Data Tracking</th>
		</tr>
		<tr bgcolor="#CCC">
			<th style="text-align: center">Date</th>
			<th style="text-align: center">Employee</th>
			<th style="text-align: center">In/Out Qty</th>
			<th style="text-align: center">Remarks</th>
		</tr>
	</thead>
	<tbody id="inventory_result">
		<?php
		/*
		 * the tbody content of the table will be filled in by the javascript (see bottom of page)
		*/
		
		$inventory_array = $this->Inventory->get_inventory_data_for_item($item_info->item_id)->result_array();
		$employee_name = array();

		foreach($inventory_array as $row)
		{
			$employee = $this->Employee->get_info($row['trans_user']);
			array_push($employee_name, $employee->first_name . ' ' . $employee->last_name);   
		}
		?>
	</tbody>
</table>

<script type='text/javascript'>
$(document).ready(function()
{
    display_stock(<?php echo json_encode(key($stock_locations)); ?>);
});

function display_stock(location_id)
{
    var item_quantities = <?php echo json_encode($item_quantities); ?>;
    document.getElementById("quantity").value = parseFloat(item_quantities[location_id]).toFixed(<?php echo quantity_decimals(); ?>);
    
    var inventory_data = <?php echo json_encode($inventory_array); ?>;
    var employee_data = <?php echo json_encode($employee_name); ?>;
    
    var table = document.getElementById("inventory_result");

    // Remove old query from tbody
    var rowCount = table.rows.length;
    for (var index = rowCount; index > 0; index--)
    {
        table.deleteRow(index-1);       
    }
				
    // Add new query to tbody
    for (var index = 0; index < inventory_data.length; index++) 
    {                
        var data = inventory_data[index];
        if(data['trans_location'] == location_id)
        {
            var tr = document.createElement('tr');

            var td = document.createElement('td');
            td.appendChild(document.createTextNode(data['trans_date']));
            tr.appendChild(td);
            
            td = document.createElement('td');
            td.appendChild(document.createTextNode(employee_data[index]));
            tr.appendChild(td);
            
            td = document.createElement('td');
            td.appendChild(document.createTextNode(parseFloat(data['trans_inventory']).toFixed(<?php echo quantity_decimals(); ?>)));
			td.setAttribute("style", "text-align:center");
            tr.appendChild(td);
            
            td = document.createElement('td');            
            td.appendChild(document.createTextNode(data['trans_comment']));
            tr.appendChild(td);

            table.appendChild(tr);
        }
    }
}
</script>