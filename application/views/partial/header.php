<!doctype html>
<html lang="<?php echo current_language_code(); ?>">

<head>
	<meta charset="utf-8">
	<base href="<?php echo base_url();?>" />
	<title><?php echo $this->config->item('company') . '&nbsp;|&nbsp;' . $this->lang->line('common_powered_by') . '&nbsp;' . $this->lang->line('common_software_short') . '&nbsp;' . $this->config->item('application_version') ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
	<link rel="stylesheet" type="text/css" href="<?php echo 'dist/bootswatch-5/' . (empty($this->config->item('theme')) ? 'flatly' : $this->config->item('theme')) . '/bootstrap.min.css' ?>"/>
	<?php if ($this->input->cookie('debug') == 'true' || $this->input->get('debug') == 'true') : ?>
	<!-- bower:css -->
	<link rel="stylesheet" href="bower_components/jquery-ui/themes/base/jquery-ui.css" />
	<link rel="stylesheet" href="bower_components/bootstrap3-dialog/dist/css/bootstrap-dialog.min.css" />
	<link rel="stylesheet" href="bower_components/jasny-bootstrap/dist/css/jasny-bootstrap.css" />
	<link rel="stylesheet" href="bower_components/smalot-bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
	<link rel="stylesheet" href="bower_components/bootstrap-select/dist/css/bootstrap-select.css" />
	<link rel="stylesheet" href="bower_components/bootstrap-table/dist/bootstrap-table.css" />
	<link rel="stylesheet" href="bower_components/bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.css" />
	<link rel="stylesheet" href="bower_components/bootstrap-daterangepicker/daterangepicker.css" />
	<link rel="stylesheet" href="bower_components/chartist/dist/chartist.min.css" />
	<link rel="stylesheet" href="bower_components/chartist-plugin-tooltip/dist/chartist-plugin-tooltip.css" />
	<link rel="stylesheet" href="bower_components/bootstrap-tagsinput/dist/bootstrap-tagsinput.css" />
	<link rel="stylesheet" href="bower_components/bootstrap-toggle/css/bootstrap-toggle.min.css" />
	<!-- endbower -->
	<!-- start css template tags -->
	<link rel="stylesheet" type="text/css" href="css/bootstrap.autocomplete.css"/>
	<link rel="stylesheet" type="text/css" href="css/invoice.css"/>
	<link rel="stylesheet" type="text/css" href="css/new.css"/>
	<link rel="stylesheet" type="text/css" href="css/ospos.css"/>
	<link rel="stylesheet" type="text/css" href="css/ospos_print.css"/>
	<link rel="stylesheet" type="text/css" href="css/popupbox.css"/>
	<link rel="stylesheet" type="text/css" href="css/receipt.css"/>
	<link rel="stylesheet" type="text/css" href="css/register.css"/>
	<link rel="stylesheet" type="text/css" href="css/reports.css"/>
	<!-- end css template tags -->
	<!-- bower:js -->
	<script src="bower_components/jquery/dist/jquery.js"></script>
	<script src="bower_components/jquery-form/src/jquery.form.js"></script>
	<script src="bower_components/jquery-validate/dist/jquery.validate.js"></script>
	<script src="bower_components/jquery-ui/jquery-ui.js"></script>
	<script src="bower_components/bootstrap/dist/js/bootstrap.js"></script>
	<script src="bower_components/bootstrap3-dialog/dist/js/bootstrap-dialog.min.js"></script>
	<script src="bower_components/jasny-bootstrap/dist/js/jasny-bootstrap.js"></script>
	<script src="bower_components/smalot-bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
	<script src="bower_components/bootstrap-select/dist/js/bootstrap-select.js"></script>
	<script src="bower_components/bootstrap-table/dist/bootstrap-table.min.js"></script>
	<script src="bower_components/bootstrap-table/dist/extensions/export/bootstrap-table-export.min.js"></script>
	<script src="bower_components/bootstrap-table/dist/extensions/mobile/bootstrap-table-mobile.min.js"></script>
	<script src="bower_components/bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.min.js"></script>
	<script src="bower_components/moment/moment.js"></script>
	<script src="bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
	<script src="bower_components/es6-promise/es6-promise.js"></script>
	<script src="bower_components/file-saver/dist/FileSaver.min.js"></script>
	<script src="bower_components/html2canvas/build/html2canvas.js"></script>
	<script src="bower_components/jspdf/dist/jspdf.debug.js"></script>
	<script src="bower_components/jspdf-autotable/dist/jspdf.plugin.autotable.js"></script>
	<script src="bower_components/tableExport.jquery.plugin/tableExport.js"></script>
	<script src="bower_components/chartist/dist/chartist.min.js"></script>
	<script src="bower_components/chartist-plugin-pointlabels/dist/chartist-plugin-pointlabels.min.js"></script>
	<script src="bower_components/chartist-plugin-tooltip/dist/chartist-plugin-tooltip.min.js"></script>
	<script src="bower_components/chartist-plugin-barlabels/dist/chartist-plugin-barlabels.min.js"></script>
	<script src="bower_components/remarkable-bootstrap-notify/bootstrap-notify.js"></script>
	<script src="bower_components/js-cookie/src/js.cookie.js"></script>
	<script src="bower_components/bootstrap-tagsinput/dist/bootstrap-tagsinput.js"></script>
	<script src="bower_components/bootstrap-toggle/js/bootstrap-toggle.min.js"></script>
	<!-- endbower -->
	<!-- start js template tags -->
	<script type="text/javascript" src="dist/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/bs-modal_switch_content.js"></script>
	<script type="text/javascript" src="js/bs-tooltips.js"></script>
	<script type="text/javascript" src="js/clipboard.min.js"></script>
	<script type="text/javascript" src="js/imgpreview.full.jquery.js"></script>
	<script type="text/javascript" src="js/manage_tables.js"></script>
	<script type="text/javascript" src="js/nominatim.autocomplete.js"></script>
	<script type="text/javascript" src="js/ospos-change_password.js"></script>
	<!-- end js template tags -->
	<?php else : ?>
	<!--[if lte IE 8]>
	<link rel="stylesheet" media="print" href="dist/print.css" type="text/css" />
	<![endif]-->
	<!-- start mincss template tags -->
	<link rel="stylesheet" type="text/css" href="dist/jquery-ui/jquery-ui.min.css"/>
	<link rel="stylesheet" type="text/css" href="dist/opensourcepos.min.css?rel=923c3f4eef"/>
	<!-- end mincss template tags -->

	<!-- Tweaks to the UI for a particular theme should drop here  -->
	<?php if ($this->config->item('theme') != 'flatly' && file_exists($_SERVER['DOCUMENT_ROOT'] . '/public/css/' . $this->config->item('theme') . '.css')) { ?>
	<link rel="stylesheet" type="text/css" href="<?php echo 'css/' . $this->config->item('theme') . '.css' ?>"/>
	<?php } ?>

	<!-- start minjs template tags -->
	<script type="text/javascript" src="dist/opensourcepos.min.js?rel=68d2de7fa7"></script>
	<!-- end minjs template tags -->
	<?php endif; ?>

	<?php $this->load->view('partial/header_js'); ?>
	<?php $this->load->view('partial/lang_lines'); ?>

	<style type="text/css">
		html {
			overflow: auto;
		}
	</style>
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
          <?php foreach($allowed_modules as $module): ?>
            <li class="d-none d-lg-block nav-item mt-1 ms-1 <?php echo $module->module_id == $this->uri->segment(1) ? 'active bg-body border border-2 border-bottom-0 border-secondary rounded-top' : ''; ?>" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php echo $this->lang->line("module_" . $module->module_id); ?>">
              <a class="nav-link p-2 mb-1 <?php echo $module->module_id == $this->uri->segment(1) ? 'text-body' : ''; ?>" href="<?php echo site_url("$module->module_id"); ?>">
                <img src="<?php echo base_url() . 'images/menubar/' . $module->module_id . '.svg'; ?>" alt="<?php echo $this->lang->line('common_icon') . '&nbsp;' . $this->lang->line("module_" . $module->module_id); ?>"/>
              </a>
            </li>
            <li class="d-block d-lg-none nav-item px-2 py-1 <?php echo $module->module_id == $this->uri->segment(1) ? 'active bg-light' : ''; ?>">
              <a class="nav-link p-0 <?php echo $module->module_id == $this->uri->segment(1) ? 'text-body' : ''; ?>" href="<?php echo site_url("$module->module_id"); ?>">
                <img class="pe-1 my-1" src="<?php echo base_url() . 'images/menubar/' . $module->module_id . '.svg'; ?>" alt="<?php echo $this->lang->line('common_icon') . '&nbsp;' . $this->lang->line("module_" . $module->module_id); ?>"/>
                <span class="align-middle"><?php echo $this->lang->line("module_" . $module->module_id) ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </nav>

  <main class="container-lg flex-grow-1 py-3">