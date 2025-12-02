<?php

    use Config\OSPOS;

/**
 * @var string $dbVersion
 * @var array $config
 */

$logs = WRITEPATH . 'logs/';
$uploads = FCPATH . 'uploads/';
$images = FCPATH . 'uploads/item_pics/';
$importCustomers = WRITEPATH . '/uploads/importCustomers.csv';    //TODO: This variable does not follow naming conventions for the project.
$bullet = '&raquo;' . '&nbsp;';
$divider = ' &middot;' . '&nbsp;';
$enabled = '<span class="text-success">&#10003; Enabled</span>';
$disabled = '<span class="text-danger">&#10007; Disabled</span>';
$writable = '<span class="text-success">&#10003; Writable</span>';
$notwritable = '<span class="text-danger">&#10007; Not Writable</span>';
$readable = '<span class="text-success">&#10003; Readable</span>';
$notreadable = '<span class="text-danger">&#10007; Not Readable</span>';
$permissions_check = '<span class="text-success">&#10003; Security Check Passed</span>';
$permissions_fail = '<span class="text-danger">&#10007; Vulnerable or Incorrect Permissions</span>';
?>

<?php
$title_info['config_title'] = lang('Config.system_info');
echo view('configs/config_header', $title_info);
?>

<div class="mb-3"><?= lang('Config.server_notice'); ?></div>

