<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
<ul id="error_message_box"></ul>
<?php
echo form_open('giftcards/save/'.$giftcard_info->giftcard_id,array('id'=>'giftcard_form'));
?>
<fieldset id="giftcard_basic_info" style="padding: 5px;">
<legend><?php echo $this->lang->line("giftcards_basic_information"); ?></legend>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('giftcards_giftcard_number').':', 'name',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'giftcard_number',
		'id'=>'giftcard_number',
		'value'=>$giftcard_info->giftcard_number)
	);?>
	</div>
</div>

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('giftcards_card_value').':', 'name',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'value',
		'id'=>'value',
		'value'=>$giftcard_info->value)
	);?>
	</div>
</div>

<?php
echo form_submit(array(
	'name'=>'submit',
	'id'=>'submit',
	'value'=>$this->lang->line('common_submit'),
	'class'=>'submit_button float_right')
);
?>
</fieldset>
<?php
echo form_close();
?>
<script type='text/javascript'>

//validation and submit handling
$(document).ready(function()
{
	$('#giftcard_form').validate({
		submitHandler:function(form)
		{
			$(form).ajaxSubmit({
			success:function(response)
			{
				tb_remove();
				post_giftcard_form_submit(response);
			},
			dataType:'json'
		});

		},
		errorLabelContainer: "#error_message_box",
 		wrapper: "li",
		rules:
		{
			giftcard_number:
			{
				required:true,
				number:true
			},
			value:
			{
				required:true,
				number:true
			}
   		},
		messages:
		{
			giftcard_number:
			{
				required:"<?php echo $this->lang->line('giftcards_number_required'); ?>",
				number:"<?php echo $this->lang->line('giftcards_number'); ?>"
			},
			value:
			{
				required:"<?php echo $this->lang->line('giftcards_value_required'); ?>",
				number:"<?php echo $this->lang->line('giftcards_value'); ?>"
			}
		}
	});
});
</script>