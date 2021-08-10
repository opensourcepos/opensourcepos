<!doctype html>
<html lang="<?= current_language_code(); ?>">

<head>
  <meta charset="utf-8">
  <base href="<?= base_url(); ?>">
  <title><?= $this->config->item('company') . '&nbsp;|&nbsp;' . $this->lang->line('common_powered_by') . '&nbsp;' . $this->lang->line('common_software_short') . '&nbsp;' . $this->config->item('application_version') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
  <link rel="stylesheet" type="text/css" href="<?= 'dist/bootswatch/' . (empty($this->config->item('theme')) ? 'flatly' : $this->config->item('theme')) . '/bootstrap.min.css' ?>">
  <link rel="stylesheet" type="text/css" href="dist/jasny-bootstrap/jasny-bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="dist/bootstrap-select/bootstrap-select.min.css">
  <link rel="stylesheet" type="text/css" href="dist/bootstrap-table/bootstrap-table.min.css">
  <link rel="stylesheet" type="text/css" href="dist/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" type="text/css" href="css/new.css">
  <meta name="theme-color" content="#2c3e50">
</head>

<body class="d-flex flex-column">
  <header class="flex-shrink-0 small bg-light py-1">
    <div class="container-lg container-navbar d-flex flex-wrap-reverse justify-content-between align-items-center">
      <div class="flex-grow-1 d-none d-md-block ps-md-3 ps-lg-0">
        <span id="clock">
          <?= date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat')) ?>
        </span>
      </div>
      <div class="fw-bold ps-3 ps-md-0">
        <?= $this->config->item('company'); ?>
      </div>
      <div class="flex-grow-1 text-end pe-3 pe-lg-0">
        <?= ($this->input->get('debug') == 'true' ? $this->session->userdata('session_sha1') : ''); ?>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="removeAnimationBg()" data-bs-toggle="modal" data-bs-target="#profile-modal" title="<?= $this->lang->line('employees_change_password'); ?>">
          <?= $user_info->first_name . '&nbsp;' . $user_info->last_name; ?>
        </button>
        <?php $this->load->view("home/profile"); ?>
      </div>
    </div>
  </header>

  <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-0">
    <div class="container-lg container-navbar">
      <a class="navbar-brand py-2 pe-1 ps-3 ps-lg-0" href="<?= site_url(); ?>"><?= $this->lang->line('common_software_short'); ?></a>
      <button class="navbar-toggler my-2 mx-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbar">
        <ul class="navbar-nav ms-0 ms-lg-auto">
          <?php foreach ($allowed_modules as $module) : ?>
            <li class="d-none d-lg-block nav-item ms-1 <?= $module->module_id == $this->uri->segment(1) ? 'active bg-body border border-2 border-top-0 border-bottom-0 border-secondary' : ''; ?>" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?= $this->lang->line("module_" . $module->module_id); ?>">
              <a class="nav-link p-2" href="<?= site_url("$module->module_id"); ?>">
                <img src="<?= base_url() . 'images/menubar/' . $module->module_id . '.svg'; ?>" alt="<?= $this->lang->line('common_icon') . '&nbsp;' . $this->lang->line("module_" . $module->module_id); ?>">
              </a>
            </li>
            <li class="d-lg-none nav-item py-1 <?= $module->module_id == $this->uri->segment(1) ? 'active bg-light' : ''; ?>">
              <a class="nav-link p-0 <?= $module->module_id == $this->uri->segment(1) ? 'text-body' : ''; ?>" href="<?= site_url("$module->module_id"); ?>">
                <img class="ps-3 pe-1 my-1" src="<?= base_url() . 'images/menubar/' . $module->module_id . '.svg'; ?>" alt="<?= $this->lang->line('common_icon') . '&nbsp;' . $this->lang->line("module_" . $module->module_id); ?>">
                <span class="align-middle"><?= $this->lang->line("module_" . $module->module_id) ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </nav>

  <main class="container-lg flex-grow-1 py-3">