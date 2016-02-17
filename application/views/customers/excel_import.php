<?php echo form_open_multipart('customers/do_excel_import/',array('id'=>'item_form', 'class'=>'form-horizontal')); ?>
	<div id="required_fields_message"><?php echo $this->lang->line('customers_import_items_excel'); ?></div>
	<ul id="error_message_box" class="error_message_box"></ul>
	<b><a href="<?php echo site_url('customers/excel'); ?>"><?php echo $this->lang->line('common_download_import_template'); ?></a></b>
	<fieldset id="item_basic_info">
		<legend><?php echo $this->lang->line('common_import'); ?></legend>

		<div class="form-group">
		<?php echo form_label($this->lang->line('common_import_file_path').':', 'name',array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
			<?php echo form_upload(array(
				'name'=>'file_path',
				'id'=>'file_path',
				'class'=>'form-control',
				'value'=>'')
			);?>
			</div>
		</div>

		?>
	</fieldset>
<?php echo form_close(); ?>
<script type='text/javascript'>

//validation and submit handling
$(document).ready(function()
{	
	$('#item_form').validate({
		submitHandler:function(form)
		{
			$(form).ajaxSubmit({
			success:function(response)
			{
				dialog_support.hide();;
				post_person_form_submit(response);
			},
			dataType:'json'
		});

		},
		errorLabelContainer: "#error_message_box",
 		wrapper: "li",
		rules: 
		{
			file_path:"required"
   		},
		messages: 
		{
   			file_path:"<?php echo $this->lang->line('common_import_full_path'); ?>"
		}
	});
});
</script>
