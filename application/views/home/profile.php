<div class="modal fade" id="profile-modal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<ul class="nav nav-pills nav-justified w-100 me-1 gap-1">
					<li class="nav-item">
						<button type="button" id="modal-button-profile" onclick="modalSwitchProfile()" class="nav-link active">Profile</button>
					</li>
					<li class="nav-item" title="<?php echo $this->lang->line('employees_change_password'); ?>">
						<button type="button" id="modal-button-password" onclick="modalSwitchPassword()" class="nav-link"><?php echo $this->lang->line('employees_change_password'); ?></a>
					</li>
				</ul>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body text-start">

				<div id="modal-profile">
					<h5><?php echo $user_info->first_name . '&nbsp;' . $user_info->last_name; ?></h5>
					<span><?php echo $user_info->person_id; ?></span>
				</div>

				<div id="modal-password" class="d-none">
					<h6 class="text-end mb-3"><span class="badge bg-danger"><?php echo $this->lang->line('common_fields_required_message'); ?></span></h6>
					<div class="form-floating mb-3">
						<input name="username" type="text" class="form-control" placeholder="<?php echo $this->lang->line('login_username'); ?>" value="<?php echo $user_info->username; ?>" disabled>
						<label for="input-username"><?php echo $this->lang->line('login_username'); ?></label>
					</div>
					<div class="form-floating mb-3">
						<input name="password-current" type="password" id="input-password-current" class="form-control" placeholder="Current Password">
						<label for="input-password-current">Current Password</label>
					</div>
					<div class="form-floating mb-3">
						<input name="password-new" type="password" id="input-password-new" class="form-control" placeholder="New Password">
						<label for="input-password-new">New Password</label>
						<span class="form-text">Must be 8-20 characters long</span>
					</div>
					<div class="form-floating mb-3">
						<input name="password-repeat" type="password" id="input-password-repeat" class="form-control" placeholder="Repeat Password">
						<label for="input-password-repeat">Repeat Password</label>
					</div>
				</div>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<a type="button" id="modal-button-logout" class="btn btn-danger" href="home/logout"><?php echo $this->lang->line('login_logout'); ?></a>
				<button type="button" id="modal-button-save" class="btn btn-primary d-none">Save</button>
			</div>
		</div>
	</div>
</div>
