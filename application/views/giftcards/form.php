<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
<ul id="error_message_box"></ul>
<?php
echo form_open('giftcards/save/'.$giftcard_info->giftcard_id,array('id'=>'giftcard_form'));
?>
<fieldset id="giftcard_basic_info" style="padding: 5px;">
<legend><?php echo $this->lang->line("giftcards_basic_information"); ?></legend>

<!-- GARRISON ADDED 4/22/2013 -->
<div class="field_row clearfix">
<?php echo form_label($this->lang->line('giftcards_person_id').':', 'name',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'person_id',
		'id'=>'person_id',
		'value'=>$selected_person)
	);?>
	</div>
</div>
<!-- END GARRISON ADDED -->

<div class="field_row clearfix">
<?php echo form_label($this->lang->line('giftcards_giftcard_number').':', 'name',array('class'=>'required wide')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'giftcard_number',
		'id'=>'giftcard_number',
		'value'=>$giftcard_number)
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
	var format_item = function(row) 
	{
    	var result = [row[0], "|", row[1]].join("");
    	// if more than one occurence
    	if (row[2] > 1 && row[3] && row[3].toString().trim()) {
			// display zip code
    		result += ' - ' + row[3];
    	}
		return result;
	};
	var autocompleter = $("#person_id").autocomplete('<?php echo site_url("giftcards/person_search"); ?>', 
	{
    	minChars:0,
    	delay:15, 
    	max:100,
       	cacheLength: 1,
        formatItem: format_item,
        formatResult : format_item
    });

	// declare submitHandler as an object.. will be reused
	var submit_form = function(selected_person) 
	{ 
		$(this).ajaxSubmit(
		{
			success:function(response)
			{
				tb_remove();
				post_giftcard_form_submit(response);
			},
			error: function(jqXHR, textStatus, errorThrown) 
			{
				selected_customer && autocompleter.val(selected_person);
				post_giftcard_form_submit({message: errorThrown});
			},
			dataType:'json'
		});
	};
	
	$('#giftcard_form').validate({
		submitHandler:function(form)
		{
			var selected_person = autocompleter.val();
			var selected_person_id = selected_person.replace(/(\w)\|.*/, "$1");
			selected_person_id && autocompleter.val(selected_person_id);
			submit_form.call(form, selected_person);
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