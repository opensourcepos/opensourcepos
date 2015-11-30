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
<?php echo form_label($this->lang->line('config_use_invoice_template').':', 'use_invoice_template',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_checkbox(array(
		'name'=>'use_invoice_template',
		'value'=>'use_invoice_template',
		'id'=>'use_invoice_template',
		'checked'=>$this->config->item('use_invoice_template')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_invoice_default_comments').':', 'invoice_default_comments',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_textarea(array(
		'name'=>'invoice_default_comments',
		'id'=>'invoice_default_comments',
		'rows'=>4,
		'cols'=>25,
		'value'=>$this->config->item('invoice_default_comments')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_invoice_email_message').':', 'invoice_email_message',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_textarea(array(
		'name'=>'invoice_email_message',
		'id'=>'invoice_email_message',
		'rows'=>4,
		'cols'=>25,
		'value'=>$this->config->item('invoice_email_message')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_receipt_show_taxes').':', 'receipt_show_taxes',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_checkbox(array(
		'name'=>'receipt_show_taxes',
		'value'=>'receipt_show_taxes',
		'id'=>'receipt_show_taxes',
		'checked'=>$this->config->item('receipt_show_taxes')));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_show_total_discount').':', 'show_total_discount',array('class'=>'wide')); ?>
	<div class='form_field'>
	<?php echo form_checkbox(array(
		'name'=>'show_total_discount',
		'value'=>'show_total_discount',
		'id'=>'show_total_discount',
		'checked'=>$this->config->item('show_total_discount')));?>
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
			'','id="receipt_printer"');?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('config_invoice_printer').':', 'config_invoice_printer',array('class'=>'wide')); ?>
	<div class='form_field'>
		<?php echo form_dropdown('invoice_printer', array(), ' ','id="invoice_printer"');?>
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
	var enable_disable_use_invoice_template = (function() 
	{
		var use_invoice_template = $("#use_invoice_template").is(":checked");
		$("#invoice_default_comments, #invoice_email_message").prop('disabled', !use_invoice_template);
		return arguments.callee;
	})();
	$("#use_invoice_template").change(enable_disable_use_invoice_template);

	if (window.localStorage && window.jsPrintSetup) 
	{
		var printers = (jsPrintSetup.getPrintersList() && jsPrintSetup.getPrintersList().split(',')) || [];
		$('#receipt_printer, #invoice_printer').each(function() 
		{
			var $this = $(this)
			$(printers).each(function(key, value) 
			{   
			     $this.append($('<option>', { value : value }).text(value));
  	 		});
			$("option[value='" + localStorage[$(this).attr('id')] + "']", this).prop('selected', true);
			$(this).change(function()
			{
				localStorage[$(this).attr('id')] = $(this).val();		
			});
		});
	}
	else
	{
		$("input[id*='margin'], #print_footer, #print_header, #receipt_printer, #invoice_printer, #print_silently").prop('disabled', true);
		$("#receipt_printer, #invoice_printer").each(function() 
		{
			$(this).append($('<option>', {value : 'na'}).text('N/A'));
		});
	}

	var dialog_confirmed = window.jsPrintSetup;
			
	$('#receipt_config_form').validate({
		submitHandler:function(form)
		{
			$(form).ajaxSubmit({
			beforeSerialize: function(arr, $form, options) {
				dialog_confirmed = dialog_confirmed || confirm('<?php echo $this->lang->line('config_jsprintsetup_required'); ?>');
				$("input:disabled, textarea:disabled").prop("disabled", false); 
				return dialog_confirmed;
			},
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
				// set back disabled state
				enable_disable_use_invoice_template();
			},
			dataType:'json'
		});

		},
		errorLabelContainer: "#receipt_error_message_box",
 		wrapper: "li",
		rules: 
		{
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
