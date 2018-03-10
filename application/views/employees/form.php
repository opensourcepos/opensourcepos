<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open($controller_name . '/save/' . $person_info->person_id, array('id'=>'employee_form', 'class'=>'form-horizontal')); ?>
	<ul class="nav nav-tabs nav-justified" data-tabs="tabs">
		<li class="active" role="presentation">
			<a data-toggle="tab" href="#employee_basic_info"><?php echo $this->lang->line("employees_basic_information"); ?></a>
		</li>
		<li role="presentation">
			<a data-toggle="tab" href="#employee_login_info"><?php echo $this->lang->line("employees_login_info"); ?></a>
		</li>
		<li role="presentation">
			<a data-toggle="tab" href="#employee_permission_info"><?php echo $this->lang->line("employees_permission_info"); ?></a>
		</li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane fade in active" id="employee_basic_info">
			<fieldset>
				<?php $this->load->view("people/form_basic_info"); ?>
			</fieldset>
		</div>

		<div class="tab-pane" id="employee_login_info">
			<fieldset>
				<div class="form-group form-group-sm">	
					<?php echo form_label($this->lang->line('employees_username'), 'username', array('class'=>'required control-label col-xs-3')); ?>
					<div class='col-xs-8'>
						<div class="input-group">
							<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-user"></span></span>
							<?php echo form_input(array(
									'name'=>'username',
									'id'=>'username',
									'class'=>'form-control input-sm',
									'value'=>$person_info->username)
									);?>
						</div>
					</div>
				</div>

				<?php $password_label_attributes = $person_info->person_id == "" ? array('class'=>'required') : array(); ?>

				<div class="form-group form-group-sm">	
					<?php echo form_label($this->lang->line('employees_password'), 'password', array_merge($password_label_attributes, array('class'=>'control-label col-xs-3'))); ?>
					<div class='col-xs-8'>
						<div class="input-group">
							<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-lock"></span></span>
							<?php echo form_password(array(
									'name'=>'password',
									'id'=>'password',
									'class'=>'form-control input-sm')
									);?>
						</div>
					</div>
				</div>

				<div class="form-group form-group-sm">	
				<?php echo form_label($this->lang->line('employees_repeat_password'), 'repeat_password', array_merge($password_label_attributes, array('class'=>'control-label col-xs-3'))); ?>
					<div class='col-xs-8'>
						<div class="input-group">
							<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-lock"></span></span>
							<?php echo form_password(array(
									'name'=>'repeat_password',
									'id'=>'repeat_password',
									'class'=>'form-control input-sm')
									);?>
						</div>
					</div>
				</div>

				<div class="form-group form-group-sm">
					<?php echo form_label($this->lang->line('employees_language'), 'language', array('class' => 'control-label col-xs-3')); ?>
					<div class='col-xs-8'>
						<div class="input-group">
							<?php 
								$languages = get_languages();
								$languages[':'] = $this->lang->line('employees_system_language');
								$language_code = current_language_code();
								$language = current_language();
								
								// If No language is set then it will display "System Language"
								if($language_code === current_language_code(TRUE))
								{
									$language_code = '';
									$language = '';
								}
								
								echo form_dropdown(
									'language',
									$languages,
									$language_code . ':' . $language,
									array('class' => 'form-control input-sm')
									);
							?>
						</div>
					</div>
				</div>
			</fieldset>
		</div>

		<div class="tab-pane" id="employee_permission_info">
			<fieldset>
				<p><?php echo $this->lang->line("employees_permission_desc"); ?></p>

				<ul id="permission_list">
					<?php
					foreach($all_modules as $module)
					{
					?>
						<li>	
							<?php echo form_checkbox("grant_".$module->module_id, $module->module_id, $module->grant, "class='module'"); ?>
							<?php echo form_dropdown("menu_group_".$module->module_id, array(
								'home' => $this->lang->line('module_home'),
								'office' => $this->lang->line('module_office'),
								'both' => $this->lang->line('module_both')
							), $module->menu_group, "class='module'"); ?>
							<span class="medium"><?php echo $this->lang->line('module_'.$module->module_id);?>:</span>
							<span class="small"><?php echo $this->lang->line('module_'.$module->module_id.'_desc');?></span>
							<?php
								foreach($all_subpermissions as $permission)
								{
									$exploded_permission = explode('_', $permission->permission_id);
									if($permission->module_id == $module->module_id)
									{
										$lang_key = $module->module_id.'_'.$exploded_permission[1];
										$lang_line = $this->lang->line($lang_key);
										$lang_line = ($this->lang->line_tbd($lang_key) == $lang_line) ? $exploded_permission[1] : $lang_line;
										if(!empty($lang_line))
										{
							?>
											<ul>
												<li>
													<?php echo form_checkbox("grant_".$permission->permission_id, $permission->permission_id, $permission->grant); ?>
													<?php echo form_hidden("menu_group_".$permission->permission_id, "--"); ?>
													<span class="medium"><?php echo $lang_line ?></span>
												</li>
											</ul>
							<?php
										}
									}
								}
							?>
						</li>
					<?php
					}
					?>
				</ul>
			</fieldset>
		</div>
	</div>
