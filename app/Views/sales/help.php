<div class="container-fluid">
	<ul class="nav nav-tabs" id="SCTabs" data-toggle="tab">
		<li class="active"><a href="#system_shortcuts" data-toggle="tab" title="<?php echo $this->lang->line('sales_key_system'); ?>"><?php echo $this->lang->line('sales_key_system'); ?></a></li>
		<li><a href="#browser_shortcuts" data-toggle="tab" title="<?php echo $this->lang->line('sales_key_browser'); ?>"><?php echo $this->lang->line('sales_key_browser'); ?></a></li>
	</ul>  
	<div class="tab-content">
		<div class="tab-pane active" id="system_shortcuts">
		<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th><?php echo $this->lang->line('sales_key_help'); ?></th>
				<th><?php echo $this->lang->line('sales_key_function'); ?></th>
			</tr>
		</thead>
			<tbody>
				<tr>
					<td><code>ESC</code></td>
					<td><?php echo $this->lang->line('sales_key_cancel'); ?></td>
				</tr>
				<tr>
					<td><code>ALT + 1</code></td>
					<td><?php echo $this->lang->line('sales_key_item_search'); ?></td>
				</tr>
				<tr>
					<td><code>ALT + 2</code></td>
					<td><?php echo $this->lang->line('sales_key_customer_search'); ?></td>
				</tr>
				<tr>
					<td><code>ALT + 3</code></td>
					<td><?php echo $this->lang->line('sales_key_suspend'); ?></td>
				</tr>
				<tr>
					<td><code>ALT + 4</code></td>
					<td><?php echo $this->lang->line('sales_key_suspended'); ?></td>
				</tr>
				<tr>
					<td><code>ALT + 5</code></td>
					<td><?php echo $this->lang->line('sales_key_tendered'); ?></td>
				</tr>
				<tr>
					<td><code>ALT + 6</code></td>
					<td><?php echo $this->lang->line('sales_key_payment'); ?></td>
				</tr>
				<tr>
					<td><code>ALT + 7</code></td>
					<td><?php echo $this->lang->line('sales_key_finish_sale'); ?></td>
				</tr>
				<tr>
					<td><code>ALT + 8</code></td>
					<td><?php echo $this->lang->line('sales_key_finish_quote'); ?></td>
				</tr>
				<tr>
					<td><code>ALT + 9</code></td>
					<td><?php echo $this->lang->line('sales_key_help_modal'); ?></td>
				</tr>
			</tbody>
		</table>
		</div>		

	<div class="tab-pane" id="browser_shortcuts">
		<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th><?php echo $this->lang->line('sales_key_help'); ?></th>
				<th><?php echo $this->lang->line('sales_key_function'); ?></th>
			</tr>
		</thead>
			<tbody>
				<tr>
					<td><code>F11</code></td>
					<td><?php echo $this->lang->line('sales_key_full'); ?></td>
				</tr>
				<tr>
					<td><code>CTRL + </code></td>
					<td><?php echo $this->lang->line('sales_key_in'); ?></td>
				</tr>
				<tr>
					<td><code>CTRL -</code></td>
					<td><?php echo $this->lang->line('sales_key_out'); ?></td>
				</tr>
				<tr>
					<td><code>CTRL + 0</code></td>
					<td><?php echo $this->lang->line('sales_key_restore'); ?></td>
				</tr>
				<tr>
					<td><code>CTRL + P</code></td>
					<td><?php echo $this->lang->line('sales_key_print'); ?></td>
				</tr>
				<tr>
					<td><code>CTRL + F</code></td>
					<td><?php echo $this->lang->line('sales_key_search'); ?></td>
				</tr>
			</tbody>
		</table>
		</div>		
	</div>
</div>
