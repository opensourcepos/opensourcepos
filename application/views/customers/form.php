<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open($controller_name . '/save/' . $person_info->person_id, array('id'=>'customer_form', 'class'=>'form-horizontal')); ?>
	<ul class="nav nav-tabs nav-justified" data-tabs="tabs">
		<li class="active" role="presentation">
			<a data-toggle="tab" href="#customer_basic_info"><?php echo $this->lang->line("customers_basic_information"); ?></a>
		</li>
		<?php
		if(!empty($stats))
		{
		?>
			<li role="presentation">
				<a data-toggle="tab" href="#customer_stats_info"><?php echo $this->lang->line("customers_stats_info"); ?></a>
			</li>
		<?php
		}
		?>
		<?php
		if(!empty($mailchimp_info) && !empty($mailchimp_activity))
		{
		?>
			<li role="presentation">
				<a data-toggle="tab" href="#customer_mailchimp_info"><?php echo $this->lang->line("customers_mailchimp_info"); ?></a>
			</li>
		<?php
		}
		?>
	</ul>

	<div class="tab-content">
		<div class="tab-pane fade in active" id="customer_basic_info">
			<fieldset>
				<div class="form-group form-group-sm">
					<?php echo form_label($this->lang->line('customers_consent'), 'consent', array('class' => 'required control-label col-xs-3')); ?>
					<div class='col-xs-1'>
						<?php echo form_checkbox('consent', '1', $person_info->consent == '' ? (boolean)!$this->config->item('enforce_privacy') : (boolean)$person_info->consent); ?>
					</div>
				</div>

				<?php $this->load->view("people/form_basic_info"); ?>

				<div class="form-group form-group-sm">
					<?php echo form_label($this->lang->line('customers_discount'), 'discount_percent', array('class' => 'control-label col-xs-3')); ?>
					<div class='col-xs-3'>
						<div class="input-group input-group-sm">
							<?php echo form_input(array(
									'name'=>'discount_percent',
									'id'=>'discount_percent',
									'class'=>'form-control input-sm',
									'value'=>$person_info->discount_percent)
									); ?>
							<span class="input-group-addon input-sm"><b>%</b></span>
						</div>
					</div>	
				</div>

				<div class="form-group form-group-sm">
					<?php echo form_label($this->lang->line('customers_company_name'), 'company_name', array('class' => 'control-label col-xs-3')); ?>
					<div class='col-xs-8'>
						<?php echo form_input(array(
								'name'=>'company_name',
								'id'=>'company_name',
								'class'=>'form-control input-sm',
								'value'=>$person_info->company_name)
								); ?>
					</div>
				</div>

				<div class="form-group form-group-sm">
					<?php echo form_label($this->lang->line('customers_account_number'), 'account_number', array('class' => 'control-label col-xs-3')); ?>
					<div class='col-xs-4'>
						<?php echo form_input(array(
								'name'=>'account_number',
								'id'=>'account_number',
								'class'=>'form-control input-sm',
								'value'=>$person_info->account_number)
								); ?>
					</div>
				</div>

				<?php if($this->config->item('customer_reward_enable') == TRUE): ?>
					<div class="form-group form-group-sm">
						<?php echo form_label($this->lang->line('rewards_package'), 'rewards', array('class'=>'control-label col-xs-3')); ?>
						<div class='col-xs-8'>
							<?php echo form_dropdown('package_id', $packages, $selected_package, array('class'=>'form-control')); ?>
						</div>
					</div>

					<div class="form-group form-group-sm">
						<?php echo form_label($this->lang->line('customers_available_points'), 'available_points', array('class' => 'control-label col-xs-3')); ?>
						<div class='col-xs-4'>
							<?php echo form_input(array(
									'name'=>'available_points',
									'id'=>'available_points',
									'class'=>'form-control input-sm',
									'value'=>$person_info->points,
									'disabled'=>'')
									); ?>
						</div>
					</div>
				<?php endif; ?>

				<div class="form-group form-group-sm">
					<?php echo form_label($this->lang->line('customers_taxable'), 'taxable', array('class' => 'control-label col-xs-3')); ?>
					<div class='col-xs-1'>
						<?php echo form_checkbox('taxable', '1', $person_info->taxable == '' ? TRUE : (boolean)$person_info->taxable); ?>
					</div>
				</div>

				<?php
				if($customer_sales_tax_enabled)
				{
				?>
					<div class="form-group form-group-sm">
						<?php echo form_label($this->lang->line('customers_tax_code'), 'sales_tax_code_name', array('class'=>'control-label col-xs-3')); ?>
						<div class='col-xs-8'>
							<div class="input-group input-group-sm">
								<?php echo form_input(array(
										'name'=>'sales_tax_code_name',
										'id'=>'sales_tax_code_name',
										'class'=>'form-control input-sm',
										'size'=>'50',
										'value'=>$sales_tax_code_label)
								); ?>
								<?php echo form_hidden('sales_tax_code', $person_info->sales_tax_code); ?>
							</div>
						</div>
					</div>
				<?php
				}
				?>

				<div class="form-group form-group-sm">
					<?php echo form_label($this->lang->line('customers_date'), 'date', array('class'=>'control-label col-xs-3')); ?>
					<div class='col-xs-8'>
						<div class="input-group">
							<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-calendar"></span></span>
							<?php echo form_input(array(
									'name'=>'date',
									'id'=>'datetime',
									'class'=>'form-control input-sm',
									'value'=>date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), strtotime($person_info->date)),
									'readonly'=>'true')
									); ?>
						</div>
					</div>
				</div>

				<div class="form-group form-group-sm">
					<?php echo form_label($this->lang->line('customers_employee'), 'employee', array('class'=>'control-label col-xs-3')); ?>
					<div class='col-xs-8'>
						<?php echo form_input(array(
								'name'=>'employee',
								'id'=>'employee',
								'class'=>'form-control input-sm',
								'value'=>$employee,
								'readonly'=>'true')
								); ?>
					</div>
				</div>

				<?php echo form_hidden('employee_id', $person_info->employee_id); ?>
			</fieldset>
		</div>

		<?php
		if(!empty($stats))
		{
		?>
			<div class="tab-pane" id="customer_stats_info">
				<fieldset>
					<div class="form-group form-group-sm">
						<?php echo form_label($this->lang->line('customers_total'), 'total', array('class' => 'control-label col-xs-3')); ?>
						<div class="col-xs-4">
							<div class="input-group input-group-sm">
								<?php if (!currency_side()): ?>
									<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
								<?php endif; ?>
								<?php echo form_input(array(
										'name'=>'total',
										'id'=>'total',
										'class'=>'form-control input-sm',
										'value'=>to_currency_no_money($stats->total),
										'disabled'=>'')
										); ?>
								<?php if (currency_side()): ?>
									<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
								<?php endif; ?>
							</div>
						</div>
					</div>
					
					<div class="form-group form-group-sm">
						<?php echo form_label($this->lang->line('customers_max'), 'max', array('class' => 'control-label col-xs-3')); ?>
						<div class="col-xs-4">
							<div class="input-group input-group-sm">
								<?php if (!currency_side()): ?>
									<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
								<?php endif; ?>
								<?php echo form_input(array(
										'name'=>'max',
										'id'=>'max',
										'class'=>'form-control input-sm',
										'value'=>to_currency_no_money($stats->max),
										'disabled'=>'')
										); ?>
								<?php if (currency_side()): ?>
									<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
								<?php endif; ?>
							</div>
						</div>
					</div>
					
					<div class="form-group form-group-sm">
						<?php echo form_label($this->lang->line('customers_min'), 'min', array('class' => 'control-label col-xs-3')); ?>
						<div class="col-xs-4">
							<div class="input-group input-group-sm">
								<?php if (!currency_side()): ?>
									<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
								<?php endif; ?>
								<?php echo form_input(array(
										'name'=>'min',
										'id'=>'min',
										'class'=>'form-control input-sm',
										'value'=>to_currency_no_money($stats->min),
										'disabled'=>'')
										); ?>
								<?php if (currency_side()): ?>
									<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
								<?php endif; ?>
							</div>
						</div>
					</div>
					
					<div class="form-group form-group-sm">
						<?php echo form_label($this->lang->line('customers_average'), 'average', array('class' => 'control-label col-xs-3')); ?>
						<div class="col-xs-4">
							<div class="input-group input-group-sm">
								<?php if (!currency_side()): ?>
									<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
								<?php endif; ?>
								<?php echo form_input(array(
										'name'=>'average',
										'id'=>'average',
										'class'=>'form-control input-sm',
										'value'=>to_currency_no_money($stats->average),
										'disabled'=>'')
										); ?>
								<?php if (currency_side()): ?>
									<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
								<?php endif; ?>
							</div>
						</div>
					</div>
					
					<div class="form-group form-group-sm">
						<?php echo form_label($this->lang->line('customers_quantity'), 'quantity', array('class' => 'control-label col-xs-3')); ?>
						<div class="col-xs-4">
							<div class="input-group input-group-sm">
								<?php echo form_input(array(
										'name'=>'quantity',
										'id'=>'quantity',
										'class'=>'form-control input-sm',
										'value'=>$stats->quantity,
										'disabled'=>'')
										); ?>
							</div>
						</div>
					</div>

					<div class="form-group form-group-sm">
						<?php echo form_label($this->lang->line('customers_avg_discount'), 'avg_discount', array('class' => 'control-label col-xs-3')); ?>
						<div class="col-xs-3">
							<div class="input-group input-group-sm">
								<?php echo form_input(array(
										'name'=>'avg_discount',
										'id'=>'avg_discount',
										'class'=>'form-control input-sm',
										'value'=>$stats->avg_discount,
										'disabled'=>'')
										); ?>
								<span class="input-group-addon input-sm"><b>%</b></span>
							</div>
						</div>
					</div>
				</fieldset>
			</div>
		<?php
		}
		?>

		<?php
		if(!empty($mailchimp_info) && !empty($mailchimp_activity))
		{
		?>
			<div class="tab-pane" id="customer_mailchimp_info">
				<fieldset>
					<div class="form-group form-group-sm">
						<?php echo form_label($this->lang->line('customers_mailchimp_status'), 'mailchimp_status', array('class' => 'control-label col-xs-3')); ?>
						<div class='col-xs-4'>
							<?php echo form_dropdown('mailchimp_status', 
								array(
									'subscribed' => 'subscribed',
									'unsubscribed' => 'unsubscribed',
									'cleaned' => 'cleaned',
									'pending' => 'pending'
								),
								$mailchimp_info['status'],
								array('id' => 'mailchimp_status', 'class' => 'form-control input-sm')); ?>
						</div>
					</div>

					<div class="form-group form-group-sm">
						<?php echo form_label($this->lang->line('customers_mailchimp_vip'), 'mailchimp_vip', array('class' => 'control-label col-xs-3')); ?>
						<div class='col-xs-1'>
							<?php echo form_checkbox('mailchimp_vip', '1', $mailchimp_info['vip'] == '' ? FALSE : (boolean)$mailchimp_info['vip']); ?>
						</div>
					</div>

					<div class="form-group form-group-sm">
						<?php echo form_label($this->lang->line('customers_mailchimp_member_rating'), 'mailchimp_member_rating', array('class' => 'control-label col-xs-3')); ?>
						<div class='col-xs-4'>
							<?php echo form_input(array(
									'name'=>'mailchimp_member_rating',
									'class'=>'form-control input-sm',
									'value'=>$mailchimp_info['member_rating'],
									'disabled'=>'')
									); ?>
						</div>
					</div>

					<div class="form-group form-group-sm">
						<?php echo form_label($this->lang->line('customers_mailchimp_activity_total'), 'mailchimp_activity_total', array('class' => 'control-label col-xs-3')); ?>
						<div class='col-xs-4'>
							<?php echo form_input(array(
									'name'=>'mailchimp_activity_total',
									'class'=>'form-control input-sm',
									'value'=>$mailchimp_activity['total'],
									'disabled'=>'')
									); ?>
						</div>
					</div>

					<div class="form-group form-group-sm">
						<?php echo form_label($this->lang->line('customers_mailchimp_activity_lastopen'), 'mailchimp_activity_lastopen', array('class' => 'control-label col-xs-3')); ?>
						<div class='col-xs-4'>
							<?php echo form_input(array(
									'name'=>'mailchimp_activity_lastopen',
									'class'=>'form-control input-sm',
									'value'=>$mailchimp_activity['lastopen'],
									'disabled'=>'')
									); ?>
						</div>
					</div>

					<div class="form-group form-group-sm">
						<?php echo form_label($this->lang->line('customers_mailchimp_activity_open'), 'mailchimp_activity_open', array('class' => 'control-label col-xs-3')); ?>
						<div class='col-xs-4'>
							<?php echo form_input(array(
									'name'=>'mailchimp_activity_open',
									'class'=>'form-control input-sm',
									'value'=>$mailchimp_activity['open'],
									'disabled'=>'')
									); ?>
						</div>
					</div>

					<div class="form-group form-group-sm">
						<?php echo form_label($this->lang->line('customers_mailchimp_activity_click'), 'mailchimp_activity_click', array('class' => 'control-label col-xs-3')); ?>
						<div class='col-xs-4'>
							<?php echo form_input(array(
									'name'=>'mailchimp_activity_click',
									'class'=>'form-control input-sm',
									'value'=>$mailchimp_activity['click'],
									'disabled'=>'')
									); ?>
						</div>
					</div>

					<div class="form-group form-group-sm">
						<?php echo form_label($this->lang->line('customers_mailchimp_activity_unopen'), 'mailchimp_activity_unopen', array('class' => 'control-label col-xs-3')); ?>
						<div class='col-xs-4'>
							<?php echo form_input(array(
									'name'=>'mailchimp_activity_unopen',
									'class'=>'form-control input-sm',
									'value'=>$mailchimp_activity['unopen'],
									'disabled'=>'')
									); ?>
						</div>
					</div>

					<div class="form-group form-group-sm">
						<?php echo form_label($this->lang->line('customers_mailchimp_email_client'), 'mailchimp_email_client', array('class' => 'control-label col-xs-3')); ?>
						<div class='col-xs-4'>
							<?php echo form_input(array(
									'name'=>'mailchimp_email_client',
									'class'=>'form-control input-sm',
									'value'=>$mailchimp_info['email_client'],
									'disabled'=>'')
									); ?>
						</div>
					</div>
				</fieldset>
			</div>
		<?php
		}
		?>
	</div>
