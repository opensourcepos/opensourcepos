<?php
/**
 * @var string $dbVersion
 * @var array $config
 */

use Config\OSPOS;

?>

<style>
    a:hover {
        cursor: pointer;
    }

    hidden {
        visibility: hidden;
    }
</style>

<script type="text/javascript" src="js/clipboard.min.js"></script>

<div id="config_wrapper" class="col-sm-12">
    <?= lang('Config.server_notice') ?>
    <div class="container">
        <div class="row">
            <div class="col-sm-2" style="text-align: left;"><br>
                <strong>
                    <p style="min-height: 14.7em;">General Info</p>
                    <p style="min-height: 10.5em;">User Setup</p><br>
                    <p>Permissions</p>
                </strong>
            </div>
            <div class="col-sm-8" id="issuetemplate" style="text-align: left;"><br>
                <?= lang('Config.ospos_info') . ':' ?>
                <?= esc(config('App')->application_version) ?> - <?= esc(substr(config(OSPOS::class)->commit_sha1, 0, 6)) ?><br>
                Language Code: <?= current_language_code() ?><br><br>
                <div id="TimeError"></div>
                Extensions & Modules:<br>
                <?php
                echo "&#187; GD: ", extension_loaded('gd') ? '<span style="color: green;">Enabled &#x2713</span>' : '<span style="color: red;">Disabled &#x2717</span>', '<br>';
                echo "&#187; BC Math: ", extension_loaded('bcmath') ? '<span style="color: green;">Enabled &#x2713</span>' : '<span style="color: red">Disabled &#x2717</span>', '<br>';
                echo "&#187; INTL: ", extension_loaded('intl') ? '<span style="color: green;">Enabled &#x2713</span>' : '<span style="color: red">Disabled &#x2717</span>', '<br>';
                echo "&#187; OpenSSL: ", extension_loaded('openssl') ? '<span style="color: green;">Enabled &#x2713</span>' : '<span style="color: red">Disabled &#x2717</span>', '<br>';
                echo "&#187; MBString: ", extension_loaded('mbstring') ? '<span style="color: green;">Enabled &#x2713</span>' : '<span style="color: red">Disabled &#x2717</span>', '<br>';
                echo "&#187; Curl: ", extension_loaded('curl') ? '<span style="color: green;">Enabled &#x2713</span>' : '<span style="color: red">Disabled &#x2717</span>', '<br>';
                echo "&#187; Json: ", extension_loaded('json') ? '<span style="color: green;">Enabled &#x2713</span>' : '<span style="color: red">Disabled &#x2717</span>', '<br><br>';
                echo "&#187; Xml: ", extension_loaded('xml') ? '<span style="color: green;">Enabled &#x2713</span>' : '<span style="color: red">Disabled &#x2717</span>', '<br><br>';
                ?>
                User Configuration:<br>
                .Browser:
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
                echo esc(getBrowserNameAndVersion($_SERVER['HTTP_USER_AGENT']));
                ?><br>
                Server Software: <?= esc($_SERVER['SERVER_SOFTWARE']) ?><br>
                PHP Version: <?= PHP_VERSION ?><br>
                DB Version: <?= esc($dbVersion) ?><br>
                Server Port: <?= esc($_SERVER['SERVER_PORT']) ?><br>
                OS: <?= php_uname('s') . ' ' . php_uname('r') ?><br><br>
                <br><br>

                File Permissions:<br>
                &#187; [writeable/logs:]
                <?php $logs = WRITEPATH . 'logs/';
                $uploads = FCPATH . 'uploads/';
                $images = FCPATH . 'uploads/item_pics/';
                $importCustomers = WRITEPATH . '/uploads/importCustomers.csv';    // TODO: This variable does not follow naming conventions for the project.

                if (is_writable($logs)) {
                    echo ' -  ' . substr(sprintf("%o", fileperms($logs)), -4) . ' |  ' . '<span style="color: green;">  Writable &#x2713 </span>';
                } else {
                    echo ' -  ' . substr(sprintf("%o", fileperms($logs)), -4) . ' |  ' . '<span style="color: red;">    Not Writable &#x2717 </span>';
                }

                clearstatcache();
                if (is_writable($logs) && substr(decoct(fileperms($logs)), -4) != 750) {
                    echo ' | <span style="color: red;">Vulnerable or Incorrect Permissions &#x2717</span>';
                } else {
                    echo ' | <span style="color: green;">Security Check Passed &#x2713</span>';
                }
                clearstatcache();
                ?>
                <br>
                &#187; [writable/uploads:]
                <?php
                if (is_writable($uploads)) {
                    echo ' -  ' . substr(sprintf("%o", fileperms($uploads)), -4) . ' |  ' . '<span style="color: green;">     Writable &#x2713 </span>';
                } else {
                    echo ' -  ' . substr(sprintf("%o", fileperms($uploads)), -4) . ' |  ' . '<span style="color: red;"> Not Writable &#x2717 </span>';
                }

                clearstatcache();

                if (is_writable($uploads) && substr(decoct(fileperms($uploads)), -4) != 750) {
                    echo ' | <span style="color: red;">Vulnerable or Incorrect Permissions &#x2717</span>';
                } else {
                    echo ' |  <span style="color: green;">Security Check Passed &#x2713 </span>';
                }

                clearstatcache();
                ?>
                <br>
                &#187; [writable/uploads/item_pics:]
                <?php
                if (is_writable($images)) {
                    echo ' -  ' . substr(sprintf("%o", fileperms($images)), -4) . ' |     ' . '<span style="color: green;"> Writable &#x2713 </span>';
                } else {
                    echo ' -  ' . substr(sprintf("%o", fileperms($images)), -4) . ' |     ' . '<span style="color: red;"> Not Writable &#x2717 </span>';
                }

                clearstatcache();

                if (substr(decoct(fileperms($images)), -4) != 750) {
                    echo ' | <span style="color: red;">Vulnerable or Incorrect Permissions &#x2717</span>';
                } else {
                    echo ' | <span style="color: green;">Security Check Passed &#x2713 </span>';
                }

                clearstatcache();
                ?>
                <br>
                &#187; [importCustomers.csv:]
                <?php
                if (is_readable($importCustomers)) {
                    echo ' -  ' . substr(sprintf("%o", fileperms($importCustomers)), -4) . ' |  ' . '<span style="color: green;">     Readable &#x2713 </span>';
                } else {
                    echo ' -  ' . substr(sprintf("%o", fileperms($importCustomers)), -4) . ' |  ' . '<span style="color: red;"> Not Readable &#x2717 </span>';
                }
                clearstatcache();

                if (!((substr(decoct(fileperms($importCustomers)), -4) == 640) || (substr(decoct(fileperms($importCustomers)), -4) == 660))) {
                    echo ' | <span style="color: red;">Vulnerable or Incorrect Permissions &#x2717</span>';
                } else {
                    echo ' | <span style="color: green;">Security Check Passed &#x2713 </span>';
                }
                clearstatcache();
                ?>
                <br>
                <?php
                if (!((substr(decoct(fileperms($logs)), -4) == 750)
                    && (substr(decoct(fileperms($uploads)), -4) == 750)
                    && (substr(decoct(fileperms($images)), -4) == 750)
                    && ((substr(decoct(fileperms($importCustomers)), -4) == 640)
                        || (substr(decoct(fileperms($importCustomers)), -4) == 660)))) {
                    echo '<br><span style="color: red;"><strong>' . lang('Config.security_issue') . '</strong> <br>' . lang('Config.perm_risk') . '</span><br>';
                } else {
                    echo '<br><span style="color: green;">' . lang('Config.no_risk') . '</strong> <br> </span>';
                }

                if (substr(decoct(fileperms($logs)), -4) != 750) {
                    echo '<br><span style="color: red;"> &#187; [writeable/logs:] ' . lang('Config.is_writable') . '</span>';
                }

                if (substr(decoct(fileperms($uploads)), -4) != 750) {
                    echo '<br><span style="color: red;"> &#187; [writable/uploads:] ' . lang('Config.is_writable') . '</span>';
                }

                if (substr(decoct(fileperms($images)), -4) != 750) {
                    echo '<br><span style="color: red;"> &#187; [writable/uploads/item_pics:] ' . lang('Config.is_writable') . '</span>';
                }

                if (!((substr(decoct(fileperms($importCustomers)), -4) == 640)
                    || (substr(decoct(fileperms($importCustomers)), -4) == 660))) {
                    echo '<br><span style="color: red;"> &#187; [importCustomers.csv:] ' . lang('Config.is_readable') . '</span>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<div style="text-align: center;">
    <a class="copy" data-clipboard-action="copy" data-clipboard-target="#issuetemplate">Copy Info</a> | <a href="https://github.com/opensourcepos/opensourcepos/issues/new" target="_blank"> <?= lang('Config.report_an_issue') ?></a>
    <script type="text/javascript">
        var clipboard = new ClipboardJS('.copy');

        clipboard.on('success', function(e) {
            document.getSelection().removeAllRanges();
        });

        $(function() {
            $('#timezone').clone().appendTo('#timezoneE');
        });

        if ($('#timezone').html() !== $('#ostimezone').html()) {
            document.getElementById("timezone").innerText = Intl.DateTimeFormat().resolvedOptions().timeZone;
            document.getElementById("TimeError").innerHTML = '<span style="color: red;"><?= lang('Config.timezone_error') ?></span><br><br><?= lang('Config.user_timezone') ?><div id="timezoneE" style="font-weight:600;"></div><br><?= lang('Config.os_timezone') ?><div id="ostimezoneE" style="font-weight:600;"><?= esc($config['timezone']) ?></div><br>';
        }
    </script>
</div>
