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
				<th><?php echo $this->lang->line('sales_key_function'); ?></th>
				<th><?php echo $this->lang->line('sales_key_help'); ?></th>
			</tr>
		</thead>
			<tbody>
				<tr>
					
					<td><?php echo $this->lang->line('sales_key_cancel'); ?></td>
					<td><code><?php echo substr($this->config->item('key_cancel'), 5); ?></code></td>
				</tr>
				<tr>
					<td><?php echo $this->lang->line('sales_key_item_search'); ?></td>
					<td><code><?php echo substr($this->config->item('key_items'), 5); ?></code></td>
				</tr>
				<tr>
					<td><?php echo $this->lang->line('sales_key_customer_search'); ?></td>
					<td><code><?php echo substr($this->config->item('key_customers'), 5); ?></code></td>
				</tr>
				<tr>
					<td><?php echo $this->lang->line('sales_key_suspend'); ?></td>
					<td><code><?php echo substr($this->config->item('key_suspend'), 5); ?></code></td>
				</tr>
				<tr>
					<td><?php echo $this->lang->line('sales_key_suspended'); ?></td>
					<td><code><?php echo substr($this->config->item('key_suspended'), 5); ?></code></td>
				</tr>
				<tr>
					<td><?php echo $this->lang->line('sales_key_tendered'); ?></td>
					<td><code><?php echo substr($this->config->item('key_amount'), 5); ?></code></td>
				</tr>
				<tr>
					<td><?php echo $this->lang->line('sales_key_payment'); ?></td>
					<td><code><?php echo substr($this->config->item('key_payment'), 5); ?></code></td>
				</tr>
				<tr>
					<td><?php echo $this->lang->line('sales_key_finish_sale'); ?></td>
					<td><code><?php echo substr($this->config->item('key_complete'), 5); ?></code></td>
				</tr>
				<tr>
					<td><?php echo $this->lang->line('sales_key_finish_quote'); ?></td>
					<td><code><?php echo substr($this->config->item('key_finish'), 5); ?></code></td>
				</tr>
				<tr>
					<td><?php echo $this->lang->line('sales_key_help_modal'); ?></td>
					<td><code><?php echo substr($this->config->item('key_help'), 5); ?></code></td>
				</tr>
			</tbody>
		</table>
		</div>		

	<div class="tab-pane" id="browser_shortcuts">
		<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th><?php echo $this->lang->line('sales_key_function'); ?></th>
				<th><?php echo $this->lang->line('sales_key_help'); ?></th>
			</tr>
		</thead>
			<tbody>
				<tr>
					<td><?php echo $this->lang->line('sales_key_full'); ?></td>
					<td><code>F11</code></td>
				</tr>
				<tr>
					<td><?php echo $this->lang->line('sales_key_in'); ?></td>
					<td><code>CTRL + </code></td>
				</tr>
				<tr>
					<td><?php echo $this->lang->line('sales_key_out'); ?></td>
					<td><code>CTRL -</code></td>
				</tr>
				<tr>
					<td><?php echo $this->lang->line('sales_key_restore'); ?></td>
					<td><code>CTRL + 0</code></td>
				</tr>
				<tr>
					<td><?php echo $this->lang->line('sales_key_print'); ?></td>
					<td><code>CTRL + P</code></td>
				</tr>
				<tr>
					<td><?php echo $this->lang->line('sales_key_search'); ?></td>
					<td><code>CTRL + F</code></td>
				</tr>
			</tbody>
		</table>
		</div>		
	</div>
</div>