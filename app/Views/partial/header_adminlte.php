<?php
/**
 * AdminLTE 4 Header Partial
 * @var object $user_info
 * @var array $allowed_modules
 * @var array $config
 */

use Config\Services;

$request = Services::request();
?>

<!doctype html>
<html lang="<?= $request->getLocale() ?>" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <base href="<?= base_url() ?>">
    <title><?= esc($config['company']) . ' | OSPOS' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="resources/adminlte/fontawesome/css/all.min.css">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="resources/adminlte/css/adminlte.min.css">
    <!-- Bootstrap 3 Compatibility -->
    <link rel="stylesheet" href="css/bs3-compat.css">

    <?php if (ENVIRONMENT == 'development' || get_cookie('debug') == 'true' || $request->getGet('debug') == 'true'): ?>
        <!-- inject:debug:css -->
        <!-- endinject -->
        <!-- inject:debug:js -->
        <!-- endinject -->
    <?php else: ?>
        <!--inject:prod:css -->
        <!-- endinject -->
        <!-- inject:prod:js -->
        <!-- endinject -->
    <?php endif; ?>

    <?= view('partial/header_js') ?>
    <?= view('partial/lang_lines') ?>

    <style>
        .sidebar-brand-text {
            font-weight: 600;
        }

        .nav-sidebar .nav-link.active {
            background-color: var(--bs-primary) !important;
        }

        .content-wrapper {
            min-height: calc(100vh - 101px);
        }
    </style>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <!-- Navbar -->
        <nav class="app-header navbar navbar-expand bg-body">
            <div class="container-fluid">
                <!-- Start navbar links -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                            <i class="fas fa-bars"></i>
                        </a>
                    </li>
                    <li class="nav-item d-none d-md-block">
                        <span class="nav-link"
                            id="liveclock"><?= date($config['dateformat'] . ' ' . $config['timeformat']) ?></span>
                    </li>
                </ul>

                <!-- Center - Company Name -->
                <div class="navbar-nav mx-auto">
                    <strong class="nav-link"><?= esc($config['company']) ?></strong>
                </div>

                <!-- End navbar links -->
                <ul class="navbar-nav ms-auto">
                    <!-- Dark/Light Mode Toggle -->
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-lte-toggle="dark-mode">
                            <i class="fas fa-moon"></i>
                        </a>
                    </li>
                    <!-- User Menu -->
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle fa-lg"></i>
                            <span
                                class="d-none d-md-inline"><?= "$user_info->first_name $user_info->last_name" ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <?= anchor("home/changePassword/$user_info->person_id", '<i class="fas fa-key me-2"></i>' . lang('Employees.change_password'), ['class' => 'dropdown-item modal-dlg', 'data-btn-submit' => lang('Common.submit')]) ?>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <?= anchor('home/logout', '<i class="fas fa-sign-out-alt me-2"></i>' . lang('Login.logout'), ['class' => 'dropdown-item']) ?>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
        <!-- /.navbar -->

        <!-- Sidebar -->
        <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
            <div class="sidebar-brand">
                <a href="<?= site_url() ?>" class="brand-link">
                    <?php if (isset($config['company_logo']) && !empty($config['company_logo'])): ?>
                        <img src="<?= base_url('uploads/' . $config['company_logo']) ?>" alt="Logo"
                            class="brand-image opacity-75 shadow" style="max-height: 33px;">
                    <?php else: ?>
                        <i class="fas fa-cash-register brand-image opacity-75"></i>
                    <?php endif; ?>
                    <span class="brand-text fw-light">OSPOS</span>
                </a>
            </div>

            <div class="sidebar-wrapper">
                <nav class="mt-2">
                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu"
                        data-accordion="false">
                        <?php
                        // Icon mapping for modules
                        $module_icons = [
                            'home' => 'fa-home',
                            'office' => 'fa-building',
                            'sales' => 'fa-shopping-cart',
                            'receivings' => 'fa-truck',
                            'items' => 'fa-boxes',
                            'item_kits' => 'fa-box-open',
                            'suppliers' => 'fa-industry',
                            'customers' => 'fa-users',
                            'employees' => 'fa-user-tie',
                            'giftcards' => 'fa-gift',
                            'reports' => 'fa-chart-bar',
                            'config' => 'fa-cog',
                            'expenses' => 'fa-file-invoice-dollar',
                            'expenses_categories' => 'fa-tags',
                            'taxes' => 'fa-percent',
                            'cashups' => 'fa-cash-register',
                            'attributes' => 'fa-star',
                            'messages' => 'fa-envelope',
                        ];

                        foreach ($allowed_modules as $module):
                            $is_active = $module->module_id == $request->getUri()->getSegment(1);
                            $icon = $module_icons[$module->module_id] ?? 'fa-circle';
                            ?>
                            <li class="nav-item">
                                <a href="<?= base_url($module->module_id) ?>"
                                    class="nav-link <?= $is_active ? 'active' : '' ?>">
                                    <i class="nav-icon fas <?= $icon ?>"></i>
                                    <p><?= lang('Module.' . $module->module_id) ?></p>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            </div>
        </aside>
        <!-- /.sidebar -->

        <!-- Main Content -->
        <main class="app-main">
            <div class="app-content">
                <div class="container-fluid">