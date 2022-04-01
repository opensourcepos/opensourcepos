<?php
/**
 * @var string $controller_name
 * @var object $person_info
 * @var array $all_modules
 * @var array $all_subpermissions
 * @var int $employee_id
 */
?>
<div id="required_fields_message"><?php echo lang('Common.fields_required_message') ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open("$controller_name/save/$person_info->person_id", ['id' => 'employee_form', 'class' => 'form-horizontal']) ?>
	<ul class="nav nav-tabs nav-justified" data-tabs="tabs">
		<li class="active" role="presentation">
			<a data-toggle="tab" href="#employee_basic_info"><?php echo lang('Employees.basic_information') ?></a>
		</li>
		<li role="presentation">
			<a data-toggle="tab" href="#employee_login_info"><?php echo lang('Employees.login_info') ?></a>
		</li>
		<li role="presentation">
			<a data-toggle="tab" href="#employee_permission_info"><?php echo lang('Employees.permission_info') ?></a>
		</li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane fade in active" id="employee_basic_info">
			<fieldset>
				<?php echo view('people/form_basic_info') ?>
			</fieldset>
		</div>

		<div class="tab-pane" id="employee_login_info">
			<fieldset>
				<div class="form-group form-group-sm">	
					<?php echo form_label(lang('Employees.username'), 'username', ['class' => 'required control-label col-xs-3']) ?>
					<div class='col-xs-8'>
						<div class="input-group">
							<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-user"></span></span>
							<?php echo form_input ([
								'name' => 'username',
								'id' => 'username',
								'class' => 'form-control input-sm',
								'value' => esc($person_info->username, 'attr')
							]) ?>
						</div>
					</div>
				</div>

				<?php $password_label_attributes = $person_info->person_id == "" ? ['class' => 'required'] : []; ?>

				<div class="form-group form-group-sm">	
					<?php echo form_label(lang('Employees.password'), 'password', esc(array_merge($password_label_attributes, ['class' => 'control-label col-xs-3']), 'attr'))?>
					<div class='col-xs-8'>
						<div class="input-group">
							<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-lock"></span></span>
							<?php echo form_password ([
									'name' => 'password',
									'id' => 'password',
									'class' => 'form-control input-sm'
								]) ?>
						</div>
					</div>
				</div>

				<div class="form-group form-group-sm">	
				<?php echo form_label(lang('Employees.repeat_password'), 'repeat_password', esc(array_merge($password_label_attributes, ['class' => 'control-label col-xs-3']), 'attr')) ?>
					<div class='col-xs-8'>
						<div class="input-group">
							<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-lock"></span></span>
							<?php echo form_password ([
									'name' => 'repeat_password',
									'id' => 'repeat_password',
									'class' => 'form-control input-sm'
								]) ?>
						</div>
					</div>
				</div>

				<div class="form-group form-group-sm">
					<?php echo form_label(lang('Employees.language'), 'language', ['class' => 'control-label col-xs-3']) ?>
					<div class='col-xs-8'>
						<div class="input-group">
							<?php 
								$languages = get_languages();
								$languages[':'] = lang('Employees.system_language');
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
									esc($languages, 'attr'),
									esc("$language_code:$language", 'attr'),
									['class' => 'form-control input-sm']
									);
							?>
						</div>
					</div>
				</div>
			</fieldset>
		</div>

		<div class="tab-pane" id="employee_permission_info">
			<fieldset>
				<p><?php echo lang('Employees.permission_desc') ?></p>

				<ul id="permission_list">
					<?php
					foreach($all_modules as $module)
					{
					?>
						<li>	
							<?php echo form_checkbox("grant_$module->module_id", $module->module_id, $module->grant, "class=\'module\'") ?>
							<?php echo form_dropdown(
								"menu_group_$module->module_id", [
									'home' => lang('Module.home'),
									'office' => lang('Module.office'),
									'both' => lang('Module.both')
								],
								$module->menu_group,
								"class=\'module\'"
							) ?>

							<span class="medium"><?php echo lang("Module.$module->module_id") ?>:</span>
							<span class="small"><?php echo lang("Module.$module->module_id" . '_desc') ?></span>
							<?php
								foreach($all_subpermissions as $permission)
								{
									$exploded_permission = explode('_', $permission->permission_id, 2);
									if($permission->module_id == $module->module_id)
									{
										$lang_key = $module->module_id . '_' . $exploded_permission[1];
										$lang_line = lang($lang_key);
										$lang_line = ($this->lang->line_tbd($lang_key) == $lang_line) ? ucwords(str_replace("_", " ",$exploded_permission[1])) : $lang_line;
										if(!empty($lang_line))
										{
							?>
											<ul>
												<li>
													<?php echo form_checkbox("grant_$permission->permission_id", $permission->permission_id, $permission->grant) ?>
													<?php echo form_hidden("menu_group_$permission->permission_id", "--") ?>
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
<?php echo form_close() ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	$.validator.setDefaults({ ignore: [] });

	$.validator.addMethod('module', function (value, element) {
		var result = $('#permission_list input').is(':checked');
		$('.module').each(function(index, element)
		{
			var parent = $(element).parent();
			var checked =  $(element).is(':checked');
			if($('ul', parent).length > 0 && result)
			{
				result &= !checked || (checked && $('ul > li > input:checked', parent).length > 0);
			}
		});
		return result;
	}, "<?php echo lang('Employees.subpermission_required') ?>");

	$('ul#permission_list > li > input.module').each(function()
	{
		var $this = $(this);
		$('ul > li > input,select', $this.parent()).each(function()
		{
			var $that = $(this);
			var updateInputs = function (checked)
			{
				$that.prop('disabled', !checked);
				!checked && $that.prop('checked', false);
			}
			$this.change(function() {
				updateInputs($this.is(':checked'));
			});
			updateInputs($this.is(':checked'));
		});
	});
	
	$('#employee_form').validate($.extend({
		submitHandler: function(form) {
			$(form).ajaxSubmit({
				success: function(response)
				{
					dialog_support.hide();
					table_support.handle_submit("<?php echo esc(site_url($controller_name), 'url') ?>", response);
				},
				dataType: 'json'
			});
		},

		errorLabelContainer: '#error_message_box',

		rules:
		{
			first_name: 'required',
			last_name: 'required',
			username:
			{

				required: true,
				minlength: 5,
				remote: '<?php echo esc(site_url("$controller_name/check_username/$employee_id"), 'url') ?>'
			},
			password:
			{
				<?php
				if($person_info->person_id == '')
				{
				?>
					required: true,
				<?php
				}
				?>
				minlength: 8
			},	
			repeat_password:
			{
				equalTo: '#password'
			},
			email: 'email'
		},

		messages: 
		{
			first_name: "<?php echo lang('Common.first_name_required') ?>",
			last_name: "<?php echo lang('Common.last_name_required') ?>",
			username:
			{
				required: "<?php echo lang('Employees.username_required') ?>",
				minlength: "<?php echo lang('Employees.username_minlength') ?>",
				remote: "<?php echo lang('Employees.username_duplicate') ?>"
            },
			password:
			{
				<?php
				if($person_info->person_id == "")
				{
				?>
				required: "<?php echo lang('Employees.password_required') ?>",
				<?php
				}
				?>
				minlength: "<?php echo lang('Employees.password_minlength') ?>"
			},
			repeat_password:
			{
				equalTo: "<?php echo lang('Employees.password_must_match') ?>"
			},
			email: "<?php echo lang('Common.email_invalid_format') ?>"
		}
	}, form_support.error));
});
</script>
