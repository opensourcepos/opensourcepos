<?php
/**
 * @var bool $has_errors
 * @var bool $is_latest
 * @var bool $is_new_install
 * @var string $latest_version
 * @var bool $gcaptcha_enabled
 * @var array $config
 * @var $validation
 */

// Manually setting some error messages for testing
// $validation->setError('test', 'The test error is testing.');
// $validation->setError('email', 'The email field is required.');
// $has_errors = $validation->hasError('email');
// $has_errors = $validation->hasError('test');

// Manually set database migration message to show
// $is_latest = false;
// $latest_version = '1.0.0';

// Manually set language for page, doesn't work for dynamic elements
// \Config\Services::language()->setLocale('de-DE');

// Manually set login form appearence
// $config['login_form'] = 'input_groups';

// Manually set theme
// $config['theme'] = 'cerulean';

?>

<!doctype html>
<html lang="<?= $request->getLocale() ?>" data-bs-theme="<?= esc($config['color_mode']) ?>" <?= esc($config['rtl_language']) == 1 ? 'dir="rtl"' : '' ?>>

<head>
    <meta charset="utf-8">
    <base href="<?= base_url() ?>">
    <title><?= esc($config['company']) . '&nbsp;|&nbsp;' . esc(lang('Common.software_short')) . '&nbsp;|&nbsp;' . esc(lang('Login.login')) ?></title>
    <?= $config['responsive_design'] == 1 ? '<meta name="viewport" content="width=device-width, initial-scale=1">' : '' ?>
    <meta name="robots" content="noindex, nofollow">
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
    <?php $theme = (empty($config['theme']) ? 'flatly' : $config['theme']); ?>
    <link rel="stylesheet" href="resources/bootswatch/<?= "$theme" ?>/bootstrap<?= esc($config['rtl_language']) == 1 ? '.rtl' : '' ?>.min.css">
    <link rel="stylesheet" href="resources/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/login.css">
    <meta name="theme-color" content="#2c3e50">
</head>

