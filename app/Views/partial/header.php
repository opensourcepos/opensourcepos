<?php
/**
 * @var object $user_info
 * @var array $allowed_modules
 * @var CodeIgniter\HTTP\IncomingRequest $request
 * @var array $config
 */

use Config\Services;

$request = Services::request();

// Services::language()->setLocale('de-DE');
?>

<!doctype html>
<html lang="<?= current_language_code() ?>" data-bs-theme="<?= $config['color_mode'] ?>" <?= $config['rtl_language'] == 1 ? 'dir="rtl"' : '' ?>>

<head>
    <meta charset="utf-8">
    <base href="<?= base_url() ?>">
    <title><?= esc($config['company']) . ' | ' . lang('Common.powered_by') . ' OSPOS ' . esc(config('App')->application_version) ?></title>
    <?= $config['responsive_design'] == 1 ? '<meta name="viewport" content="width=device-width, initial-scale=1">' : '' ?>
    <meta name="robots" content="noindex, nofollow">
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
    <?php $theme = (empty($config['theme']) ? 'flatly' : esc($config['theme'])); ?>
    <link rel="stylesheet" href="resources/bootswatch/<?= "$theme" ?>/bootstrap<?= $config['rtl_language'] == 1 ? '.rtl' : '' ?>.min.css">
    <link rel="stylesheet" href="resources/bootstrap-icons/bootstrap-icons.min.css">

    <?php if (ENVIRONMENT == 'development' || get_cookie('debug') == 'true' || $request->getGet('debug') == 'true') : ?>
        <!-- inject:debug:css -->
        <!-- endinject -->
        <!-- inject:debug:js -->
        <!-- endinject -->
    <?php else : ?>
        <!--inject:prod:css -->
        <!-- endinject -->

        <!-- Tweaks to the UI for a particular theme should drop here  -->
        <?php if ($config['theme'] != 'flatly' && file_exists($_SERVER['DOCUMENT_ROOT'] . '/public/css/' . esc($config['theme']) . '.css')) { ?>
            <link rel="stylesheet" href="<?= 'css/' . esc($config['theme']) . '.css' ?>">
        <?php } ?>
        <!-- inject:prod:js -->
        <!-- endinject -->
    <?php endif; ?>

    <?= view('partial/header_js') ?>
    <?= view('partial/lang_lines') ?>

</head>

<body class="d-flex flex-column">
    <header class="flex-shrink-0 small bg-secondary-subtle py-1 d-print-none">
        <div class="container-lg container-navbar d-flex flex-wrap-reverse justify-content-between align-items-center">
            <div class="flex-grow-1 d-none d-md-block ps-md-3 ps-lg-0">
                <span id="liveclock"><?= date($config['dateformat'] . ' ' . $config['timeformat']) ?></span>
            </div>
            <div class="fw-bold ps-3 ps-md-0">
                <?= esc($config['company']) ?>
            </div>
            <div class="flex-grow-1 text-end pe-3 pe-lg-0">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="removeAnimationBg()" data-bs-toggle="modal" data-bs-target="#profile-modal" title="<?= lang('Employees.change_password'); ?>">
                    <?= $user_info->first_name . '&nbsp;' . $user_info->last_name; ?>
                </button>
                <?= view('home/profile'); ?>
            </div>
        </div>
    </header>

    <nav class="navbar navbar-dark navbar-expand-lg bg-primary py-0 d-print-none">
        <div class="container-lg container-navbar">
            <a class="navbar-brand py-2 pe-1 ps-3 ps-lg-0 fs-4" href="<?= site_url() ?>"><i class="bi bi-house-fill"></i></a>
            <button class="navbar-toggler my-2 mx-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbar">
                <ul class="navbar-nav ms-0 ms-lg-auto">
                    <?php foreach($allowed_modules as $module): ?>
                    <li class="d-none d-lg-block nav-item ms-1 <?= $module->module_id == $request->getUri()->getSegment(1) ? 'active bg-light bg-opacity-25' : '' ?>" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= lang("Module.$module->module_id") ?>">
                        <a class="nav-link p-2" href="<?= base_url($module->module_id) ?>">
                            <img src="<?= base_url("images/menubar/$module->module_id.svg") ?>" alt="<?= lang('Common.icon') . '&nbsp;' . lang("Module.$module->module_id") ?>">
                        </a>
                    </li>
                    <li class="d-lg-none nav-item py-1 <?= $module->module_id == $request->getUri()->getSegment(1) ? 'active bg-light bg-opacity-25' : '' ?>">
                        <a class="nav-link p-0" href="<?= base_url($module->module_id) ?>">
                            <img class="ps-3 pe-1 my-1" src="<?= base_url("images/menubar/$module->module_id.svg") ?>" alt="<?= lang('Common.icon') . '&nbsp;' . lang("Module.$module->module_id") ?>">
                            <span class="align-middle text-light"><?= lang("Module.$module->module_id") ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container-lg flex-grow-1 py-3">
