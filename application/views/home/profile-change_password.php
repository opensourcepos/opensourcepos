<div class="modal fade" id="password-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<ul class="nav nav-pills nav-justified w-100">
					<li class="nav-item">
						<button type="button" class="nav-link" onclick="removeAnimation()" data-bs-toggle="modal" data-bs-target="#profile-modal" data-bs-dismiss="modal">Profile</a>
					</li>
					<li class="nav-item" title="<?= $this->lang->line('employees_change_password'); ?>">
						<button type="button" class="nav-link active" aria-current="page"><?= $this->lang->line('employees_change_password'); ?></button>
					</li>
				</ul>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body text-start">
				<h6><span class="badge bg-danger"><?= $this->lang->line('common_fields_required_message'); ?></span></h6>

				<ul id="error_message_box" class="error_message_box"></ul>

				<?= form_open('home/save/' . $person_info->person_id, array('id' => 'employee_form', 'class' => 'form-horizontal')); ?>
				<div class="tab-content">
					<div class="tab-pane fade in active" id="employee_login_info">
						<fieldset>
							<div class="form-group form-group-sm">
								<?= form_label($this->lang->line('employees_username'), 'username', array('class' => 'required control-label col-xs-3')); ?>
								<div class='col-xs-8'>
									<div class="input-group">
										<span class="input-group-addon input-sm"><i class="bi bi-person"></i></span>
										<?= form_input(
											array(
												'name' => 'username',
												'id' => 'username',
												'class' => 'form-control input-sm',
												'value' => $person_info->username,
												'readonly' => 'true'
											)
										); ?>
									</div>
								</div>
							</div>

							<?php $password_label_attributes = $person_info->person_id == "" ? array('class' => 'required') : array(); ?>

							<div class="form-group form-group-sm">
								<?= form_label($this->lang->line('employees_current_password'), 'current_password', array_merge($password_label_attributes, array('class' => 'control-label col-xs-3'))); ?>
								<div class='col-xs-8'>
									<div class="input-group">
										<span class="input-group-addon input-sm"><i class="bi bi-lock"></i></span>
										<?= form_password(
											array(
												'name' => 'current_password',
												'id' => 'current_password',
												'class' => 'form-control input-sm'
											)
										); ?>
									</div>
								</div>
							</div>

							<div class="form-group form-group-sm">
								<?= form_label($this->lang->line('employees_password'), 'password', array_merge($password_label_attributes, array('class' => 'control-label col-xs-3'))); ?>
								<div class='col-xs-8'>
									<div class="input-group">
										<span class="input-group-addon input-sm"><i class="bi bi-lock"></i></span>
										<?= form_password(
											array(
												'name' => 'password',
												'id' => 'password',
												'class' => 'form-control input-sm'
											)
										); ?>
									</div>
								</div>
							</div>

							<div class="form-group form-group-sm">
								<?= form_label($this->lang->line('employees_repeat_password'), 'repeat_password', array_merge($password_label_attributes, array('class' => 'control-label col-xs-3'))); ?>
								<div class='col-xs-8'>
									<div class="input-group">
										<span class="input-group-addon input-sm"><i class="bi bi-lock"></i></span>
										<?= form_password(
											array(
												'name' => 'repeat_password',
												'id' => 'repeat_password',
												'class' => 'form-control input-sm'
											)
										); ?>
									</div>
								</div>
							</div>
						</fieldset>
					</div>
				</div>
				<?= form_close(); ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" data-bs-dismiss="modal">Save</button>
			</div>
		</div>
	</div>
</div>