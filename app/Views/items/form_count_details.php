<?php
/**
 * @var object $item_info
 * @var array $stock_locations
 * @var array $item_quantities
 */

use App\Models\Employee;
use App\Models\Inventory;

?>
<?= form_open('items', ['id' => 'item_form', 'class' => 'form-horizontal']) ?>
	<fieldset id="count_item_basic_info">
		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.item_number'), 'name', ['class' => 'control-label col-xs-3']) ?>
			<div class="col-xs-8">
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-barcode"></span></span>
					<?= form_input ([
						'name' => 'item_number',
						'id' => 'item_number',
						'class' => 'form-control input-sm',
						'disabled' => '',
						'value' => esc($item_info->item_number)
					]) ?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.name'), 'name', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?= form_input ([
					'name' => 'name',
					'id' => 'name',
					'class' => 'form-control input-sm',
					'disabled' => '',
					'value' => esc($item_info->name)
				]) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.category'), 'category', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-tag"></span></span>
					<?= form_input ([
						'name' => 'category',
						'id' => 'category',
						'class' => 'form-control input-sm',
						'disabled' => '',
						'value' => esc($item_info->category)
					]) ?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.stock_location'), 'stock_location', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?= form_dropdown('stock_location', $stock_locations, current($stock_locations), ['onchange' => 'display_stock(this.value);', 'class' => 'form-control']) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Items.current_quantity'), 'quantity', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-4'>
				<?= form_input ([
					'name' => 'quantity',
					'id' => 'quantity',
					'class' => 'form-control input-sm',
					'disabled' => '',
					'value' => to_quantity_decimals(current($item_quantities))
				]) ?>
			</div>
		</div>
	</fieldset>
<?= form_close() ?>

<table id="items_count_details" class="table table-striped table-hover">
	<thead>
		<tr style="background-color: #999 !important;">
			<th colspan="4"><?= lang('Items.inventory_data_tracking') ?></th>
		</tr>
		<tr>
			<th style="width: 30%;"><?= lang('Items.inventory_date') ?></th>
			<th style="width: 20%;"><?= lang('Items.inventory_employee') ?></th>
			<th style="width: 20%;"><?= lang('Items.inventory_in_out_quantity') ?></th>
			<th style="width: 30%;"><?= lang('Items.inventory_remarks') ?></th>
		</tr>
	</thead>
	<tbody id="inventory_result">
		<?php
			 //the tbody content of the table will be filled in by the javascript (see bottom of page)
			$employee = model(Employee::class);
			$inventory = model(Inventory::class);

			$inventory_array = $inventory->get_inventory_data_for_item($item_info->item_id)->getResultArray();
			$employee_name = [];

			foreach($inventory_array as $row)
			{
				$employee_data = $employee->get_info($row['trans_user']);
				$employee_name[] = $employee_data->first_name . ' ' . $employee_data->last_name;
			}
		?>
	</tbody>
</table>

<script type="text/javascript">
$(document).ready(function()
{
	display_stock(<?= json_encode(key(esc($stock_locations, 'raw'))) ?>);
});

function display_stock(location_id)
{
	var item_quantities = <?= json_encode(esc($item_quantities, 'raw')) ?>;
	document.getElementById("quantity").value = parseFloat(item_quantities[location_id]).toFixed(<?= quantity_decimals() ?>);

	var inventory_data = <?= json_encode(esc($inventory_array, 'raw')) ?>;
	var employee_data = <?= json_encode(esc($employee_name, 'raw')) ?>;

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
			td.appendChild(document.createTextNode(parseFloat(data['trans_inventory']).toFixed(<?= quantity_decimals() ?>)));
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
