<?php
$email = $user_info->email;
$size = 96;
$default = 'https://ui-avatars.com/api/?name=' . $user_info->first_name . '+' . $user_info->last_name . '&format=svg&size=' . $size;
$grav_url = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?d=' . urlencode($default) . '&s=' . $size;
?>

<div class="modal fade" id="profile-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <ul class="nav nav-pills nav-justified w-100 me-1 gap-1">
                    <li class="nav-item">
                        <button type="button" id="modal-button-profile" onclick="modalSwitchProfile()" class="nav-link active">Profile</button>
                    </li>
                    <li class="nav-item" title="<?= lang('Employees.change_password'); ?>">
                        <button type="button" id="modal-button-password" onclick="modalSwitchPassword()" class="nav-link"><?= lang('Employees.change_password'); ?></a>
                    </li>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                <div id="modal-profile">
                    <div class="container">
                        <table>
                            <tbody>
                                <tr>
                                    <td class="pe-2"><img class="img-thumbnail rounded-circle" src="<?= $default; ?>" style="height: 48px;"></td>
                                    <td class="align-middle"><h5><?= $user_info->first_name . '&nbsp;' . $user_info->last_name; ?></h5></td>
                                </tr>
                            </tbody>
                        </table>
                        <br>
                        <table>
                            <tbody>
                                <tr>
                                    <td class="pe-3">Username</td>
                                    <td><?= $user_info->username; ?></td>
                                </tr>
                                <tr>
                                    <td class="pe-3"><?= lang('Common.id'); ?></td>
                                    <td><?= $user_info->person_id; ?></td>
                                </tr>
                                <tr>
                                    <td class="pe-3"><?= lang('Common.email'); ?></td>
                                    <td><?= $user_info->email; ?></td>
                                </tr>
                                <tr>
                                    <td class="pe-3"><?= lang('Common.phone_number'); ?></td>
                                    <td><?= $user_info->phone_number; ?></td>
                                </tr>
                                <tr>
                                    <td class="pe-3"><?= lang('Common.gender'); ?></td>
                                    <td><?= $user_info->gender; ?></td>
                                </tr>
                                <tr>
                                    <td class="pe-3"><?= lang('Common.address_1'); ?></td>
                                    <td><?= $user_info->address_1; ?></td>
                                </tr>
                                <tr>
                                    <td class="pe-3"><?= lang('Common.address_2'); ?></td>
                                    <td><?= $user_info->address_2; ?></td>
                                </tr>
                                <tr>
                                    <td class="pe-3"><?= lang('Common.city'); ?></td>
                                    <td><?= $user_info->city; ?></td>
                                </tr>
                                <tr>
                                    <td class="pe-3"><?= lang('Common.state'); ?></td>
                                    <td><?= $user_info->state; ?></td>
                                </tr>
                                <tr>
                                    <td class="pe-3"><?= lang('Common.zip'); ?></td>
                                    <td><?= $user_info->zip; ?></td>
                                </tr>
                                <tr>
                                    <td class="pe-3"><?= lang('Common.country'); ?></td>
                                    <td><?= $user_info->country; ?></td>
                                </tr>
                                <tr>
                                    <td class="pe-3"><?= lang('Common.comments'); ?></td>
                                    <td><?= $user_info->comments; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="modal-password" class="d-none was-validated" novalidate>

                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <div class="form-floating">
                            <input name="username" id="input-username" type="text" class="form-control" placeholder="<?= lang('Login.username'); ?>" value="<?= $user_info->username; ?>" disabled>
                            <label for="input-username"><?= lang('Login.username'); ?></label>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <div class="form-floating is-invalid">
                            <input name="password-current" id="input-password-current" type="password" class="form-control" placeholder="Current Password" required>
                            <label for="input-password-current">Current Password</label>
                        </div>
                        <div class="invalid-feedback">Please fill in your current password.</div>
                    </div>
                    <div class="form-text">Must be 8-20 characters long</div>
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <div class="form-floating is-invalid">
                            <input name="password-new" id="input-password-new" type="password" class="form-control" placeholder="New Password" required>
                            <label for="input-password-new">New Password</label>
                        </div>
                        <div class="invalid-feedback">Please fill in a new password.</div>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <div class="form-floating is-invalid">
                            <input name="password-repeat" id="input-password-repeat" type="password" class="form-control" placeholder="Repeat Password" required>
                            <label for="input-password-repeat">Repeat Password</label>
                        </div>
                        <div class="invalid-feedback">Please repeat the new password.</div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a type="button" id="modal-button-logout" class="btn btn-danger" href="home/logout">
                    <i class="bi bi-power me-2"></i><?= lang('Login.logout'); ?>
                </a>
                <button type="button" id="modal-button-save" class="btn btn-primary d-none">
                    <i class="bi bi-floppy me-2"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>
