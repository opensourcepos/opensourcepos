<?php
/**
 * @var string $controller_name
 * @var object $person_info
 * @var array $packages
 * @var int $selected_package
 * @var bool $use_destination_based_tax
 * @var string $sales_tax_code_label
 * @var string $employee
 */
?>
<div id="required_fields_message"><?php echo lang('Common.fields_required_message') ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open("$controller_name/save/$person_info->person_id", ['id' => 'customer_form', 'class' => 'form-horizontal']) ?>
	<ul class="nav nav-tabs nav-justified" data-tabs="tabs">
		<li class="active" role="presentation">
			<a data-toggle="tab" href="#customer_basic_info"><?php echo lang('Customers.basic_information') ?></a>
		</li>
		<?php
		if(!empty($stats))
		{
		?>
			<li role="presentation">
				<a data-toggle="tab" href="#customer_stats_info"><?php echo lang('Customers.stats_info') ?></a>
			</li>
		<?php
		}
		?>
		<?php
		if(!empty($mailchimp_info) && !empty($mailchimp_activity))
		{
		?>
			<li role="presentation">
				<a data-toggle="tab" href="#customer_mailchimp_info"><?php echo lang('Customers.mailchimp_info') ?></a>
			</li>
		<?php
		}
		?>
	</ul>

	<div class="tab-content">
		<div class="tab-pane fade in active" id="customer_basic_info">
			<fieldset>
				<div class="form-group form-group-sm">
					<?php echo form_label(lang('Customers.consent'), 'consent', ['class' => 'required control-label col-xs-3']) ?>
					<div class='col-xs-1'>
						<?php echo form_checkbox('consent', '1', $person_info->consent == '' ? !config('OSPOS')->settings['enforce_privacy'] : (boolean)$person_info->consent) ?>
					</div>
				</div>

				<?php echo view('people/form_basic_info') ?>
				
				<div class="form-group form-group-sm">
					<?php echo form_label(lang('Customers.discount_type'), 'discount_type', ['class' => 'control-label col-xs-3']) ?>
					<div class="col-xs-8">
						<label class="radio-inline">
							<?php echo form_radio ([
									'name' => 'discount_type',
									'type' => 'radio',
									'id' => 'discount_type',
									'value' => 0,
									'checked' => $person_info->discount_type == PERCENT
								]
							) ?> <?php echo lang('Customers.discount_percent') ?>
						</label>
						<label class="radio-inline">
							<?php echo form_radio ([
									'name' => 'discount_type',
									'type' => 'radio',
									'id' => 'discount_type',
									'value' => 1,
									'checked' => $person_info->discount_type == FIXED
								]
							) ?> <?php echo lang('Customers.discount_fixed') ?>
						</label>
					</div>
				</div>

				<div class="form-group form-group-sm">
					<?php echo form_label(lang('Customers.discount'), 'discount', ['class' => 'control-label col-xs-3']) ?>
					<div class='col-xs-3'>
						<div class="input-group input-group-sm">
							<?php echo form_input ([
									'name' => 'discount',
									'id' => 'discount',
									'class' => 'form-control input-sm',
									'onClick' => 'this.select();',
									'value' => $person_info->discount
								]) ?>
						</div>
					</div>	
				</div>

				<div class="form-group form-group-sm">
					<?php echo form_label(lang('Customers.company_name'), 'company_name', ['class' => 'control-label col-xs-3']) ?>
					<div class='col-xs-8'>
						<?php echo form_input ([
								'name' => 'company_name',
								'id' => 'company_name',
								'class' => 'form-control input-sm',
								'value' => esc($person_info->company_name, 'attr')
							]) ?>
					</div>
				</div>

				<div class="form-group form-group-sm">
					<?php echo form_label(lang('Customers.account_number'), 'account_number', ['class' => 'control-label col-xs-3']) ?>
					<div class='col-xs-4'>
						<?php echo form_input ([
								'name' => 'account_number',
								'id' => 'account_number',
								'class' => 'form-control input-sm',
								'value' => esc($person_info->account_number, 'attr')
							]) ?>
					</div>
				</div>

				<div class="form-group form-group-sm">
					<?php echo form_label(lang('Customers.tax_id'), 'tax_id', ['class' => 'control-label col-xs-3']) ?>
					<div class='col-xs-4'>
						<?php echo form_input ([
								'name' => 'tax_id',
								'id' => 'tax_id',
								'class' => 'form-control input-sm',
								'value' => esc($person_info->tax_id, 'attr')
							]) ?>
					</div>
				</div>

				<?php if(config('OSPOS')->settings['customer_reward_enable'] == TRUE): ?>
					<div class="form-group form-group-sm">
						<?php echo form_label(lang('Customers.rewards_package'), 'rewards', ['class' => 'control-label col-xs-3']) ?>
						<div class='col-xs-8'>
							<?php echo form_dropdown('package_id', esc($packages, 'attr'), $selected_package, ['class' => 'form-control']) ?>
						</div>
					</div>

					<div class="form-group form-group-sm">
						<?php echo form_label(lang('Customers.available_points'), 'available_points', ['class' => 'control-label col-xs-3']) ?>
						<div class='col-xs-4'>
							<?php echo form_input ([
									'name' => 'available_points',
									'id' => 'available_points',
									'class' => 'form-control input-sm',
									'value' => $person_info->points,
									'disabled' => ''
								]) ?>
						</div>
					</div>
				<?php endif; ?>

				<div class="form-group form-group-sm">
					<?php echo form_label(lang('Customers.taxable'), 'taxable', ['class' => 'control-label col-xs-3']) ?>
					<div class='col-xs-1'>
						<?php echo form_checkbox('taxable', '1', $person_info->taxable == '' || $person_info->taxable) ?>
					</div>
				</div>

				<?php
				if($use_destination_based_tax)
				{
				?>
					<div class="form-group form-group-sm">
						<?php echo form_label(lang('Customers.tax_code'), 'sales_tax_code_name', ['class' => 'control-label col-xs-3']) ?>
						<div class='col-xs-8'>
							<div class="input-group input-group-sm">
								<?php echo form_input ([
										'name' => 'sales_tax_code_name',
										'id' => 'sales_tax_code_name',
										'class' => 'form-control input-sm',
										'size' => '50',
										'value' => esc($sales_tax_code_label, 'attr')
								]) ?>
								<?php echo form_hidden('sales_tax_code_id', $person_info->sales_tax_code_id) ?>
							</div>
						</div>
					</div>
				<?php
				}
				?>

				<div class="form-group form-group-sm">
					<?php echo form_label(lang('Customers.date'), 'date', ['class' => 'control-label col-xs-3']) ?>
					<div class='col-xs-8'>
						<div class="input-group">
							<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-calendar"></span></span>
							<?php echo form_input ([
									'name' => 'date',
									'id' => 'datetime',
									'class' => 'form-control input-sm',
									'value' => to_datetime(strtotime($person_info->date)),
									'readonly' => 'true'
								]) ?>
						</div>
					</div>
				</div>

				<div class="form-group form-group-sm">
					<?php echo form_label(lang('Customers.employee'), 'employee', ['class' => 'control-label col-xs-3']) ?>
					<div class='col-xs-8'>
						<?php echo form_input ([
								'name' => 'employee',
								'id' => 'employee',
								'class' => 'form-control input-sm',
								'value' => esc($employee, 'attr'),
								'readonly' => 'true'
							]) ?>
					</div>
				</div>

				<?php echo form_hidden('employee_id', $person_info->employee_id) ?>
			</fieldset>
		</div>

		<?php
		if(!empty($stats))
		{
		?>
			<div class="tab-pane" id="customer_stats_info">
				<fieldset>
					<div class="form-group form-group-sm">
						<?php echo form_label(lang('Customers.total'), 'total', ['class' => 'control-label col-xs-3']) ?>
						<div class="col-xs-4">
							<div class="input-group input-group-sm">
								<?php if (!currency_side()): ?>
									<span class="input-group-addon input-sm"><b><?php echo esc(config('OSPOS')->settings['currency_symbol']) ?></b></span>
								<?php endif; ?>
								<?php echo form_input ([
										'name' => 'total',
										'id' => 'total',
										'class' => 'form-control input-sm',
										'value' => to_currency_no_money($stats->total),
										'disabled' => ''
								]) ?>
								<?php if (currency_side()): ?>
									<span class="input-group-addon input-sm"><b><?php echo esc(config('OSPOS')->settings['currency_symbol']) ?></b></span>
								<?php endif; ?>
							</div>
						</div>
					</div>
					
					<div class="form-group form-group-sm">
						<?php echo form_label(lang('Customers.max'), 'max', ['class' => 'control-label col-xs-3']) ?>
						<div class="col-xs-4">
							<div class="input-group input-group-sm">
								<?php if (!currency_side()): ?>
									<span class="input-group-addon input-sm"><b><?php echo esc(config('OSPOS')->settings['currency_symbol']) ?></b></span>
								<?php endif; ?>
								<?php echo form_input ([
										'name' => 'max',
										'id' => 'max',
										'class' => 'form-control input-sm',
										'value' => to_currency_no_money($stats->max),
										'disabled' => ''
									]) ?>
								<?php if (currency_side()): ?>
									<span class="input-group-addon input-sm"><b><?php echo esc(config('OSPOS')->settings['currency_symbol']) ?></b></span>
								<?php endif; ?>
							</div>
						</div>
					</div>
					
					<div class="form-group form-group-sm">
						<?php echo form_label(lang('Customers.min'), 'min', ['class' => 'control-label col-xs-3']) ?>
						<div class="col-xs-4">
							<div class="input-group input-group-sm">
								<?php if (!currency_side()): ?>
									<span class="input-group-addon input-sm"><b><?php echo esc(config('OSPOS')->settings['currency_symbol']) ?></b></span>
								<?php endif; ?>
								<?php echo form_input ([
										'name' => 'min',
										'id' => 'min',
										'class' => 'form-control input-sm',
										'value' => to_currency_no_money($stats->min),
										'disabled' => ''
									]) ?>
								<?php if (currency_side()): ?>
									<span class="input-group-addon input-sm"><b><?php echo esc(config('OSPOS')->settings['currency_symbol']) ?></b></span>
								<?php endif; ?>
							</div>
						</div>
					</div>
					
					<div class="form-group form-group-sm">
						<?php echo form_label(lang('Customers.average'), 'average', ['class' => 'control-label col-xs-3']) ?>
						<div class="col-xs-4">
							<div class="input-group input-group-sm">
								<?php if (!currency_side()): ?>
									<span class="input-group-addon input-sm"><b><?php echo esc(config('OSPOS')->settings['currency_symbol']) ?></b></span>
								<?php endif; ?>
								<?php echo form_input ([
										'name' => 'average',
										'id' => 'average',
										'class' => 'form-control input-sm',
										'value' => to_currency_no_money($stats->average),
										'disabled' => ''
									]) ?>
								<?php if (currency_side()): ?>
									<span class="input-group-addon input-sm"><b><?php echo esc(config('OSPOS')->settings['currency_symbol']) ?></b></span>
								<?php endif; ?>
							</div>
						</div>
					</div>
					
					<div class="form-group form-group-sm">
						<?php echo form_label(lang('Customers.quantity'), 'quantity', ['class' => 'control-label col-xs-3']) ?>
						<div class="col-xs-4">
							<div class="input-group input-group-sm">
								<?php echo form_input ([
										'name' => 'quantity',
										'id' => 'quantity',
										'class' => 'form-control input-sm',
										'value' => $stats->quantity,
										'disabled' => ''
									]) ?>
							</div>
						</div>
					</div>

					<div class="form-group form-group-sm">
						<?php echo form_label(lang('Customers.avg_discount'), 'avg_discount', ['class' => 'control-label col-xs-3']) ?>
						<div class="col-xs-3">
							<div class="input-group input-group-sm">
								<?php echo form_input ([
										'name' => 'avg_discount',
										'id' => 'avg_discount',
										'class' => 'form-control input-sm',
										'value' => $stats->avg_discount,
										'disabled' => ''
									]) ?>
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
						<?php echo form_label(lang('Customers.mailchimp_status'), 'mailchimp_status', ['class' => 'control-label col-xs-3']) ?>
						<div class='col-xs-4'>
							<?php echo form_dropdown('mailchimp_status', 
								[
									'subscribed' => 'subscribed',
									'unsubscribed' => 'unsubscribed',
									'cleaned' => 'cleaned',
									'pending' => 'pending'
								],
								esc($mailchimp_info['status'], 'attr'),
								['id' => 'mailchimp_status', 'class' => 'form-control input-sm']) ?>
						</div>
					</div>

					<div class="form-group form-group-sm">
						<?php echo form_label(lang('Customers.mailchimp_vip'), 'mailchimp_vip', ['class' => 'control-label col-xs-3']) ?>
						<div class='col-xs-1'>
							<?php echo form_checkbox('mailchimp_vip', '1', !($mailchimp_info['vip'] == '') && $mailchimp_info['vip']) ?>
						</div>
					</div>

					<div class="form-group form-group-sm">
						<?php echo form_label(lang('Customers.mailchimp_member_rating'), 'mailchimp_member_rating', ['class' => 'control-label col-xs-3']) ?>
						<div class='col-xs-4'>
							<?php echo form_input ([
									'name' => 'mailchimp_member_rating',
									'class' => 'form-control input-sm',
									'value' => esc($mailchimp_info['member_rating'], 'attr'),
									'disabled' => ''
								]) ?>
						</div>
					</div>

					<div class="form-group form-group-sm">
						<?php echo form_label(lang('Customers.mailchimp_activity_total'), 'mailchimp_activity_total', ['class' => 'control-label col-xs-3']) ?>
						<div class='col-xs-4'>
							<?php echo form_input ([
									'name' => 'mailchimp_activity_total',
									'class' => 'form-control input-sm',
									'value' => esc($mailchimp_activity['total'], 'attr'),
									'disabled' => ''
								]) ?>
						</div>
					</div>

					<div class="form-group form-group-sm">
						<?php echo form_label(lang('Customers.mailchimp_activity_lastopen'), 'mailchimp_activity_lastopen', ['class' => 'control-label col-xs-3']) ?>
						<div class='col-xs-4'>
							<?php echo form_input ([
									'name' => 'mailchimp_activity_lastopen',
									'class' => 'form-control input-sm',
									'value' => esc($mailchimp_activity['lastopen'], 'attr'),
									'disabled' => ''
								]) ?>
						</div>
					</div>

					<div class="form-group form-group-sm">
						<?php echo form_label(lang('Customers.mailchimp_activity_open'), 'mailchimp_activity_open', ['class' => 'control-label col-xs-3']) ?>
						<div class='col-xs-4'>
							<?php echo form_input ([
									'name' => 'mailchimp_activity_open',
									'class' => 'form-control input-sm',
									'value' => esc($mailchimp_activity['open'], 'attr'),
									'disabled' => ''
								]) ?>
						</div>
					</div>

					<div class="form-group form-group-sm">
						<?php echo form_label(lang('Customers.mailchimp_activity_click'), 'mailchimp_activity_click', ['class' => 'control-label col-xs-3']) ?>
						<div class='col-xs-4'>
							<?php echo form_input ([
									'name' => 'mailchimp_activity_click',
									'class' => 'form-control input-sm',
									'value' => esc($mailchimp_activity['click'], 'attr'),
									'disabled' => ''
								]) ?>
						</div>
					</div>

					<div class="form-group form-group-sm">
						<?php echo form_label(lang('Customers.mailchimp_activity_unopen'), 'mailchimp_activity_unopen', ['class' => 'control-label col-xs-3']) ?>
						<div class='col-xs-4'>
							<?php echo form_input ([
									'name' => 'mailchimp_activity_unopen',
									'class' => 'form-control input-sm',
									'value' => esc($mailchimp_activity['unopen'], 'attr'),
									'disabled' => ''
								]) ?>
						</div>
					</div>

					<div class="form-group form-group-sm">
						<?php echo form_label(lang('Customers.mailchimp_email_client'), 'mailchimp_email_client', ['class' => 'control-label col-xs-3']) ?>
						<div class='col-xs-4'>
							<?php echo form_input ([
									'name' => 'mailchimp_email_client',
									'class' => 'form-control input-sm',
									'value' => esc($mailchimp_info['email_client'], 'attr'),
									'disabled' => ''
								]) ?>
						</div>
					</div>
				</fieldset>
			</div>
		<?php
		}
		?>
	</div>
