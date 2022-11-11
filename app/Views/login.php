<!doctype html>
<html lang="<?php echo current_language_code(); ?>">

<head>
  <meta charset="utf-8">
	<base href="<?php echo base_url(); ?>">
	<title><?php echo $this->config->item('company') . '&nbsp;|&nbsp;' . $this->lang->line('common_software_short')  . '&nbsp;|&nbsp;' .  $this->lang->line('login_login'); ?></title>
	<meta content="width=device-width, initial-scale=1" name="viewport">
  <meta content="noindex, nofollow" name="robots">
	<link href="images/favicon.ico" rel="shortcut icon" type="image/x-icon">
  <link href="<?php echo 'dist/bootswatch-5/' . (empty($this->config->item('theme')) || 'paper' == $this->config->item('theme') || 'readable' == $this->config->item('theme') ? 'flatly' : $this->config->item('theme')) . '/bootstrap.min.css'; ?>" rel="stylesheet" type="text/css">
  <!-- start css template tags -->
  <link rel="stylesheet" type="text/css" href="css/login.min.css"/>
  <!-- end css template tags -->
	<meta content="#2c3e50" name="theme-color">
</head>

<body class="bg-light d-flex flex-column">
  <main class="d-flex justify-content-around align-items-center flex-grow-1">
    <div class="container-login container-fluid d-flex flex-column flex-md-row bg-body shadow rounded m-3 p-4 p-md-0">
      <div class="box-logo d-flex flex-column justify-content-center align-items-center border-end px-4 pb-3 p-md-4">
      <?php if ($this->Appconfig->get('company_logo')): ?>
        <img class="logo w-100" src="<?php echo base_url('uploads/' . $this->Appconfig->get('company_logo')); ?>" alt="<?php echo $this->lang->line('common_logo') . '&nbsp;' . $this->config->item('company'); ?>">
      <?php else: ?>
        <svg class="logo text-primary" role="img" viewBox="0 0 308.57998 308.57997" xmlns="http://www.w3.org/2000/svg">
          <title><?php echo $this->lang->line('common_software_title') . '&nbsp;' . $this->lang->line('common_logo'); ?></title>
          <circle cx="154.28999" cy="154.28999" r="154.28999" fill="currentColor"/>
          <path fill="#fff" d="M154.88998 145.66999c-.03-1.26-.03-3.29.19-4.29 4.6-11.1 15.57-18.82 28.3-18.82h.41v58.3c0 .12-.03.78-.04.9-.54 16.46-14.01 29.7-30.59 29.7v27.08c21 0 39.17-11.27 49.29-28.07l.07-.11c2.9.45 5.86.75 8.9.75 31.95 0 57.81-26 57.81-57.81 0-30.87-24.37-56.46-55.1-57.81h-30.74c-17.18 0-32.61 7.64-43.22 19.63-10.59-11.92-25.86-19.59-43.02-19.59-31.86 0-57.77 25.91-57.77 57.77 0 31.86 25.91 57.77 57.77 57.77 31.86 0 57.77-25.91 57.77-57.77v-3.68c-.01.01-.02-3.31-.03-3.95zm-57.75 38.33c-16.92 0-30.69-13.77-30.69-30.69s13.77-30.69 30.69-30.69 30.69 13.77 30.69 30.69-13.77 30.69-30.69 30.69zm142.96-19.87c-4.33 11.64-15.57 19.9-28.7 19.9h-.54v-61.47h.54c13.13 0 24.37 8.26 28.7 19.9 1.35 3.25 2.03 6.91 2.03 10.83s-.67 7.59-2.03 10.84z"/>
        </svg>
      <?php endif; ?>
      </div>
      <section class="box-login d-flex flex-column justify-content-center align-items-center p-md-4">
				<?php echo form_open('login'); ?>
        <h3 class="text-center m-0"><?php echo $this->lang->line('login_welcome', $this->lang->line('common_software_short')); ?></h3>
        <?php if (validation_errors()): ?>
        <div class="alert alert-danger mt-3">
          <?php echo validation_errors(); ?>
        </div>
        <?php endif; ?>
				<?php if (!$this->migration->is_latest()): ?>
        <div class="alert alert-info mt-3">
					<?php echo $this->lang->line('login_migration_needed', $this->config->item('application_version')); ?>
				</div>
				<?php endif; ?>
        <?php if (empty($this->config->item('login_form')) || 'floating_labels'==($this->config->item('login_form'))): ?>
        <div class="form-floating mt-3">
          <input class="form-control" id="input-username" name="username" type="text" placeholder="<?php echo $this->lang->line('login_username'); ?>">
          <label for="input-username"><?php echo $this->lang->line('login_username'); ?></label>
        </div>
        <div class="form-floating mb-3">
          <input class="form-control" id="input-password" name="password" type="password" placeholder="<?php echo $this->lang->line('login_password'); ?>">
          <label for="input-password"><?php echo $this->lang->line('login_password'); ?></label>
        </div>
      <?php elseif ('input_groups'==($this->config->item('login_form'))): ?>
        <div class="input-group mt-3">
          <span class="input-group-text" id="input-username">
            <svg class="bi" fill="currentColor" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
              <title><?php echo $this->lang->line('common_icon') . '&nbsp;' . $this->lang->line('login_username'); ?></title>
              <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
            </svg>
          </span>
          <input class="form-control" name="username" type="text" placeholder="<?php echo $this->lang->line('login_username'); ?>" aria-label="<?php echo $this->lang->line('login_username'); ?>" aria-describedby="input-username" <?php if (ENVIRONMENT == "testing") echo "value='admin'"; ?>>
        </div>
        <div class="input-group mb-3">
          <span class="input-group-text" id="input-password">
            <svg class="bi" fill="currentColor" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
              <title><?php echo $this->lang->line('common_icon') . '&nbsp;' . $this->lang->line('login_password'); ?></title>
              <path d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2zM2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
            </svg>
          </span>
          <input class="form-control" name="password" type="password" placeholder="<?php echo $this->lang->line('login_password'); ?>" aria-label="<?php echo $this->lang->line('login_password'); ?>" <?php if (ENVIRONMENT == "testing") echo "value='pointofsale'"; ?>" aria-describedby="input-password">
        </div>
        <?php endif; ?>
				<?php if($this->config->item('gcaptcha_enable')) {
					echo '<script src="https://www.google.com/recaptcha/api.js"></script>';
					echo '<div class="g-recaptcha mb-3" align="center" data-sitekey="' . $this->config->item('gcaptcha_site_key') . '"></div>'; }
        ?>
        <div class="d-grid">
          <button class="btn btn-lg btn-primary" name="login-button" type="submit" ><?php echo $this->lang->line('login_go'); ?></button>
        </div>
				<?php echo form_close(); ?>
      </section>
    </div>
  </main>
  <footer class="d-flex justify-content-center flex-shrink-0 text-center">
    <div class="footer container-fluid bg-body rounded shadow p-3 mb-md-4 mx-md-3">
      <span class="text-muted">
        <svg height="1em" role="img" viewBox="0 0 229.85 143.05001" xmlns="http://www.w3.org/2000/svg">
          <title><?php echo $this->lang->line('common_software_short') . '&nbsp;' . $this->lang->line('common_logo_mark'); ?></title>
          <path fill="currentColor" d="M115.51 50.18c-.03-1.26-.03-3.29.19-4.29 4.6-11.1 15.57-18.82 28.3-18.82h.41v58.3c0 .12-.03.78-.04.9-.54 16.46-14.01 29.7-30.59 29.7v27.08c21 0 39.17-11.27 49.29-28.07l.07-.11c2.9.45 5.86.75 8.9.75 31.95 0 57.81-26 57.81-57.81 0-30.87-24.37-56.46-55.1-57.81h-30.74c-17.18 0-32.61 7.64-43.22 19.63C90.2 7.71 74.93.04 57.77.04 25.91.04 0 25.95 0 57.81c0 31.86 25.91 57.77 57.77 57.77 31.86 0 57.77-25.91 57.77-57.77v-3.68c-.01.01-.02-3.31-.03-3.95zM57.76 88.51c-16.92 0-30.69-13.77-30.69-30.69s13.77-30.69 30.69-30.69S88.45 40.9 88.45 57.82 74.68 88.51 57.76 88.51zm142.96-19.87c-4.33 11.64-15.57 19.9-28.7 19.9h-.54V27.07h.54c13.13 0 24.37 8.26 28.7 19.9 1.35 3.25 2.03 6.91 2.03 10.83s-.67 7.59-2.03 10.84z"/>
        </svg>
      </span>
      <span><?php echo $this->lang->line('common_software_title'); ?></span>
    </div>
  </footer>
</body>

</html>
