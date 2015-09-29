<div id="page_title"><?php echo $this->lang->line('config_general_configuration'); ?></div>
<?php
echo form_open('config/save/',array('id'=>'config_form','enctype'=>'multipart/form-data'));
?>
<div id="config_wrapper">
<fieldset id="config_info">
<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
<ul id="error_message_box" class="error_message_box"></ul>
<legend><?php echo $this->lang->line("config_info"); ?></legend>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_company').':', 'company',array('class'=>'wide required')); ?>
	<div class='form_field'>
		<?php echo form_input(array(
			'name'=>'company',
			'id'=>'company',
			'value'=>$this->config->item('company')));?>
		
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_company_logo').':', 'company_logo',array('class'=>'wide')); ?>
	<div class='form_field'>
	    <?php echo form_upload('company_logo');?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_address').':', 'address',array('class'=>'wide required')); ?>
	<div class='form_field'>
	<?php echo form_textarea(array(
		'name'=>'address',
		'id'=>'address',
		'rows'=>4,
		'cols'=>17,
		'value'=>$this->config->item('address')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_website').':', 'website',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'website',
		'id'=>'website',
		'value'=>$this->config->item('website')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('common_email').':', 'email',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'email',
		'id'=>'email',
		'value'=>$this->config->item('email')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_phone').':', 'phone',array('class'=>'wide required')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'phone',
		'id'=>'phone',
		'value'=>$this->config->item('phone')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_fax').':', 'fax',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'fax',
		'id'=>'fax',
		'value'=>$this->config->item('fax')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('common_return_policy').':', 'return_policy',array('class'=>'wide required')); ?>
	<div class='form_field'>
	<?php echo form_textarea(array(
		'name'=>'return_policy',
		'id'=>'return_policy',
		'rows'=>'4',
		'cols'=>'17',
		'value'=>$this->config->item('return_policy')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_default_tax_rate_1').':', 'default_tax_1_rate',array('class'=>'wide required')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'default_tax_1_name',
		'id'=>'default_tax_1_name',
		'size'=>'10',
		'value'=>$this->config->item('default_tax_1_name')!==FALSE ? $this->config->item('default_tax_1_name') : $this->lang->line('items_sales_tax_1')));?>
		
	<?php echo form_input(array(
		'name'=>'default_tax_1_rate',
		'id'=>'default_tax_1_rate',
		'size'=>'4',
		'value'=>$this->config->item('default_tax_1_rate')));?>%
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_default_tax_rate_2').':', 'default_tax_1_rate',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'default_tax_2_name',
		'id'=>'default_tax_2_name',
		'size'=>'10',
		'value'=>$this->config->item('default_tax_2_name')!==FALSE ? $this->config->item('default_tax_2_name') : $this->lang->line('items_sales_tax_2')));?>
		
	<?php echo form_input(array(
		'name'=>'default_tax_2_rate',
		'id'=>'default_tax_2_rate',
		'size'=>'4',
		'value'=>$this->config->item('default_tax_2_rate')));?>%
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_tax_included').':', 'tax_included',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_checkbox(array(
		'name'=>'tax_included',
		'id'=>'tax_included',
		'value'=>'tax_included',
		'checked'=>$this->config->item('tax_included')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_default_sales_discount').':', 'default_sales_discount',array('class'=>'wide required')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'default_sales_discount',
		'id'=>'default_sales_discount',
		'type'=>'number',
		'min'=>0,
		'max'=>100,
		'value'=>$this->config->item('default_sales_discount')));?>
	</div>
</div>

<div class="field_row clearfix">    
<?php echo form_label($this->lang->line('config_sales_invoice_format').':', 'sales_invoice_format',array('class'=>'wide')); ?>
    <div class='form_field'>
    <?php echo form_input(array(
        'name'=>'sales_invoice_format',
        'id'=>'sales_invoice_format',
        'value'=>$this->config->item('sales_invoice_format'))); ?>
    </div>
</div>

