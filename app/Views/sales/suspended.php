<?php
/**
 * @var array $suspended_sales
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
			<th><?php echo lang('Sales.suspended_doc_id') ?></th>
			<th><?php echo lang('Sales.date') ?></th>
			<?php
			if(config('OSPOS')->settings['dinner_table_enable'] == TRUE)
			{
			?>
				<th><?php echo lang('Sales.table') ?></th>
			<?php
			}
			?>
			<th><?php echo lang('Sales.customer') ?></th>
			<th><?php echo lang('Sales.employee') ?></th>
			<th><?php echo lang('Sales.comments') ?></th>
			<th><?php echo lang('Sales.unsuspend_and_delete') ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach($suspended_sales as $suspended_sale)
		{
		?>
			<tr>
				<td><?php echo $suspended_sale['doc_id'] ?></td>
				<td><?php echo date(config('OSPOS')->settings['dateformat'], strtotime($suspended_sale['sale_time'])) ?></td>
				<?php
				if(config('OSPOS')->settings['dinner_table_enable'] == TRUE)
				{
				?>
					<td><?php echo esc($this->Dinner_table->get_name($suspended_sale['dinner_table_id'])) ?></td>
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
				<td><?php echo esc($suspended_sale['comment']) ?></td>
				<td>
					<?php echo form_open('sales/unsuspend') ?>
						<?php echo form_hidden('suspended_sale_id', $suspended_sale['sale_id']) ?>
						<input type="submit" name="submit" value="<?php echo lang('Sales.unsuspend') ?>" id="submit" class="btn btn-primary btn-xs pull-right">
					<?php echo form_close() ?>
				</td>
			</tr>
		<?php
		}
		?>
	</tbody>
</table>
