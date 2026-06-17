<?php
/**
 * @var object $item_info
 * @var array $stock_locations
 * @var array $item_quantities
 */

use App\Models\Employee;
use App\Models\Inventory;
?>

<?= form_open('items', ['id' => 'item_form']) ?>

    <label for="item_number" class="form-label"><?= lang('Items.item_number'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="item_number-icon"><i class="bi bi-upc-scan"></i></span>
        <input type="text" class="form-control" name="item_number" id="item_number" aria-describedby="item_number-icon" value="<?= $item_info->item_number ?>" disabled readonly>
    </div>

    <label for="name" class="form-label"><?= lang('Items.name'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="name-icon"><i class="bi bi-tag"></i></span>
        <input type="text" class="form-control" name="name" id="name" aria-describedby="name-icon" value="<?= $item_info->name ?>" disabled readonly>
    </div>

    <label for="category" class="form-label"><?= lang('Items.category'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="category-icon"><i class="bi bi-bookmark"></i></span>
        <input type="text" class="form-control" name="category" id="category" aria-describedby="category-icon" value="<?= $item_info->category ?>" disabled readonly>
    </div>

    <label for="stock_location" class="form-label"><?= lang('Items.stock_location'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="bi bi-boxes"></i></span>
        <select class="form-select" name="stock_location" id="stock_location" onchange="display_stock(this.value)">
            <?php foreach ($stock_locations as $value => $label): ?>
                <option value="<?= $value ?>" <?= $value == current($stock_locations) ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <label for="quantity" class="form-label"><?= lang('Items.current_quantity'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="quantity-icon"><i class="bi bi-box"></i></span>
        <input type="text" class="form-control" name="quantity" id="quantity" aria-describedby="quantity-icon" value="<?= to_quantity_decimals(current($item_quantities)) ?>" disabled readonly>
    </div>

<?= form_close() ?>

<div class="table-responsive">
    <table class="table table-sm table-hover align-middle text-nowrap" id="items_count_details">
        <thead class="table-secondary">
            <tr class="table-active">
                <th colspan="4"><?= lang('Items.inventory_data_tracking') ?></th>
            </tr>
            <tr>
                <th scope="col"><?= lang('Items.inventory_date') ?></th>
                <th scope="col"><?= lang('Items.inventory_employee') ?></th>
                <th scope="col" class="text-center" title="<?= lang('Items.inventory_in_out_quantity') ?>">#</th>
                <th scope="col"><?= lang('Items.inventory_remarks') ?></th>
            </tr>
        </thead>
        <tbody id="inventory_result">
            <?php
            // The tbody content of the table will be filled in by the javascript (see bottom of page)
            $employee = model(Employee::class);
            $inventory = model(Inventory::class);

            $inventory_array = $inventory->get_inventory_data_for_item($item_info->item_id)->getResultArray();
            $employee_name = [];

            foreach ($inventory_array as $row) {
                $employee_data = $employee->get_info($row['trans_user']);
                $employee_name[] = $employee_data->first_name . ' ' . $employee_data->last_name;
            }
            ?>
        </tbody>
    </table>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        display_stock(<?= json_encode(key(esc($stock_locations, 'raw'))) ?>);
    });

    function display_stock(location_id) {
        var item_quantities = <?= json_encode(esc($item_quantities, 'raw')) ?>;
        document.getElementById("quantity").value = parseFloat(item_quantities[location_id]).toFixed(<?= quantity_decimals() ?>);

        var inventory_data = <?= json_encode(esc($inventory_array, 'raw')) ?>;
        var employee_data = <?= json_encode(esc($employee_name, 'raw')) ?>;

        var table = document.getElementById("inventory_result");

        // Remove old query from tbody
        var rowCount = table.rows.length;
        for (var index = rowCount; index > 0; index--) {
            table.deleteRow(index - 1);
        }

        // Add new query to tbody
        for (var index = 0; index < inventory_data.length; index++) {
            var data = inventory_data[index];
            if (data['trans_location'] == location_id) {
                var tr = document.createElement('tr');

                var td = document.createElement('td');
                td.appendChild(document.createTextNode(data['trans_date']));
                tr.appendChild(td);

                td = document.createElement('td');
                td.appendChild(document.createTextNode(employee_data[index]));
                tr.appendChild(td);

                td = document.createElement('td');
                td.appendChild(document.createTextNode(parseFloat(data['trans_inventory']).toFixed(<?= quantity_decimals() ?>)));
                td.setAttribute("class", "text-end");
                tr.appendChild(td);

                td = document.createElement('td');
                td.appendChild(document.createTextNode(data['trans_comment']));
                tr.appendChild(td);

                table.appendChild(tr);
            }
        }
    }
</script>
