<?php echo form_open_multipart('items/do_excel_import/',array('id'=>'item_form', 'class'=>'form_horizontal')); ?>
	<div id="required_fields_message"><?php echo $this->lang->line('items_import_items_excel'); ?></div>
	<ul id="error_message_box" class="error_message_box"></ul>

	<div class="form-group">
		<div class="col-xs-9">
			<a href="<?php echo site_url('items/excel'); ?>"><?php echo $this->lang->line('common_download_import_template'); ?></a>
		</div>
	</div>

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
				tb_remove();
				post_item_form_submit(response);
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