<form id="copy-issue">

    <?php
    if (!((substr(decoct(fileperms($logs)), -4) == 750)
        && (substr(decoct(fileperms($uploads)), -4) == 750)
        && (substr(decoct(fileperms($images)), -4) == 750)
        && ((substr(decoct(fileperms($importCustomers)), -4) == 640)
        || (substr(decoct(fileperms($importCustomers)), -4) == 660)))) {
            echo '<div class="card border-danger-subtle mb-4">
            <div class="card-header bg-danger-subtle border-danger-subtle fw-bold"><i class="bi bi-exclamation-circle"></i> ' . lang('Config.security_issue') . '</div>
                <div class="card-body">
                    <p class="card-text">' . lang('Config.perm_risk') . '</p>
                    <ul class="list-unstyled mb-0">';
        }
        if (substr(decoct(fileperms($logs)), -4) != 750) {
            echo '<li class="card-text">' . $bullet . '<code>writable/logs</code> ' . lang('Config.is_writable') . '</li>';
        }

        if (substr(decoct(fileperms($uploads)), -4) != 750) {
            echo '<li class="card-text">' . $bullet . '<code>public/uploads</code> ' . lang('Config.is_writable') . '</li>';
        }

        if (substr(decoct(fileperms($images)), -4) != 750) {
            echo '<li class="card-text">' . $bullet . '<code>public/uploads/item_pics</code> ' . lang('Config.is_writable') . '</li>';
        }

        if (!((substr(decoct(fileperms($importCustomers)), -4) == 640)
            || (substr(decoct(fileperms($importCustomers)), -4) == 660))) {
            echo '<li class="card-text">' . $bullet . '<code>importCustomers.csv</code> ' . lang('Config.is_readable') . '</li>';
        }
        echo '</div></div>';
    ?>

    <div class="row mb-3">
        <label for="general-info" class="col-12 col-lg-2 form-label fw-bold">General Info</label>
        <div class="col-12 col-lg-10" id="general-info">
            <?= lang('Config.ospos_info') . ':&nbsp;' . esc(config('App')->application_version) . '&nbsp;-&nbsp;' . esc(substr(config(OSPOS::class)->commit_sha1, 0, 6)); ?><br>
            <div>Language Code: <?= current_language_code(); ?></div><br>
            <div id="time-error" class="row mb-3 d-none">
                <div class="col-12 text-danger"><?= lang('Config.timezone_error'); ?></div>
                <div class="col-6">
                    <label for="timezone"><?= lang('Config.user_timezone'); ?></label>
                    <div id="timezone"></div>
                </div>
                <div class="col-6">
                    <label for="ostimezone"><?= lang('Config.os_timezone'); ?></label>
                    <div id="ostimezone"><?= $config['timezone']; ?></div>
                </div>
            </div>
            <span>Extensions & Modules:</span><br>
            <ul class="list-unstyled">
                <li><?= $bullet . 'GD: ', extension_loaded('gd') ? $enabled : $disabled; ?></li>
                <li><?= $bullet . 'BC Math: ', extension_loaded('bcmath') ? $enabled : $disabled; ?></li>
                <li><?= $bullet . 'INTL: ', extension_loaded('intl') ? $enabled : $disabled; ?></li>
                <li><?= $bullet . 'OpenSSL: ', extension_loaded('openssl') ? $enabled : $disabled; ?></li>
                <li><?= $bullet . 'MBString: ', extension_loaded('mbstring') ? $enabled : $disabled; ?></li>
                <li><?= $bullet . 'Curl: ', extension_loaded('curl') ? $enabled : $disabled; ?></li>
                <li><?= $bullet . 'Xml: ', extension_loaded('xml') ? $enabled : $disabled; ?></li>
            </ul>
        </div>
    </div>

    <div class="row mb-3">
        <label for="user-setup" class="col-12 col-lg-2 form-label fw-bold">User Setup</label>
        <div class="col-12 col-lg-10" id="user-setup">
            <ul class="list-unstyled">
            <?php
                /**
                 * @param string $userAgent
                 * @return string
                 */
                function getBrowserNameAndVersion(string $userAgent): string
                {
                    $browser = match (true) {
                        strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR/') !== false => 'Opera',
                        strpos($userAgent, 'Edge') !== false => 'Edge',
                        strpos($userAgent, 'Chrome') !== false => 'Chrome',
                        strpos($userAgent, 'Safari') !== false => 'Safari',
                        strpos($userAgent, 'Firefox') !== false => 'Firefox',
                        strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident/7') !== false => 'Internet Explorer',
                        default => 'Other',
                    };

                    $version = match ($browser) {
                        'Opera' => preg_match('/(Opera|OPR)\/([0-9.]+)/', $userAgent, $matches) ? $matches[2] : '',
                        'Edge' => preg_match('/Edge\/([0-9.]+)/', $userAgent, $matches) ? $matches[1] : '',
                        'Chrome' => preg_match('/Chrome\/([0-9.]+)/', $userAgent, $matches) ? $matches[1] : '',
                        'Safari' => preg_match('/Version\/([0-9.]+)/', $userAgent, $matches) ? $matches[1] : '',
                        'Firefox' => preg_match('/Firefox\/([0-9.]+)/', $userAgent, $matches) ? $matches[1] : '',
                        'Internet Explorer' => preg_match('/(MSIE|rv:)([0-9.]+)/', $userAgent, $matches) ? $matches[2] : '',
                        default => '',
                    };

                    return $browser . ($version ? ' ' . $version : '');
                }
            ?>
                <li><?= $bullet . 'Browser: ' . esc(getBrowserNameAndVersion($_SERVER['HTTP_USER_AGENT'])); ?></li>
                <li><?= $bullet . 'Server Software: ' . esc($_SERVER['SERVER_SOFTWARE']); ?></li>
                <li><?= $bullet . 'PHP Version: ' . PHP_VERSION; ?></li>
                <li><?= $bullet . 'DB Version: ' . esc($dbVersion); ?></li>
                <li><?= $bullet . 'Server Port: ' . esc($_SERVER['SERVER_PORT']); ?></li>
                <li><?= $bullet . 'OS: ' . php_uname('s') . ' ' . php_uname('r'); ?></li>
            </ul>
        </div>
    </div>

    <div class="row mb-3">
        <label for="permissions" class="col-12 col-lg-2 form-label fw-bold">Permissions</label>
        <div class="col-12 col-lg-10" id="permissions">
            <ul class="list-unstyled">
                <li>
                    <?= $bullet; ?><code>writable/logs</code>
                    <?php
                    if (is_writable($logs)) {
                        echo substr(sprintf("%o", fileperms($logs)), -4) . $divider . $writable;
                    } else {
                        echo substr(sprintf("%o", fileperms($logs)), -4) . $divider . $notwritable;

                    }
                    clearstatcache();

                    echo $divider;

                    if (is_writable($logs) && substr(decoct(fileperms($logs)), -4) != 750) {
                        echo $permissions_fail;
                    } else {
                        echo $permissions_check;
                    }
                    clearstatcache();
                    ?>
                </li>
                <li>
                    <?= $bullet; ?><code>public/uploads</code>
                    <?php
                    if(is_writable($uploads)) {
                        echo substr(sprintf("%o",fileperms($uploads)),-4) . $divider . $writable;
                    } else {
                        echo substr(sprintf("%o",fileperms($uploads)),-4) . $divider . $notwritable;
                    }
                    clearstatcache();

                    echo $divider;

                    if(is_writable($uploads) && substr(decoct(fileperms($uploads)), -4) != 750  ) {
                        echo $permissions_fail;
                    } else {
                        echo $permissions_check;
                    }
                    clearstatcache();
                    ?>
                </li>
                <li>
                    <?= $bullet; ?><code>public/uploads/item_pics</code>
                    <?php
                    if (is_writable($images)) {
                        echo substr(sprintf("%o", fileperms($images)), -4) . $divider . $writable;
                    } else {
                        echo substr(sprintf("%o", fileperms($images)), -4) . $divider . $notwritable;
                    }
                    clearstatcache();

                    echo $divider;

                    if (substr(decoct(fileperms($images)), -4) != 750) {
                        echo $permissions_fail;
                    } else {
                        echo $permissions_check;
                    }
                    clearstatcache();

                    ?>
                </li>
                <li>
                    <?= $bullet; ?><code>importCustomers.csv</code>
                    <?php
                    if (is_readable($importCustomers)) {
                        echo substr(sprintf("%o", fileperms($importCustomers)), -4) . $divider . $readable;
                    } else {
                        echo substr(sprintf("%o", fileperms($importCustomers)), -4) . $divider . $notreadable;
                    }
                    clearstatcache();

                    echo $divider;

                    if (!((substr(decoct(fileperms($importCustomers)), -4) == 640) || (substr(decoct(fileperms($importCustomers)), -4) == 660))) {
                        echo $permissions_fail;
                    } else {
                        echo $permissions_check;
                    }
                    clearstatcache();

                    ?>
                </li>
            </ul>
            <?php
            if (((substr(decoct(fileperms($logs)), -4) == 750) && (substr(decoct(fileperms($uploads)), -4) == 750) && (substr(decoct(fileperms($images)), -4) == 750) && ((substr(decoct(fileperms($importCustomers)), -4) == 640) || (substr(decoct(fileperms($importCustomers)), -4) == 660)))) {
                echo '<span class="text-success">' . lang('Config.no_risk') . '</span>';
            }
            ?>
        </div>
    </div>
