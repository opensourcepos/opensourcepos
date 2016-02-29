<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open('customers/save/'.$person_info->person_id, array('id'=>'customer_form', 'class'=>'form-horizontal')); ?>
	<fieldset id="customer_basic_info">
		<?php $this->load->view("people/form_basic_info"); ?>

		<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('customers_company_name'), 'company_name', array('class' => 'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<?php echo form_input(array(
					'name'=>'company_name',
					'class'=>'form-control input-sm',
					'value'=>$person_info->company_name)
					);?>
			</div>
		</div>

		<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('customers_account_number'), 'account_number', array('class' => 'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<?php echo form_input(array(
					'name'=>'account_number',
					'id'=>'account_number',
					'class'=>'account_number form-control',
					'value'=>$person_info->account_number)
					);?>
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

	$.validator.addMethod("account_number", function(value, element) 
	{
		return JSON.parse($.ajax(
		{
			  type: 'POST',
			  url: '<?php echo site_url($controller_name . "/check_account_number")?>',
			  data: {'person_id' : '<?php echo $person_info->person_id; ?>', 'account_number' : $(element).val() },
			  success: function(response) 
			  {
				  success=response.success;
			  },
			  async:false,
			  dataType: 'json'
        }).responseText).success;
        
    }, '<?php echo $this->lang->line("customers_account_number_duplicate"); ?>');

	$('#customer_form').validate($.extend({
		submitHandler:function(form)
		{
			$(form).ajaxSubmit({
			success:function(response)
			{
				dialog_support.hide();
				post_person_form_submit(response);
			},
			dataType:'json'
		});

		},
		errorLabelContainer: "#error_message_box",
 		wrapper: "li",
		rules: 
		{
			first_name: "required",
			last_name: "required",
    		email: "email",
    		account_number: { account_number: true }
   		},
		messages: 
		{
     		first_name: "<?php echo $this->lang->line('common_first_name_required'); ?>",
     		last_name: "<?php echo $this->lang->line('common_last_name_required'); ?>",
     		email: "<?php echo $this->lang->line('common_email_invalid_format'); ?>"
		}
	}, dialog_support.error));
});
</script>