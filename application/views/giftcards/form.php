<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open('giftcards/save/'.$giftcard_info->giftcard_id, array('id'=>'giftcard_form', 'class'=>'form-horizontal')); ?>
	<fieldset id="giftcard_basic_info" style="padding: 5px;">
		<legend><?php echo $this->lang->line("giftcards_basic_information"); ?></legend>

		<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('giftcards_person_id').':', 'name', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-6'>
			<?php echo form_input(array(
				'name'=>'person_id',
				'id'=>'person_id',
				'class'=>'form-control input-sm',
				'value'=>$selected_person)
			);?>
			</div>
		</div>

		<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('giftcards_giftcard_number').':', 'name', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-6'>
			<?php echo form_input(array(
				'name'=>'giftcard_number',
				'id'=>'giftcard_number',
				'class'=>'form-control input-sm',
				'value'=>$giftcard_number)
			);?>
			</div>
		</div>

		<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('giftcards_card_value').':', 'name', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-6'>
			<?php echo form_input(array(
				'name'=>'value',
				'id'=>'value',
				'class'=>'form-control input-sm',
				'value'=>$giftcard_info->value)
			);?>
			</div>
		</div>
	</fieldset>
<?php echo form_close(); ?>

<script type='text/javascript'>
//validation and submit handling
$(document).ready(function()
{
	var format_item = function(row) 
	{
    	return [row[0], "|", row[1]].join("");
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
				dialog_support.hide();
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
			var selected_person_id = selected_person && selected_person.replace(/(\w)\|.*/, "$1");
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