<?php
/**
 * @var string $controller_name
 * @var object $person_info
 * @var array $all_modules
 * @var array $all_subpermissions
 * @var int $employee_id
 */
?>

<?= form_open("$controller_name/save/$person_info->person_id", ['id' => 'employee_form']) ?>

    <ul class="nav nav-pills nav-justified mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link active" data-bs-toggle="pill" data-bs-target="#employee_basic_info" role="tab"><?= lang('Employees.basic_information') ?></button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link" data-bs-toggle="pill" data-bs-target="#employee_login_info" role="tab"><?= lang('Employees.login_info') ?></button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link" data-bs-toggle="pill" data-bs-target="#employee_permission_info" role="tab"><?= lang('Employees.permission_info') ?></button>
        </li>
    </ul>

    <ul id="error_message_box" class="alert alert-warning d-none"></ul>

    <div class="tab-content">
        <div class="tab-pane show active" id="employee_basic_info" role="tabpanel" tabindex="0">
            <?= view('people/form_basic_info') ?>
        </div>

        <div class="tab-pane" id="employee_login_info" role="tabpanel" tabindex="0">
            <label for="username" class="form-label"><?= lang('Employees.username'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="username-icon"><i class="bi bi-person"></i></span>
                <input type="text" class="form-control" name="username" id="username" aria-describedby="username-icon" value="<?= $person_info->username; ?>" required>
            </div>

            <?php $password_label_attributes = $person_info->person_id == "" ? ['class' => 'required'] : []; ?>
            <label for="password" class="form-label"><?= lang('Employees.password'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="password-icon"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control" name="password" id="password" aria-describedby="password-icon" required>
            </div>

            <label for="repeat_password" class="form-label"><?= lang('Employees.repeat_password'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="repeat_password-icon"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control" name="repeat_password" id="repeat_password" aria-describedby="repeat_password-icon" required>
            </div>

            <label for="language" class="form-label"><?= lang('Employees.language'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="language-icon"><i class="bi bi-globe"></i></span>
                <?php
                $languages = get_languages();
                $languages[':'] = lang('Employees.system_language');
                $language_code = current_language_code();
                $language = current_language();

                // If No language is set then it will display "System Language"
                if ($language_code === current_language_code(true)) {
                    $language_code = '';
                    $language = '';
                }

                echo form_dropdown(
                    'language',
                    $languages,
                    "$language_code:$language",
                    ['class' => 'form-select']
                );
                ?>
            </div>
        </div>

        <div class="tab-pane" id="employee_permission_info" role="tabpanel" tabindex="0">
            <div class="mb-3"><?= lang('Employees.permission_desc') ?></div>
            <ul class="list-unstyled" id="permission_list">
                <?php foreach ($all_modules as $module): ?>
                    <li class="form-check">
                        <input class="form-check-input module" type="checkbox" value="<?= $module->module_id ?>" name="grant_<?= $module->module_id ?>" id="grant_<?= $module->module_id ?>" <?= $module->grant == 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="grant_<?= $module->module_id ?>">
                            <select class="form-select form-select-sm d-inline-block w-auto me-1 module" name="menu_group_<?= $module->module_id ?>">
                                <option value="home" <?= $module->menu_group == 'home' ? 'selected' : '' ?>>
                                    <?= lang('Module.home') ?>
                                </option>
                                <option value="office" <?= $module->menu_group == 'office' ? 'selected' : '' ?>>
                                    <?= lang('Module.office') ?>
                                </option>
                                <option value="both" <?= $module->menu_group == 'both' ? 'selected' : '' ?>>
                                    <?= lang('Module.both') ?>
                                </option>
                            </select>
                            <span><?= lang("Module.$module->module_id") ?>:</span>
                            <span class="fw-light fst-italic"><?= lang("Module.$module->module_id" . '_desc') ?></span>
                        </label>

                        <?php foreach ($all_subpermissions as $permission): ?>
                            <?php
                                $exploded_permission = explode('_', $permission->permission_id, 2);

                                if ($permission->module_id != $module->module_id) {
                                    continue;
                                }

                                $lang_key = $module->module_id . '.' . $exploded_permission[1];
                                $lang_line = lang(ucfirst($lang_key));

                                // Fallback if language line doesn't exist
                                if ($lang_line === lang(ucfirst($lang_key))) {
                                    $lang_line = ucwords(str_replace("_", " ", $exploded_permission[1]));
                                }

                                if (empty($lang_line)) {
                                    continue;
                                }
                            ?>
                            <ul class="list-unstyled">
                                <li class="form-check">
                                    <input class="form-check-input module" type="checkbox" value="<?= $permission->permission_id ?>" name="grant_<?= $permission->permission_id ?>" id="grant_<?= $permission->permission_id ?>" <?= $permission->grant == 1 ? 'checked' : '' ?>>
                                    <input type="hidden" name="menu_group_<?= $permission->permission_id ?>" value="--">
                                    <label class="form-check-label" for="grant_<?= $permission->permission_id ?>"><?= esc($lang_line) ?></label>
                                </li>
                            </ul>
                        <?php endforeach; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        $.validator.setDefaults({
            ignore: []
        });

        $.validator.addMethod('module', function(value, element) {
            var result = $('#permission_list input').is(':checked');
            $('.module').each(function(index, element) {
                var parent = $(element).parent();
                var checked = $(element).is(':checked');
                if ($('ul', parent).length > 0 && result) {
                    result &= !checked || (checked && $('ul > li > input:checked', parent).length > 0);
                }
            });
            return result;
        }, "<?= lang('Employees.subpermission_required') ?>");

        $('ul#permission_list > li > input.module').each(function() {
            var $this = $(this);
            $('ul > li > input,select', $this.parent()).each(function() {
                var $that = $(this);
                var updateInputs = function(checked) {
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
                    success: function(response) {
                        dialog_support.hide();
                        table_support.handle_submit("<?= esc($controller_name) ?>", response);
                    },
                    dataType: 'json'
                });
            },

            errorLabelContainer: '#error_message_box',

            rules: {
                first_name: 'required',
                last_name: 'required',
                username: {

                    required: true,
                    minlength: 5,
                    remote: '<?= esc("$controller_name/checkUsername/$employee_id") ?>'
                },
                password: {
                    <?php if ($person_info->person_id == '') { ?>
                        required: true,
                    <?php } ?>
                    minlength: 8
                },
                repeat_password: {
                    equalTo: '#password'
                },
                email: 'email'
            },

            messages: {
                first_name: "<?= lang('Common.first_name_required') ?>",
                last_name: "<?= lang('Common.last_name_required') ?>",
                username: {
                    required: "<?= lang('Employees.username_required') ?>",
                    minlength: "<?= lang('Employees.username_minlength') ?>",
                    remote: "<?= lang('Employees.username_duplicate') ?>"
                },
                password: {
                    <?php if ($person_info->person_id == "") { ?>
                        required: "<?= lang('Employees.password_required') ?>",
                    <?php } ?>
                    minlength: "<?= lang('Employees.password_minlength') ?>"
                },
                repeat_password: {
                    equalTo: "<?= lang('Employees.password_must_match') ?>"
                },
                email: "<?= lang('Common.email_invalid_format') ?>"
            }
        }, form_support.error));
    });
</script>