<?php echo form_close() ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	$("input[name='sales_tax_code_name']").change(function() {
		if( ! $("input[name='sales_tax_code_name']").val() ) {
			$("input[name='sales_tax_code_id']").val('');
		}
	});

	var fill_value = function(event, ui) {
		event.preventDefault();
		$("input[name='sales_tax_code_id']").val(ui.item.value);
		$("input[name='sales_tax_code_name']").val(ui.item.label);
	};

	$('#sales_tax_code_name').autocomplete({
		source: "<?php echo esc(site_url('taxes/suggest_tax_codes'), 'url') ?>",
		minChars: 0,
		delay: 15,
		cacheLength: 1,
		appendTo: '.modal-content',
		select: fill_value,
		focus: fill_value
	});

	$('#customer_form').validate($.extend({
		submitHandler: function(form) {
			$(form).ajaxSubmit({
				success: function(response)
				{
					dialog_support.hide();
					table_support.handle_submit("<?php echo esc(site_url($controller_name), 'url') ?>", response);
				},
				dataType: 'json'
			});
		},

		errorLabelContainer: '#error_message_box',

		rules:
		{
			first_name: 'required',
			last_name: 'required',
			consent: 'required',
			email:
			{
				remote:
				{
					url: "<?php echo esc(site_url("$controller_name/ajax_check_email"), 'url') ?>",
					type: 'POST',
					data: {
						'person_id': "<?php echo $person_info->person_id ?>"
						// email is posted by default
					}
				}
			},
			account_number:
			{
				remote:
				{
					url: "<?php echo esc(site_url("$controller_name/ajax_check_account_number"), 'url') ?>",
					type: 'POST',
					data: {
						'person_id': "<?php echo $person_info->person_id ?>"
						// account_number is posted by default
					}
				}
			}
		},

		messages:
		{
			first_name: "<?php echo lang('Common.first_name_required') ?>",
			last_name: "<?php echo lang('Common.last_name_required') ?>",
			consent: "<?php echo lang('Customers.consent_required') ?>",
			email: "<?php echo lang('Customers.email_duplicate') ?>",
			account_number: "<?php echo lang('Customers.account_number_duplicate') ?>"
		}
	}, form_support.error));
});
</script>
