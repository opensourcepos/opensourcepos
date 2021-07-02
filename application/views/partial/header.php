<!doctype html>
<html lang="<?php echo current_language_code(); ?>">

<head>
  <meta charset="utf-8">
  <base href="<?php echo base_url(); ?>">
  <title><?php echo $this->config->item('company') . '&nbsp;|&nbsp;' . $this->lang->line('common_powered_by') . '&nbsp;' . $this->lang->line('common_software_short') . '&nbsp;' . $this->config->item('application_version') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
  <link rel="stylesheet" type="text/css" href="<?php echo 'dist/bootswatch-5/' . (empty($this->config->item('theme')) ? 'flatly' : $this->config->item('theme')) . '/bootstrap.min.css' ?>">
  <link rel="stylesheet" type="text/css" href="css/new.css">
  <meta name="theme-color" content="#2288bb">
</head>

<body class="d-flex flex-column">
  <header class="flex-shrink-0 small bg-light py-1">
    <div class="container-lg d-flex flex-wrap-reverse justify-content-between align-items-center">
      <div class="flex-grow-1 d-none d-md-block">
        <span id="clock">
          <?php echo date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat')) ?>
        </span>
      </div>
      <div class="fw-bold">
        <?php echo $this->config->item('company'); ?>
      </div>
      <div class="flex-grow-1 text-end">
        <?php echo ($this->input->get('debug') == 'true' ? $this->session->userdata('session_sha1') : ''); ?>
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="removeAnimationBg()" data-bs-toggle="modal" data-bs-target="#profile-modal" title="<?php echo $this->lang->line('employees_change_password'); ?>">
          <?php echo $user_info->first_name . '&nbsp;' . $user_info->last_name; ?>
        </button>
        <?php $this->load->view("home/profile"); ?>
      </div>
    </div>
  </header>

  <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-0">
    <div class="container-lg px-0">
      <a class="navbar-brand p-2" href="<?php echo site_url(); ?>"><?php echo $this->lang->line('common_software_short'); ?></a>
      <button class="navbar-toggler m-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbar">
        <ul class="navbar-nav ms-0 ms-lg-auto">
          <?php foreach ($allowed_modules as $module) : ?>
            <li class="d-none d-lg-block nav-item mt-1 ms-1 <?php echo $module->module_id == $this->uri->segment(1) ? 'active bg-body border border-2 border-bottom-0 border-secondary rounded-top' : ''; ?>" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php echo $this->lang->line("module_" . $module->module_id); ?>">
              <a class="nav-link p-2 mb-1 <?php echo $module->module_id == $this->uri->segment(1) ? 'text-body' : ''; ?>" href="<?php echo site_url("$module->module_id"); ?>">
                <img src="<?php echo base_url() . 'images/menubar/' . $module->module_id . '.svg'; ?>" alt="<?php echo $this->lang->line('common_icon') . '&nbsp;' . $this->lang->line("module_" . $module->module_id); ?>" />
              </a>
            </li>
            <li class="d-block d-lg-none nav-item px-2 py-1 <?php echo $module->module_id == $this->uri->segment(1) ? 'active bg-light' : ''; ?>">
              <a class="nav-link p-0 <?php echo $module->module_id == $this->uri->segment(1) ? 'text-body' : ''; ?>" href="<?php echo site_url("$module->module_id"); ?>">
                <img class="pe-1 my-1" src="<?php echo base_url() . 'images/menubar/' . $module->module_id . '.svg'; ?>" alt="<?php echo $this->lang->line('common_icon') . '&nbsp;' . $this->lang->line("module_" . $module->module_id); ?>" />
                <span class="align-middle"><?php echo $this->lang->line("module_" . $module->module_id) ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </nav>

  <main class="container-lg flex-grow-1 py-3">