<?php echo form_close(); ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	$("input[name='sales_tax_code_name']").change(function() {
		if( ! $("input[name='sales_tax_code_name']").val() ) {
		    $("input[name='sales_tax_code']").val('');
		}
	});

	var fill_value = function(event, ui) {
		event.preventDefault();
		$("input[name='sales_tax_code']").val(ui.item.value);
		$("input[name='sales_tax_code_name']").val(ui.item.label);
	};

	$("#sales_tax_code_name").autocomplete({
		source: '<?php echo site_url("taxes/suggest_sales_tax_codes"); ?>',
		minChars: 0,
		delay: 15,
		cacheLength: 1,
		appendTo: '.modal-content',
		select: fill_value,
		focus: fill_value
	});

	$('#customer_form').validate($.extend({
		submitHandler: function(form)
		{
			$(form).ajaxSubmit({
				success: function(response)
				{
					dialog_support.hide();
					table_support.handle_submit('<?php echo site_url($controller_name); ?>', response);
				},
				dataType: 'json'
			});
		},

		rules:
		{
			first_name: 'required',
			last_name: 'required',
			consent: 'required',
			email:
			{
				remote:
				{
					url: "<?php echo site_url($controller_name . '/ajax_check_email')?>",
					type: 'POST',
					data: $.extend(csrf_form_base(), {
						'person_id': '<?php echo $person_info->person_id; ?>'
						// email is posted by default
					})
				}
			},
			account_number:
			{
				remote:
				{
					url: "<?php echo site_url($controller_name . '/ajax_check_account_number')?>",
					type: 'POST',
					data: $.extend(csrf_form_base(), {
						'person_id': '<?php echo $person_info->person_id; ?>'
						// account_number is posted by default
					})
				}
			}
		},

		messages:
		{
			first_name: "<?php echo $this->lang->line('common_first_name_required'); ?>",
			last_name: "<?php echo $this->lang->line('common_last_name_required'); ?>",
			consent: "<?php echo $this->lang->line('customers_consent_required'); ?>",
			email: "<?php echo $this->lang->line('customers_email_duplicate'); ?>",
			account_number: "<?php echo $this->lang->line('customers_account_number_duplicate'); ?>"
		}
	}, form_support.error));
});
</script>
