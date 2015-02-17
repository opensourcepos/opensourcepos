<div id="page_title"><?php echo $this->lang->line('config_receipt_configuration'); ?></div>
<?php
echo form_open('config/save_receipt/',array('id'=>'receipt_config_form'));
?>
<div id="config_wrapper">
<fieldset id="config_info">
<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
<ul id="receipt_error_message_box" class="error_message_box"></ul>
<legend><?php echo $this->lang->line("config_receipt_info"); ?></legend>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_receipt_show_taxes').':', 'config_receipt_show_taxes',array('class'=>'wide')); ?>
	<div class='form_field'>
		<?php 
			echo form_checkbox(array(
				'name'=>'receipt_show_taxes',
				'id'=>'receipt_show_taxes',
				'checked'=>$this->config->item('receipt_show_taxes')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_print_after_sale').':', 'print_after_sale',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_checkbox(array(
		'name'=>'print_after_sale',
		'id'=>'print_after_sale',
		'value'=>'print_after_sale',
		'checked'=>$this->config->item('print_after_sale')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_print_silently').':', 'print_silently',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_checkbox(array(
		'name'=>'print_silently',
		'id'=>'print_silently',
		'value'=>'print_silently',
		'checked'=>$this->config->item('print_silently')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_print_header').':', 'print_header',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_checkbox(array(
		'name'=>'print_header',
		'id'=>'print_header',
		'value'=>'print_header',
		'checked'=>$this->config->item('print_header')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_print_footer').':', 'print_footer',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_checkbox(array(
		'name'=>'print_footer',
		'id'=>'print_footer',
		'value'=>'print_footer',
		'checked'=>$this->config->item('print_footer')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_receipt_printer').':', 'config_receipt_printer',array('class'=>'wide')); ?>
	<div class='form_field'>
		<?php echo form_dropdown(
			'receipt_printer',
			array(),
			$this->config->item('receipt_printer'),'class="addon_installed" id="receipt_printer"');?>
	</div>
</div>

<div class="field_row clearfix">    
 	<?php echo form_label($this->lang->line('config_print_top_margin').':', 'print_top_margin',array('class'=>'wide required')); ?>
    <div class='form_field'>
        <?php echo form_input(array(
        	'type'=>'number',
        	'min'=>'0',
        	'max'=>'20',
            'name'=>'print_top_margin',
            'id'=>'print_top_margin',
            'value'=>$this->config->item('print_top_margin')));?>
            px
    </div>
</div>

<div class="field_row clearfix">    
 	<?php echo form_label($this->lang->line('config_print_left_margin').':', 'print_left_margin',array('class'=>'wide required')); ?>
    <div class='form_field'>
        <?php echo form_input(array(
        	'type'=>'number',
        	'min'=>'0',
        	'max'=>'20',
            'name'=>'print_left_margin',
            'id'=>'print_left_margin',
            'value'=>$this->config->item('print_left_margin')));?>
            px
    </div>
</div>

<div class="field_row clearfix">    
 	<?php echo form_label($this->lang->line('config_print_bottom_margin').':', 'print_bottom_margin',array('class'=>'wide required')); ?>
    <div class='form_field'>
        <?php echo form_input(array(
        	'type'=>'number',
        	'min'=>'0',
        	'max'=>'20',
            'name'=>'print_bottom_margin',
            'id'=>'print_bottom_margin',
            'value'=>$this->config->item('print_bottom_margin')));?>
            px
    </div>
</div>

<div class="field_row clearfix">    
 	<?php echo form_label($this->lang->line('config_print_right_margin').':', 'print_right_margin',array('class'=>'wide required')); ?>
    <div class='form_field'>
        <?php echo form_input(array(
        	'type'=>'number',
        	'min'=>'0',
        	'max'=>'20',
            'name'=>'print_right_margin',
            'id'=>'print_right_margin',
            'value'=>$this->config->item('print_right_margin')));?>
            px
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
	var printers = window.jsPrintSetup ? jsPrintSetup.getPrintersList().split(',') : [];
	$.each(printers, function(key, value) 
	{   
	     $('#receipt_printer').append($('<option>', { value : value }).text(value)); 
	});

	$("input[id*='margin'], #print_footer, #print_header, #receipt_printer, #print_silently").prop('disabled', !window.jsPrintSetup);
	$('#receipt_printer option[value="<?php echo $this->config->item('receipt_printer'); ?>"]').attr('selected', 'selected');

	var dialog_confirmed = window.jsPrintSetup;
	$.validator.addMethod("addon_installed", function(value, element) 
	{
		dialog_confirmed = dialog_confirmed || confirm('<?php echo $this->lang->line('config_jsprintsetup_required'); ?>'); 
 		return dialog_confirmed; 
	}, '<?php echo $this->lang->line("config_jsprintsetup_required"); ?>');
			
	$('#receipt_config_form').validate({
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
		errorLabelContainer: "#receipt_error_message_box",
 		wrapper: "li",
		rules: 
		{
			print_after_sale: 
			{
				addon_installed: true
			},
			print_top_margin:
    		{
    			required:true,
    			number:true
    		},
    		print_left_margin:
    		{
    			required:true,
    			number:true
    		},
    		print_bottom_margin:
    		{
    			required:true,
    			number:true
    		},
    		print_right_margin:
    		{
    			required:true,
    			number:true
    		}    		
   		},
		messages: 
		{
			print_top_margin:
			{
	            required:"<?php echo $this->lang->line('config_print_top_margin_required'); ?>",
	            number:"<?php echo $this->lang->line('config_print_top_margin_number'); ?>",
			},
			print_left_margin:
			{
	            required:"<?php echo $this->lang->line('config_print_left_margin_required'); ?>",
	            number:"<?php echo $this->lang->line('config_print_left_margin_number'); ?>",
			},
			print_bottom_margin:
			{
	            required:"<?php echo $this->lang->line('config_print_bottom_margin_required'); ?>",
	            number:"<?php echo $this->lang->line('config_print_bottom_margin_number'); ?>",
			},
			print_right_margin:
			{
	            required:"<?php echo $this->lang->line('config_print_right_margin_required'); ?>",
	            number:"<?php echo $this->lang->line('config_print_right_margin_number'); ?>",
			}
		}
	});
});
</script>