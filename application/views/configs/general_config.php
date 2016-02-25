<div id="page_title"><?php echo $this->lang->line('config_general_configuration'); ?></div>

<?php echo form_open('config/save/',array('id'=>'config_form','enctype'=>'multipart/form-data', 'class' => 'form-horizontal')); ?>
	<div id="config_wrapper">
		<fieldset id="config_info">
			<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
			<ul id="error_message_box" class="error_message_box"></ul>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_company'), 'company', array('class'=>'control-label col-xs-2 required')); ?>
				<div class='col-xs-6'>
					<?php echo form_input(array(
						'name'=>'company',
						'id'=>'company',
						'class'=>'form-control',
						'value'=>$this->config->item('company')));?>
					
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_company_logo'), 'company_logo', array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-6'>
					<div class="fileinput fileinput-new" data-provides="fileinput">
						<div class="fileinput-new thumbnail" style="width: 200px; height: 200px;">	
							<img data-src="holder.js/100%x100%" alt="<?php echo $this->lang->line('config_company_logo'); ?>" src="<?php if($this->Appconfig->get('company_logo') != '') echo base_url('uploads/' . $this->Appconfig->get('company_logo')); else echo ''; ?>" style="max-height: 100%; max-width: 100%;">
						</div>
						<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 200px;"></div>
						<div>
							<span class="btn btn-default btn-sm btn-file">
								<span class="fileinput-new"><?php echo $this->lang->line("config_company_select_image"); ?></span>
								<span class="fileinput-exists"><?php echo $this->lang->line("config_company_change_image"); ?></span><input type="file" name="company_logo"></span>
							<a href="#" class="btn btn-default btn-sm fileinput-exists" data-dismiss="fileinput"><?php echo $this->lang->line("config_company_remove_image"); ?></a>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_address'), 'address', array('class'=>'control-label col-xs-2  required')); ?>
				<div class='col-xs-6'>
					<?php echo form_textarea(array(
						'name'=>'address',
						'id'=>'address',
						'class'=>'form-control',
						'value'=>$this->config->item('address')));?>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_website'), 'website',array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-6'>
					<?php echo form_input(array(
						'name'=>'website',
						'id'=>'website',
						'class'=>'form-control',
						'value'=>$this->config->item('website')));?>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('common_email'), 'email',array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-6'>
					<?php echo form_input(array(
						'name'=>'email',
						'id'=>'email',
						'type'=>'email',
						'class'=>'form-control',
						'value'=>$this->config->item('email')));?>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_phone'), 'phone',array('class'=>'control-label col-xs-2  required')); ?>
				<div class='col-xs-6'>
					<?php echo form_input(array(
						'name'=>'phone',
						'id'=>'phone',
						'class'=>'form-control',
						'value'=>$this->config->item('phone')));?>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_fax'), 'fax',array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-6'>
					<?php echo form_input(array(
						'name'=>'fax',
						'id'=>'fax',
						'class'=>'form-control',
						'value'=>$this->config->item('fax')));?>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('common_return_policy'), 'return_policy',array('class'=>'control-label col-xs-2  required')); ?>
				<div class='col-xs-6'>
					<?php echo form_textarea(array(
						'name'=>'return_policy',
						'id'=>'return_policy',
						'class'=>'form-control',
						'value'=>$this->config->item('return_policy')));?>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_default_tax_rate_1'), 'default_tax_1_rate',array('class'=>'control-label col-xs-2 required')); ?>
				<div class='col-sm-2'>
					<?php echo form_input(array(
						'name'=>'default_tax_1_name',
						'id'=>'default_tax_1_name',
						'class'=>'form-control',
						'value'=>$this->config->item('default_tax_1_name')!==FALSE ? $this->config->item('default_tax_1_name') : $this->lang->line('items_sales_tax_1')));?>
				</div>
				<div class="col-sm-1 input-group">
					<?php echo form_input(array(
						'name'=>'default_tax_1_rate',
						'id'=>'default_tax_1_rate',
						'class'=>'form-control',
						'value'=>$this->config->item('default_tax_1_rate')));?>
					<span class="input-group-addon">%</span>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_default_tax_rate_2'), 'default_tax_1_rate',array('class'=>'control-label col-xs-2')); ?>
				<div class='col-sm-2'>
					<?php echo form_input(array(
						'name'=>'default_tax_2_name',
						'id'=>'default_tax_2_name',
						'class'=>'form-control',
						'value'=>$this->config->item('default_tax_2_name')!==FALSE ? $this->config->item('default_tax_2_name') : $this->lang->line('items_sales_tax_2')));?>
				</div>
				<div class="col-sm-1 input-group">
					<?php echo form_input(array(
						'name'=>'default_tax_2_rate',
						'id'=>'default_tax_2_rate',
						'class'=>'form-control',
						'value'=>$this->config->item('default_tax_2_rate')));?>
					<span class="input-group-addon">%</span>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_tax_included'), 'tax_included',array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_checkbox(array(
						'name'=>'tax_included',
						'id'=>'tax_included',
						'value'=>'tax_included',
						'checked'=>$this->config->item('tax_included')));?>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_default_sales_discount'), 'default_sales_discount',array('class'=>'control-label col-xs-2  required')); ?>
				<div class='col-xs-2'>
					<div class="input-group">
					<?php echo form_input(array(
						'name'=>'default_sales_discount',
						'id'=>'default_sales_discount',
						'class'=>'form-control',
						'type'=>'number',
						'min'=>0,
						'max'=>100,
						'value'=>$this->config->item('default_sales_discount')));?>
					<span class="input-group-addon">%</span>
						</div>
				</div>
			</div>

			<div class="form-group">    
			<?php echo form_label($this->lang->line('config_sales_invoice_format'), 'sales_invoice_format',array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name'=>'sales_invoice_format',
						'id'=>'sales_invoice_format',
						'class'=>'form-control',
						'value'=>$this->config->item('sales_invoice_format'))); ?>
				</div>
			</div>

			<div class="form-group">    
			<?php echo form_label($this->lang->line('config_recv_invoice_format'), 'recv_invoice_format',array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name'=>'recv_invoice_format',
						'id'=>'recv_invoice_format',
						'class'=>'form-control',
						'value'=>$this->config->item('recv_invoice_format'))); ?>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_receiving_calculate_average_price'), 'receiving_calculate_average_price',array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox(array(
						'name'=>'receiving_calculate_average_price',
						'id'=>'receiving_calculate_average_price',
						'value'=>'receiving_calculate_average_price',
						'checked'=>$this->config->item('receiving_calculate_average_price')));?>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_lines_per_page'), 'lines_per_page',array('class'=>'control-label col-xs-2  required')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name'=>'lines_per_page',
						'id'=>'lines_per_page',
						'class'=>'form-control',
						'type'=>'number',
						'min'=>10,
						'max'=>1000,
						'value'=>$this->config->item('lines_per_page')));?>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_custom1'), 'config_custom1',array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name'=>'custom1_name',
						'id'=>'custom1_name',
						'class'=>'form-control',
						'value'=>$this->config->item('custom1_name')));?>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_custom2'), 'config_custom2',array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name'=>'custom2_name',
						'id'=>'custom2_name',
						'class'=>'form-control',
						'value'=>$this->config->item('custom2_name')));?>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_custom3'), 'config_custom3',array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name'=>'custom3_name',
						'id'=>'custom3_name',
						'class'=>'form-control',
						'value'=>$this->config->item('custom3_name')));?>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_custom4'), 'config_custom4',array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name'=>'custom4_name',
						'id'=>'custom4_name',
						'class'=>'form-control',
						'value'=>$this->config->item('custom4_name')));?>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_custom5'), 'config_custom5',array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name'=>'custom5_name',
						'id'=>'custom5_name',
						'class'=>'form-control',
						'value'=>$this->config->item('custom5_name')));?>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_custom6'), 'config_custom6',array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name'=>'custom6_name',
						'id'=>'custom6_name',
						'class'=>'form-control',
						'value'=>$this->config->item('custom6_name')));?>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_custom7'), 'config_custom7',array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name'=>'custom7_name',
						'id'=>'custom7_name',
						'class'=>'form-control',
						'value'=>$this->config->item('custom7_name')));?>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_custom8'), 'config_custom8',array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name'=>'custom8_name',
						'id'=>'custom8_name',
						'class'=>'form-control',
						'value'=>$this->config->item('custom8_name')));?>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_custom9'), 'config_custom9',array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name'=>'custom9_name',
						'id'=>'custom9_name',
						'class'=>'form-control',
						'value'=>$this->config->item('custom9_name')));?>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_custom10'), 'config_custom10',array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name'=>'custom10_name',
						'id'=>'custom10_name',
						'class'=>'form-control',
						'value'=>$this->config->item('custom10_name')));?>
				</div>
			</div>

			<div class="form-group">	
			<?php echo form_label($this->lang->line('config_backup_database'), 'config_backup_database',array('class'=>'control-label col-xs-2')); ?>
				<div class="col-xs-2">
					<div id="backup_db" class="btn btn-default btn-sm">
						<span style="top:22%;"><?php echo $this->lang->line('config_backup_button'); ?></span>
					</div>
				</div>
			</div>

			<?php echo form_submit(array(
				'name'=>'submit_form',
				'id'=>'submit_form',
				'value'=>$this->lang->line('common_submit'),
				'class'=>'btn btn-primary btn-sm pull-right'));?>
		</fieldset>
	</div>
<?php echo form_close(); ?>

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
					set_feedback(response.message, 'alert alert-dismissible alert-success', false);		
				}
				else
				{
					set_feedback(response.message, 'alert alert-dismissible alert-danger', true);		
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
