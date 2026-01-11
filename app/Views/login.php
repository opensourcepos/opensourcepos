<?php
/**
 * AdminLTE Login Page
 * @var bool $has_errors
 * @var bool $is_latest
 * @var string $latest_version
 * @var bool $gcaptcha_enabled
 * @var array $config
 * @var $validation
 */
?>

<!doctype html>
<html lang="<?= current_language_code() ?>" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <base href="<?= base_url() ?>">
    <title><?= $config['company'] . '&nbsp;|&nbsp;OSPOS&nbsp;|&nbsp;' . lang('Login.login') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="resources/adminlte/fontawesome/css/all.min.css">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="resources/adminlte/css/adminlte.min.css">

    <style>
        .login-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .login-box {
            width: 400px;
        }

        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem 0 rgba(0, 0, 0, 0.1);
        }

        .login-logo a {
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .login-logo img {
            max-height: 80px;
            filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.3));
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .input-group-text {
            background-color: transparent;
            border-left: none;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-control {
            border-right: none;
        }
    </style>
</head>

<body class="login-page bg-body-secondary">
    <div class="login-box">
        <!-- Logo -->
        <div class="login-logo mb-4">
            <?php if (isset($config['company_logo']) && !empty($config['company_logo'])): ?>
                <img src="<?= base_url('uploads/' . $config['company_logo']) ?>"
                    alt="<?= lang('Common.logo') . '&nbsp;' . $config['company'] ?>">
            <?php else: ?>
                <a href="<?= base_url() ?>">
                    <i class="fas fa-cash-register fa-3x mb-2"></i>
                    <br>
                    <span class="fw-bold">OSPOS</span>
                </a>
            <?php endif; ?>
        </div>

        <!-- Login Card -->
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <h4 class="mb-0"><?= lang('Login.welcome', [lang('Common.software_short')]) ?></h4>
            </div>
            <div class="card-body login-card-body">
                <?php if ($has_errors): ?>
                    <?php foreach ($validation->getErrors() as $error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            <?= $error ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!$is_latest): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <?= lang('Login.migration_needed', [$latest_version]) ?>
                    </div>
                <?php endif; ?>

                <?= form_open('login') ?>
                <div class="input-group mb-3">
                    <input type="text" name="username" class="form-control" placeholder="<?= lang('Login.username') ?>"
                        <?php if (ENVIRONMENT == "testing")
                            echo 'value="admin"'; ?>>
                    <span class="input-group-text">
                        <i class="fas fa-user"></i>
                    </span>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control"
                        placeholder="<?= lang('Login.password') ?>" <?php if (ENVIRONMENT == "testing")
                              echo 'value="pointofsale"'; ?>>
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                </div>

                <?php
                if ($gcaptcha_enabled) {
                    echo '<script src="https://www.google.com/recaptcha/api.js"></script>';
                    echo '<div class="g-recaptcha mb-3" style="text-align: center;" data-sitekey="' . $config['gcaptcha_site_key'] . '"></div>';
                }
                ?>

                <div class="d-grid">
                    <button type="submit" name="login-button" class="btn btn-primary btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i><?= lang('Login.go') ?>
                    </button>
                </div>
                <?= form_close() ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-4">
            <small class="text-white-50">
                <?= lang('Common.software_title') ?> &copy; <?= date('Y') ?>
            </small>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="resources/adminlte/js/bootstrap.bundle.min.js"></script>
    <script src="resources/adminlte/js/adminlte.min.js"></script>
</body>

</html>