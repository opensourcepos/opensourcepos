<?php echo form_open('config/save_message/', array('id' => 'message_config_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal')); ?>

<?php
$title_message['config_title'] = $this->lang->line('config_message_configuration');
$this->load->view('configs/config_header', $title_message);
?>

<ul id="message_error_message_box" class="error_message_box"></ul>

<div class="row">
	<div class="col-12 col-lg-6 mb-3">
		<label for="msg-uid" class="form-label"><?php echo $this->lang->line('config_msg_uid'); ?></label>
		<div class="input-group">
			<span class="input-group-text" id="msg-uid-icon"><i class="bi bi-person"></i></span>
			<input type="text" class="form-control" id="msg-uid" aria-describedby="msg-uid-icon" required value="<?php echo $this->config->item('msg_uid'); ?>">
		</div>
	</div>

	<div class="col-12 col-lg-6 mb-3">
		<label for="msg-pwd" class="form-label"><?php echo $this->lang->line('config_msg_pwd'); ?></label>
		<div class="input-group">
			<span class="input-group-text" id="msg-pwd-icon"><i class="bi bi-lock"></i></span>
			<input type="password" class="form-control" id="msg-pwd" aria-describedby="msg-pwd-icon" required value="<?php echo $this->config->item('msg_pwd'); ?>">
		</div>
	</div>

	<div class="col-12 col-lg-6 mb-3">
		<label for="msg-src" class="form-label"><?php echo $this->lang->line('config_msg_src'); ?></label>
		<div class="input-group">
			<span class="input-group-text" id="msg-src-icon"><i class="bi bi-megaphone"></i></span>
			<input type="text" class="form-control" id="msg-src" aria-describedby="msg-src-icon" required value="<?php echo $this->config->item('msg_src'); ?>">
		</div>
	</div>
</div>

<label for="msg-msg" class="form-label"><?php echo $this->lang->line('config_msg_msg'); ?></label>
<div class="input-group mb-3">
	<span class="input-group-text"><i class="bi bi-chat-quote"></i></span>
	<textarea class="form-control" name="msg_msg" id="msg-msg" rows="10" placeholder="<?php echo $this->lang->line('config_msg_msg_placeholder'); ?>" value="<?php echo $this->lang->line('msg_msg'); ?>"></textarea>
</div>

<div class="d-flex justify-content-end">
	<button class="btn btn-primary" name="submit_message"><?= $this->lang->line('common_submit'); ?></button>
</div>

<?php echo form_close(); ?>

<script type="text/javascript">
	//validation and submit handling
	document.querySelector(document).ready(function() {
		document.querySelector('#message_config_form').validate($.extend(form_support.handler, {

			errorLabelContainer: "#message_error_message_box",

			rules: {
				msg_uid: "required",
				msg_pwd: "required",
				msg_src: "required"
			},

			messages: {
				msg_uid: "<?php echo $this->lang->line('config_msg_uid_required'); ?>",
				msg_pwd: "<?php echo $this->lang->line('config_msg_pwd_required'); ?>",
				msg_src: "<?php echo $this->lang->line('config_msg_src_required'); ?>"
			}
		}));
	});
	/*//validation and submit handling
		$(document).ready(function() {
			$('#message_config_form').validate($.extend(form_support.handler, {

				errorLabelContainer: "#message_error_message_box",

				rules: {
					msg_uid: "required",
					msg_pwd: "required",
					msg_src: "required"
				},

				messages: {
					msg_uid: "<?php echo $this->lang->line('config_msg_uid_required'); ?>",
					msg_pwd: "<?php echo $this->lang->line('config_msg_pwd_required'); ?>",
					msg_src: "<?php echo $this->lang->line('config_msg_src_required'); ?>"
				}
			}));
		});*/
</script>