<body class="bg-secondary-subtle d-flex flex-column">
    <!-- BS5 WIP message -->
    <div data-notify="container" class="col-11 col-sm-9 col-md-7 col-lg-6 col-xl-5 col-xxl-4 alert alert-warning position-absolute top-0 mt-3 start-50 translate-middle-x" role="alert">
        <span data-notify="icon" class="me-2" role="img"><i class="bi bi-exclamation-diamond-fill"></i></span>
        <span data-notify="message">WIP conversion to Bootstrap 5</span>
    </div>
    <main class="d-flex justify-content-around align-items-center flex-grow-1">
        <div class="container-login container-fluid d-flex flex-column flex-md-row bg-body shadow rounded m-3 p-4 p-md-0">
            <div class="box-logo d-flex flex-column justify-content-center align-items-center border-end border-secondary-subtle px-4 pb-3 p-md-4">
                <?php if (isset($config['company_logo']) && !empty($config['company_logo'])): ?>
                    <img class="logo w-100" src="<?= base_url('uploads/' . esc($config['company_logo'], 'url')) ?>" alt="<?= esc(lang('Common.logo') . '&nbsp;' . $config['company']) ?>">
                <?php else: ?>
                    <svg class="logo text-primary" role="img" viewBox="0 0 308.57998 308.57997" xmlns="http://www.w3.org/2000/svg">
                        <title><?= lang('Common.software_title') . '&nbsp;' . lang('Common.logo') ?></title>
                        <circle cx="154.28999" cy="154.28999" r="154.28999" fill="currentColor" />
                        <path fill="#fff" d="M154.88998 145.66999c-.03-1.26-.03-3.29.19-4.29 4.6-11.1 15.57-18.82 28.3-18.82h.41v58.3c0 .12-.03.78-.04.9-.54 16.46-14.01 29.7-30.59 29.7v27.08c21 0 39.17-11.27 49.29-28.07l.07-.11c2.9.45 5.86.75 8.9.75 31.95 0 57.81-26 57.81-57.81 0-30.87-24.37-56.46-55.1-57.81h-30.74c-17.18 0-32.61 7.64-43.22 19.63-10.59-11.92-25.86-19.59-43.02-19.59-31.86 0-57.77 25.91-57.77 57.77 0 31.86 25.91 57.77 57.77 57.77 31.86 0 57.77-25.91 57.77-57.77v-3.68c-.01.01-.02-3.31-.03-3.95zm-57.75 38.33c-16.92 0-30.69-13.77-30.69-30.69s13.77-30.69 30.69-30.69 30.69 13.77 30.69 30.69-13.77 30.69-30.69 30.69zm142.96-19.87c-4.33 11.64-15.57 19.9-28.7 19.9h-.54v-61.47h.54c13.13 0 24.37 8.26 28.7 19.9 1.35 3.25 2.03 6.91 2.03 10.83s-.67 7.59-2.03 10.84z" />
                    </svg>
                <?php endif; ?>
            </div>
            <section class="box-login d-flex flex-column justify-content-center align-items-center p-md-4">
                <?= form_open('login', ['id' => 'login-form']) ?>
                
                <h3 id="form-heading" class="text-center m-0">
                    <?php if (!$is_latest || $is_new_install): ?>
                        <?= lang('Login.migration_required') ?>
                    <?php else: ?>
                        <?= lang('Login.welcome', [lang('Common.software_short')]) ?>
                    <?php endif; ?>
                </h3>
                
                <div id="migration-warning" class="alert alert-warning mt-3<?= $is_new_install ? '' : ' d-none' ?>">
                    <?= lang('Login.migration_auth_message', [$latest_version]) ?>
                </div>
                
                <?php if ($has_errors): ?>
                    <?php foreach ($validation->getErrors() as $error): ?>
                        <div class="alert alert-danger mt-3">
                            <?= $error ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div id="migration-success" class="alert alert-success d-none mt-3">
                    <strong><?= lang('Login.migration_complete') ?></strong> <?= lang('Login.migration_complete_login') ?>
                </div>
                
                <div id="migration-progress" class="d-none mt-4">
                    <h3 class="text-center mb-4"><?= lang('Login.migration_initializing') ?></h3>
                    <div class="progress mb-3" style="height: 30px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                             role="progressbar" 
                             style="width: 100%">
                        </div>
                    </div>
                    <p class="text-center text-muted" id="migration-status">
                        <?= lang('Login.migration_running') ?>
                    </p>
                </div>
                
                <div id="migration-error" class="alert alert-danger d-none mt-3" role="alert">
                    <strong>Error:</strong> <span id="migration-error-message"></span>
                </div>
                
                <div id="login-fields" class="w-100<?= $is_new_install ? ' d-none' : '' ?>">
                    <?php if (empty($config['login_form']) || 'floating_labels' == ($config['login_form'])): ?>
                        <div class="form-floating mt-3">
                            <input class="form-control" id="input-username" name="username" type="text" placeholder="<?= lang('Login.username') ?>" <?php if (ENVIRONMENT == "testing") echo 'value="admin"'; ?>>
                            <label for="input-username"><?= lang('Login.username') ?></label>
                        </div>
                        <div class="form-floating mb-3">
                            <input class="form-control" id="input-password" name="password" type="password" placeholder="<?= lang('Login.password') ?>" <?php if (ENVIRONMENT == "testing") echo 'value="pointofsale"'; ?>>
                            <label for="input-password"><?= lang('Login.password') ?></label>
                        </div>
                    <?php elseif ('input_groups' == ($config['login_form'])): ?>
                        <div class="input-group mt-3">
                            <span class="input-group-text" id="input-username">
                                <i class="bi bi-person" title="<?= lang('Common.icon') . '&nbsp;' . lang('Login.username') ?>"></i>
                            </span>
                            <input class="form-control" name="username" type="text" placeholder="<?= lang('Login.username'); ?>" aria-label="<?= lang('Login.username') ?>" aria-describedby="input-username" <?php if (ENVIRONMENT == "testing") echo 'value="admin"'; ?>>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="input-password">
                                <i class="bi bi-lock" title="<?= lang('Common.icon') . '&nbsp;' . lang('Login.password') ?>"></i>
                            </span>
                            <input class="form-control" name="password" type="password" placeholder="<?= lang('Login.password') ?>" aria-label="<?= lang('Login.password') ?>" aria-describedby="input-password" <?php if (ENVIRONMENT == "testing") echo 'value="pointofsale"'; ?>>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($gcaptcha_enabled): ?>
                        <script src="https://www.google.com/recaptcha/api.js"></script>
                        <div class="g-recaptcha mb-3" style="text-align: center;" data-sitekey="<?= esc($config['gcaptcha_site_key']) ?>"></div>
                    <?php endif; ?>
                </div>
                
                <div class="d-grid">
                    <button id="submit-button" class="btn btn-lg btn-primary" name="login-button" type="submit">
                        <?php if ($is_new_install): ?>
                            <?= lang('Module.migrate') ?>
                        <?php else: ?>
                            <?= lang('Login.go') ?>
                        <?php endif; ?>
                    </button>
                </div>
                <?= form_close() ?>
            </section>
        </div>
    </main>

    <footer class="d-flex justify-content-center flex-shrink-0 text-center">
        <div class="footer container-fluid bg-body rounded shadow p-3 mb-md-4 mx-md-3">
            <span class="text-primary">
                <svg height="1.25em" role="img" viewBox="0 0 308.57998 308.57997" xmlns="http://www.w3.org/2000/svg">
                    <title><?= lang('Common.software_title') . '&nbsp;' . lang('Common.logo') ?></title>
                    <circle cx="154.28999" cy="154.28999" r="154.28999" fill="currentColor" />
                    <path fill="#fff" d="M154.88998 145.66999c-.03-1.26-.03-3.29.19-4.29 4.6-11.1 15.57-18.82 28.3-18.82h.41v58.3c0 .12-.03.78-.04.9-.54 16.46-14.01 29.7-30.59 29.7v27.08c21 0 39.17-11.27 49.29-28.07l.07-.11c2.9.45 5.86.75 8.9.75 31.95 0 57.81-26 57.81-57.81 0-30.87-24.37-56.46-55.1-57.81h-30.74c-17.18 0-32.61 7.64-43.22 19.63-10.59-11.92-25.86-19.59-43.02-19.59-31.86 0-57.77 25.91-57.77 57.77 0 31.86 25.91 57.77 57.77 57.77 31.86 0 57.77-25.91 57.77-57.77v-3.68c-.01.01-.02-3.31-.03-3.95zm-57.75 38.33c-16.92 0-30.69-13.77-30.69-30.69s13.77-30.69 30.69-30.69 30.69 13.77 30.69 30.69-13.77 30.69-30.69 30.69zm142.96-19.87c-4.33 11.64-15.57 19.9-28.7 19.9h-.54v-61.47h.54c13.13 0 24.37 8.26 28.7 19.9 1.35 3.25 2.03 6.91 2.03 10.83s-.67 7.59-2.03 10.84z" />
                </svg>
            </span>
            <span><?= lang('Common.software_title') ?></span>
        </div>
    </footer>

    <?php
    use Config\Services;
    $request = Services::request();
    ?>

    <?php if (ENVIRONMENT == 'development' || get_cookie('debug') == 'true' || $request->getGet('debug') == 'true') : ?>
        <!-- inject:login:debug:js -->
        <!-- endinject -->
    <?php else : ?>
        <!-- inject:login:prod:js -->
        <!-- endinject -->
    <?php endif; ?>
    <script>
        const APP_STATE = {
            isNewInstall: <?= $is_new_install ? 'true' : 'false' ?>,
            isLatest: <?= $is_latest ? 'true' : 'false' ?>,
            csrfToken: '<?= csrf_token() ?>',
            csrfHash: '<?= csrf_hash() ?>',
            migrateUrl: '<?= site_url('migrate') ?>',
            loginUrl: '<?= site_url('login') ?>',
            i18n: {
                welcome: <?= json_encode(lang('Login.welcome', [lang('Common.software_short')])) ?>,
                migrate: <?= json_encode(lang('Module.migrate')) ?>,
                go: <?= json_encode(lang('Login.go')) ?>,
                migrationRequired: <?= json_encode(lang('Login.migration_required')) ?>,
                migrationInitializing: <?= json_encode(lang('Login.migration_initializing')) ?>,
                migrationRunning: <?= json_encode(lang('Login.migration_running')) ?>,
                migrationComplete: <?= json_encode(lang('Login.migration_complete')) ?>,
                migrationCompleteLogin: <?= json_encode(lang('Login.migration_complete_login')) ?>,
                migrationFailed: <?= json_encode(lang('Login.migration_failed')) ?>,
                migrationErrorConnection: <?= json_encode(lang('Login.migration_error_connection')) ?>
            }
        };

        $(document).ready(function() {
            const $form = $('#login-form');
            const $heading = $('#form-heading');
            const $warning = $('#migration-warning');
            const $success = $('#migration-success');
            const $progress = $('#migration-progress');
            const $error = $('#migration-error');
            const $errorMessage = $('#migration-error-message');
            const $loginFields = $('#login-fields');
            const $submitButton = $('#submit-button');

            function showMigrationRequired() {
                $heading.text(APP_STATE.i18n.migrationRequired);
                $warning.removeClass('d-none');
                $success.addClass('d-none');
                $progress.addClass('d-none');
                $error.addClass('d-none');
                $loginFields.addClass('d-none');
                $submitButton.text(APP_STATE.i18n.migrate);
            }

            function showMigrationProgress() {
                $warning.addClass('d-none');
                $success.addClass('d-none');
                $error.addClass('d-none');
                $loginFields.addClass('d-none');
                $progress.removeClass('d-none');
                $submitButton.prop('disabled', true);
            }

            function showMigrationSuccess() {
                $progress.addClass('d-none');
                $error.addClass('d-none');
                $warning.addClass('d-none');
                $success.removeClass('d-none');
                $heading.text(APP_STATE.i18n.welcome);
                $loginFields.removeClass('d-none');
                $submitButton.text(APP_STATE.i18n.go);
                $submitButton.prop('disabled', false);
            }

            function showMigrationError(message) {
                $progress.addClass('d-none');
                $success.addClass('d-none');
                $loginFields.addClass('d-none');
                $errorMessage.text(message);
                $error.removeClass('d-none');
                $warning.addClass('d-none');
                $submitButton.text(APP_STATE.i18n.migrate);
                $submitButton.prop('disabled', false);
            }

            function showLoginForm() {
                $heading.text(APP_STATE.i18n.welcome);
                $warning.addClass('d-none');
                $progress.addClass('d-none');
                $error.addClass('d-none');
                $success.addClass('d-none');
                $loginFields.removeClass('d-none');
                $submitButton.text(APP_STATE.i18n.go);
            }

            if (!APP_STATE.isNewInstall) {
                showLoginForm();
            }

            $form.on('submit', function(e) {
                if (APP_STATE.isNewInstall) {
                    e.preventDefault();
                    
                    showMigrationProgress();
                    
                    $.ajax({
                        url: APP_STATE.migrateUrl,
                        type: 'POST',
                        dataType: 'json',
                        timeout: 3600000,
                        data: {
                            [APP_STATE.csrfToken]: APP_STATE.csrfHash
                        },
                        success: function(response) {
                            if (response.success) {
                                APP_STATE.isNewInstall = false;
                                showMigrationSuccess();
                            } else {
                                showMigrationError(response.message || APP_STATE.i18n.migrationFailed);
                            }
                        },
                        error: function(xhr, status, error) {
                            let message = APP_STATE.i18n.migrationErrorConnection;
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            showMigrationError(message);
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>