<?php echo form_close(); ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	$.validator.setDefaults({ ignore: [] });

	$.validator.addMethod("module", function (value, element) {
		var result = $("#permission_list input").is(":checked");
		$(".module").each(function(index, element)
		{
			var parent = $(element).parent();
			var checked =  $(element).is(":checked");
			if ($("ul", parent).length > 0 && result)
			{
				result &= !checked || (checked && $("ul > li > input:checked", parent).length > 0);
			}
		});
		return result;
	}, '<?php echo $this->lang->line('employees_subpermission_required'); ?>');

	$("ul#permission_list > li > input.module").each(function()
	{
		var $this = $(this);
		$("ul > li > input,select", $this.parent()).each(function()
		{
			var $that = $(this);
			var updateInputs = function (checked)
			{
				$that.prop("disabled", !checked);
				!checked && $that.prop("checked", false);
			}
			$this.change(function() {
				updateInputs($this.is(":checked"));
			});
			updateInputs($this.is(":checked"));
		});
	});
	
	$('#employee_form').validate($.extend({
		submitHandler:function(form) 
		{
			$(form).ajaxSubmit({
				success:function(response)
				{
					dialog_support.hide();
					table_support.handle_submit('<?php echo site_url('employees'); ?>', response);
				},
				dataType:'json'
			});
		},
		rules:
		{
			first_name: "required",
			last_name: "required",
			username:
			{
				required:true,
				minlength: 5
			},
			
			password:
			{
				<?php
				if($person_info->person_id == "")
				{
				?>
				required:true,
				<?php
				}
				?>
				minlength: 8
			},	
			repeat_password:
			{
				equalTo: "#password"
			},
			email: "email"
		},
		messages: 
		{
			first_name: "<?php echo $this->lang->line('common_first_name_required'); ?>",
			last_name: "<?php echo $this->lang->line('common_last_name_required'); ?>",
			username:
			{
				required: "<?php echo $this->lang->line('employees_username_required'); ?>",
				minlength: "<?php echo $this->lang->line('employees_username_minlength'); ?>"
			},

			password:
			{
				<?php
				if($person_info->person_id == "")
				{
				?>
				required:"<?php echo $this->lang->line('employees_password_required'); ?>",
				<?php
				}
				?>
				minlength: "<?php echo $this->lang->line('employees_password_minlength'); ?>"
			},
			repeat_password:
			{
				equalTo: "<?php echo $this->lang->line('employees_password_must_match'); ?>"
			},
			email: "<?php echo $this->lang->line('common_email_invalid_format'); ?>"
		}
	}, form_support.error));
});
</script>
