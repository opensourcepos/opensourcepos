<style>
	@media (min-width: 768px) {
		.modal-dlg .modal-dialog {
			width: 750px !important;
		}
	}
</style>
<table id="suspended_sales_table" class="table table-striped table-hover">
	<thead>
		<tr bgcolor="#CCC">
			<th><?= $this->lang->line('sales_suspended_doc_id'); ?></th>
			<th><?= $this->lang->line('sales_date'); ?></th>
			<?php if ($this->config->item('dinner_table_enable') == TRUE) { ?>
			<th><?= $this->lang->line('sales_table'); ?></th>
			<?php } ?>
			<th><?= $this->lang->line('sales_customer'); ?></th>
			<th><?= $this->lang->line('sales_employee'); ?></th>
			<th><?= $this->lang->line('sales_comments'); ?></th>
			<th><?= $this->lang->line('sales_unsuspend_and_delete'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($suspended_sales as $suspended_sale) {
		?>
			<tr>
				<td><?= $suspended_sale['doc_id']; ?></td>
				<td><?= date($this->config->item('dateformat'), strtotime($suspended_sale['sale_time'])); ?></td>
				<?php if ($this->config->item('dinner_table_enable') == TRUE) { ?>
					<td><?= $this->Dinner_table->get_name($suspended_sale['dinner_table_id']); ?></td>
				<?php } ?>
				<td>
					<?php
					if (isset($suspended_sale['customer_id'])) {
						$customer = $this->Customer->get_info($suspended_sale['customer_id']);
						echo $customer->first_name . ' ' . $customer->last_name;
					} else {
					?>
						&nbsp;
					<?php } ?>
				</td>
				<td>
					<?php
					if (isset($suspended_sale['employee_id'])) {
						$employee = $this->Employee->get_info($suspended_sale['employee_id']);
						echo $employee->first_name . ' ' . $employee->last_name;
					} else {
					?>
						&nbsp;
					<?php } ?>
				</td>
				<td><?= $suspended_sale['comment']; ?></td>
				<td>
					<?= form_open('sales/unsuspend'); ?>
					<?= form_hidden('suspended_sale_id', $suspended_sale['sale_id']); ?>
					<input type="submit" name="submit" value="<?= $this->lang->line('sales_unsuspend'); ?>" id="submit" class="btn btn-primary btn-xs pull-right">
					<?= form_close(); ?>
				</td>
			</tr>
		<?php } ?>
	</tbody>
</table>