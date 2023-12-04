<?php
/**
 * @var array $suspended_sales
 * @var array $config
 */

use App\Models\Employee;
use App\Models\Customer;

?>
<style>
@media (min-width: 768px)
{
	.modal-dlg .modal-dialog
	{
		width: 750px !important;
	}
}
</style>
<table id="suspended_sales_table" class="table table-striped table-hover">
	<thead>
		<tr style="background-color: #ccc;">
			<th><?= lang('Sales.suspended_doc_id') ?></th>
			<th><?= lang('Sales.date') ?></th>
			<?php
			if($config['dinner_table_enable'])
			{
			?>
				<th><?= lang('Sales.table') ?></th>
			<?php
			}
			?>
			<th><?= lang('Sales.customer') ?></th>
			<th><?= lang('Sales.employee') ?></th>
			<th><?= lang('Sales.comments') ?></th>
			<th><?= lang('Sales.unsuspend_and_delete') ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach($suspended_sales as $suspended_sale)
		{
		?>
			<tr>
				<td><?= $suspended_sale['doc_id'] ?></td>
				<td><?= date($config['dateformat'], strtotime($suspended_sale['sale_time'])) ?></td>
				<?php
				if($config['dinner_table_enable'])
				{
				?>
					<td><?= esc($this->Dinner_table->get_name($suspended_sale['dinner_table_id'])) ?></td>
				<?php
				}
				?>
				<td>
					<?php
					if(isset($suspended_sale['customer_id']))
					{
						$customer = model(Customer::class);	//TODO: Should we be accessing a model in a view rather than passing this data to the view via the controller?
						$customer_data = $customer->get_info($suspended_sale['customer_id']);
						echo esc("$customer_data->first_name $customer_data->last_name");
					}
					else
					{
					?>
						&nbsp;
					<?php
					}
					?>
				</td>
				<td>
					<?php
					if(isset($suspended_sale['employee_id']))
					{
						$employee = model(Employee::class);
						$employee_data = $employee->get_info($suspended_sale['employee_id']);
						echo esc("$employee_data->first_name $employee_data->last_name");
					}
					else
					{
					?>
						&nbsp;
					<?php
					}
					?>
				</td>
				<td><?= esc($suspended_sale['comment']) ?></td>
				<td>
					<?= form_open('sales/unsuspend') ?>
						<?= form_hidden('suspended_sale_id', $suspended_sale['sale_id']) ?>
						<input type="submit" name="submit" value="<?= lang('Sales.unsuspend') ?>" id="submit" class="btn btn-primary btn-xs pull-right">
					<?= form_close() ?>
				</td>
			</tr>
		<?php
		}
		?>
	</tbody>
</table>
