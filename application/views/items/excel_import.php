<div id="required_fields_message"><?php echo $this->lang->line('items_import_items_excel'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open_multipart('items/do_excel_import/', array('id'=>'item_form', 'class' => 'form-horizontal')); ?>
	<fieldset id="item_basic_info">
		<div class="form-group form-group-sm">
			<div class="col-xs-6">
				<a href="<?php echo site_url('items/excel'); ?>"><?php echo $this->lang->line('common_download_import_template'); ?></a>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('common_import_file_path'), 'name', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
				<label class="file">
					<input type="file" id="item_image" name="file_path">
					<span class="file-custom"></span>
				</label>
			</div>
		</div>
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
				dialog_support.hide();
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
