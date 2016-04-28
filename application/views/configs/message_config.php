<?php echo form_open('config/save_message/', array('id'=>'message_config_form', 'enctype'=>'multipart/form-data', 'class'=>'form-horizontal')); ?>
	<div id="config_wrapper">
		<fieldset id="message_config">
			
   <h5 style="text-align:center; color: red;">Note : If you wish to use SMS template, save your message here. Otherwise keep the 'Saved Text Message' box blank.</h5>

				</br>
				</br>

			
			
			<div class="form-group form-group-sm">	
				<?php echo form_label($this->lang->line('config_msg_msg'), 'msg_msg', array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-4'>
					<?php echo form_textarea(array(
						'name'=>'msg_msg',
						'id'=>'msg_msg',
						'class'=>'form-control input-sm',
						'value'=>$this->config->item('msg_msg')));?>
				</div>
			</div>


				<div class="form-group form-group-sm">	
				<?php echo form_label($this->lang->line('config_msg_uid'), 'msg_uid', array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name'=>'msg_uid',
						'id'=>'msg_uid',
						'class'=>'form-control input-sm',
						'type'=>'text',
						'value'=>$this->config->item('msg_uid')));?>
				</div>
			</div>

			<div class="form-group form-group-sm" >	
				<?php echo form_label($this->lang->line('config_msg_pwd'), 'msg_pwd', array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name'=>'msg_pwd',
						'id'=>'msg_pwd',
						'class'=>'form-control input-sm',
						'type'=>'password',
						'value'=>$this->config->item('msg_pwd')));?>
				</div>
			</div>
				
			<div class="form-group form-group-sm" >	
				<?php echo form_label($this->lang->line('config_msg_src'), 'msg_src', array('class'=>'control-label col-xs-2')); ?>
				<div class='col-xs-2'>
					<?php echo form_input(array(
						'name'=>'msg_src',
						'id'=>'msg_src',
						'class'=>'form-control input-sm',
						'type'=>'text',
						'value'=>$this->config->item('msg_src')));?>
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
	

	$('#message_config_form').validate({
		submitHandler: function(form) {
			$(form).ajaxSubmit({
				success: function(response)	{
					if(response.success)
					{
						set_feedback(response.message, 'alert alert-dismissible alert-success', false);		
					}
					else
					{
						set_feedback(response.message, 'alert alert-dismissible alert-danger', true);		
					}
				},
				dataType: 'json'
			});
		}
		
		
	});
});
</script>
