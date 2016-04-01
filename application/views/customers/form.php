<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open('customers/save/'.$person_info->person_id, array('id'=>'customer_form', 'class'=>'form-horizontal')); ?>
	<fieldset id="customer_basic_info">
		<?php $this->load->view("people/form_basic_info"); ?>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('customers_company_name'), 'company_name', array('class' => 'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_input(array(
						'name'=>'company_name',
						'class'=>'form-control input-sm',
						'value'=>$person_info->company_name)
						);?>
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
						);?>
			</div>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('customers_total'), 'total', array('class' => 'control-label col-xs-3')); ?>
			<div class="col-xs-4">
				<div class="input-group input-group-sm">
					<?php if (!$this->config->item('currency_side')): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
					<?php echo form_input(array(
							'name'=>'total',
							'id'=>'total',
							'class'=>'form-control input-sm',
							'value'=>to_currency_no_money($total),
							'disabled'=>'')
							);?>
					<?php if ($this->config->item('currency_side')): ?>
						<span class="input-group-addon input-sm"><b><?php echo $this->config->item('currency_symbol'); ?></b></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('customers_discount'), 'discount_percent', array('class' => 'control-label col-xs-3')); ?>
			<div class='col-xs-3'>
				<div class="input-group input-group-sm">
					<?php echo form_input(array(
							'name'=>'discount_percent',
							'id'=>'discount_percent',
							'class'=>'form-control input-sm',
							'value'=>$person_info->discount_percent)
							);?>
					<span class="input-group-addon input-sm"><b>%</b></span>
				</div>
			</div>	
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('customers_taxable'), 'taxable', array('class' => 'control-label col-xs-3')); ?>
			<div class='col-xs-1'>
				<?php echo form_checkbox('taxable', '1', $person_info->taxable == '' ? TRUE : (boolean)$person_info->taxable);?>
			</div>
		</div>
	</fieldset>
<?php echo form_close(); ?>

<script type='text/javascript'>

//validation and submit handling
$(document).ready(function()
{
	$('#customer_form').validate($.extend({
		submitHandler:function(form)
		{
			$(form).ajaxSubmit({
				success:function(response)
				{
					dialog_support.hide();
				table_support.handle_submit('<?php echo site_url($controller_name); ?>', response);
				},
				dataType:'json'
			});
		},
		rules:
		{
			first_name: "required",
			last_name: "required",
    		email: "email",
    		account_number:
			{
				remote:
				{
					url: "<?php echo site_url($controller_name . '/check_account_number')?>",
					type: "post",
					data:
					{
						"person_id" : "<?php echo $person_info->person_id; ?>",
						"account_number" : function()
						{
							return $("#account_number").val();
						}
					}
				}
			}
   		},
		messages: 
		{
     		first_name: "<?php echo $this->lang->line('common_first_name_required'); ?>",
     		last_name: "<?php echo $this->lang->line('common_last_name_required'); ?>",
     		email: "<?php echo $this->lang->line('common_email_invalid_format'); ?>",
			account_number: "<?php echo $this->lang->line('customers_account_number_duplicate'); ?>"
		}
	}, dialog_support.error));
});
</script>