<div class="field_row clearfix">    
<?php echo form_label($this->lang->line('config_recv_invoice_format').':', 'recv_invoice_format',array('class'=>'wide')); ?>
    <div class='form_field'>
    <?php echo form_input(array(
        'name'=>'recv_invoice_format',
        'id'=>'recv_invoice_format',
        'value'=>$this->config->item('recv_invoice_format'))); ?>
    </div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_receiving_calculate_average_price').':', 'receiving_calculate_average_price',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_checkbox(array(
		'name'=>'receiving_calculate_average_price',
		'id'=>'receiving_calculate_average_price',
		'value'=>'receiving_calculate_average_price',
		'checked'=>$this->config->item('receiving_calculate_average_price')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_lines_per_page').':', 'lines_per_page',array('class'=>'wide required')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'lines_per_page',
		'id'=>'lines_per_page',
		'type'=>'number',
		'min'=>10,
		'max'=>1000,
		'value'=>$this->config->item('lines_per_page')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_custom1').':', 'config_custom1',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'custom1_name',
		'id'=>'custom1_name',
		'value'=>$this->config->item('custom1_name')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_custom2').':', 'config_custom2',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'custom2_name',
		'id'=>'custom2_name',
		'value'=>$this->config->item('custom2_name')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_custom3').':', 'config_custom3',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'custom3_name',
		'id'=>'custom3_name',
		'value'=>$this->config->item('custom3_name')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_custom4').':', 'config_custom4',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'custom4_name',
		'id'=>'custom4_name',
		'value'=>$this->config->item('custom4_name')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_custom5').':', 'config_custom5',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'custom5_name',
		'id'=>'custom5_name',
		'value'=>$this->config->item('custom5_name')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_custom6').':', 'config_custom6',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'custom6_name',
		'id'=>'custom6_name',
		'value'=>$this->config->item('custom6_name')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_custom7').':', 'config_custom7',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'custom7_name',
		'id'=>'custom7_name',
		'value'=>$this->config->item('custom7_name')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_custom8').':', 'config_custom8',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'custom8_name',
		'id'=>'custom8_name',
		'value'=>$this->config->item('custom8_name')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_custom9').':', 'config_custom9',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'custom9_name',
		'id'=>'custom9_name',
		'value'=>$this->config->item('custom9_name')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_custom10').':', 'config_custom10',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'custom10_name',
		'id'=>'custom10_name',
		'value'=>$this->config->item('custom10_name')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_backup_database').':', 'config_backup_database',array('class'=>'wide')); ?>
	<div id="backup_db" class="form_field small_button" style="background-color:transparent;">
		<span style="top:22%;"><?php echo $this->lang->line('config_backup_button'); ?></span>
	</div>
</div>

<?php 
echo form_submit(array(
	'name'=>'submit_form',
	'id'=>'submit_form',
	'value'=>$this->lang->line('common_submit'),
	'class'=>'submit_button float_right')
);
?>
</fieldset>
</div>
<?php
echo form_close();
?>

<script type='text/javascript'>

//validation and submit handling
$(document).ready(function()
{
	$("#backup_db").click(function() {
		window.location='<?php echo site_url('config/backup_db') ?>';
	});
	
	$('#config_form').validate({
		submitHandler:function(form)
		{
			$(form).ajaxSubmit({
			success:function(response)
			{
				if(response.success)
				{
					set_feedback(response.message,'success_message',false);		
				}
				else
				{
					set_feedback(response.message,'error_message',true);		
				}
			},
			dataType:'json'
		});

		},
		errorLabelContainer: "#error_message_box",
 		wrapper: "li",
		rules: 
		{
			company: "required",
			address: "required",
    		phone: "required",
    		default_tax_rate:
    		{
    			required:true,
    			number:true
    		},
    		email:"email",
    		return_policy: "required",
    		lines_per_page:
    		{
        		required: true,
        		number: true
    		},
    		default_sales_discount: 
        	{
        		required: true,
        		number: true
    		}  		
   		},
		messages: 
		{
     		company: "<?php echo $this->lang->line('config_company_required'); ?>",
     		address: "<?php echo $this->lang->line('config_address_required'); ?>",
     		phone: "<?php echo $this->lang->line('config_phone_required'); ?>",
     		default_tax_rate:
    		{
    			required:"<?php echo $this->lang->line('config_default_tax_rate_required'); ?>",
    			number:"<?php echo $this->lang->line('config_default_tax_rate_number'); ?>"
    		},
     		email: "<?php echo $this->lang->line('common_email_invalid_format'); ?>",
     		return_policy:"<?php echo $this->lang->line('config_return_policy_required'); ?>",
     		default_sales_discount:
         	{
             	required: "<?php echo $this->lang->line('config_default_sales_discount_required'); ?>",
             	number :"<?php echo $this->lang->line('config_default_sales_discount_number'); ?>"
         	},
     		lines_per_page: 
         	{
            	required: "<?php echo $this->lang->line('config_lines_per_page_required'); ?>",
                number: "<?php echo $this->lang->line('config_lines_per_page_number'); ?>"
            }
		}
	});
});
</script>