</form>

<div class="d-flex justify-content-center gap-3">
    <button class="copy btn btn-secondary" data-clipboard-target="#copy-issue"><i class="bi bi-clipboard-plus"></i> Copy Info</button> <!-- TODO-BS5 add to translations -->
    <a class="btn btn-secondary" href="https://github.com/opensourcepos/opensourcepos/issues/new" target="_blank" rel="noopener"><i class="bi bi-flag"></i> <?= lang('Config.report_an_issue') ?></a>
</div>

<script type="text/javascript">
    var clipboard = new ClipboardJS('.copy');

    clipboard.on('success', function(e) {
        document.getSelection().removeAllRanges();
        $.notify({
            icon: 'bi bi-clipboard-check-fill',
            message: 'System info successfully copied.'
        }, {
            type: 'success'
        });
    });

    clipboard.on('error', function(e) {
        $.notify({
            icon: 'bi bi-clipboard-x-fill',
            message: 'Something went wrong while copying.'
        }, {
            type: 'danger'
        });
    });

    document.getElementById("timezone").innerText = Intl.DateTimeFormat().resolvedOptions().timeZone;

    $(function() {
        $('#timezone').clone().appendTo('#timezoneE');
    });

    if ($('#timezone').html() !== $('#ostimezone').html()) {
        $('#time-error').removeClass('d-none');
    };
</script>
