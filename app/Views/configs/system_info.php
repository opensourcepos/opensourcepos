<?php
/**
 * @var string $dbVersion
 * @var array $config
 */

use Config\OSPOS;

?>

<script type="text/javascript" src="js/clipboard.min.js"></script>

<form id="config_wrapper" class="form-horizontal">
    <p><?= lang('Config.server_notice') ?></p>

    <div id="issuetemplate">
        <div class="form-group form-group-sm">
            <label class="control-label col-xs-3 col-sm-2">General Info</label>
            <div class="text-left col-xs-9 col-sm-10">
                <?= lang('Config.ospos_info') . ':' ?>
                <?= esc(config('App')->application_version) ?> - <?= esc(substr(config(OSPOS::class)->commit_sha1, 0, 6)) ?><br>
                Language Code: <?= current_language_code() ?><br><br>
                <div id="timeerror"></div>
                Extensions & Modules:<br>
                <?php
                    echo "&#187; GD: ", extension_loaded('gd') ? '<span class="text-success">&#x2713 Enabled</span>' : '<span class="text-danger">&#x2717 Disabled</span>', '<br>';
                    echo "&#187; BC Math: ", extension_loaded('bcmath') ? '<span class="text-success">&#x2713 Enabled</span>' : '<span class="text-danger">&#x2717 Disabled</span>', '<br>';
                    echo "&#187; intl: ", extension_loaded('intl') ? '<span class="text-success">&#x2713 Enabled</span>' : '<span class="text-danger">&#x2717 Disabled</span>', '<br>';
                    echo "&#187; OpenSSL: ", extension_loaded('openssl') ? '<span class="text-success">&#x2713 Enabled</span>' : '<span class="text-danger">&#x2717 Disabled</span>', '<br>';
                    echo "&#187; Multibyte String: ", extension_loaded('mbstring') ? '<span class="text-success">&#x2713 Enabled</span>' : '<span class="text-danger">&#x2717 Disabled</span>', '<br>';
                    echo "&#187; cURL: ", extension_loaded('curl') ? '<span class="text-success">&#x2713 Enabled</span>' : '<span class="text-danger">&#x2717 Disabled</span>', '<br>';
                    echo "&#187; JSON: ", extension_loaded('json') ? '<span class="text-success">&#x2713 Enabled</span>' : '<span class="text-danger">&#x2717 Disabled</span>', '<br>';
                    echo "&#187; XML: ", extension_loaded('xml') ? '<span class="text-success">&#x2713 Enabled</span>' : '<span class="text-danger">&#x2717 Disabled</span>', '<br><br>';
                ?>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <label class="control-label col-xs-3 col-sm-2">User Setup</label>
            <div class="text-left col-xs-9 col-sm-10">
                Browser:
                <?php
                /**
                 * @param string $userAgent
                 * @return string
                 */
                function getBrowserNameAndVersion(string $userAgent): string
                {
                    $browser = match (true) {
                        strpos($userAgent, 'Opera')   !== false || strpos($userAgent, 'OPR/') !== false => 'Opera',
                        strpos($userAgent, 'Edge')    !== false => 'Edge',
                        strpos($userAgent, 'Chrome')  !== false => 'Chrome',
                        strpos($userAgent, 'Safari')  !== false => 'Safari',
                        strpos($userAgent, 'Firefox') !== false => 'Firefox',
                        strpos($userAgent, 'MSIE')    !== false || strpos($userAgent, 'Trident/7') !== false => 'Internet Explorer',
                        default                       => 'Other',
                    };

                    $version = match ($browser) {
                        'Opera'             => preg_match('/(Opera|OPR)\/([0-9.]+)/', $userAgent, $matches) ? $matches[2] : '',
                        'Edge'              => preg_match('/Edge\/([0-9.]+)/', $userAgent, $matches) ? $matches[1] : '',
                        'Chrome'            => preg_match('/Chrome\/([0-9.]+)/', $userAgent, $matches) ? $matches[1] : '',
                        'Safari'            => preg_match('/Version\/([0-9.]+)/', $userAgent, $matches) ? $matches[1] : '',
                        'Firefox'           => preg_match('/Firefox\/([0-9.]+)/', $userAgent, $matches) ? $matches[1] : '',
                        'Internet Explorer' => preg_match('/(MSIE|rv:)([0-9.]+)/', $userAgent, $matches) ? $matches[2] : '',
                        default             => '',
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
            </div>
        </div>

        <div class="form-group form-group-sm">
            <label class="control-label col-xs-3 col-sm-2">Permissions</label>
            <div class="text-left col-xs-9 col-sm-10">
                &#187; <code>[writable/logs]</code>
                <?php $logs = WRITEPATH . 'logs/';
                $uploads = FCPATH. 'uploads/';
                $images = FCPATH. 'uploads/item_pics/';
                $importCustomers = WRITEPATH . '/uploads/importCustomers.csv';    // TODO: This variable does not follow naming conventions for the project.

                if (is_writable($logs)) {
                    echo substr(sprintf("%o", fileperms($logs)), -4) . ' · ' . '<span class="text-success">&#x2713 Writable</span>';
                } else {
                    echo substr(sprintf("%o", fileperms($logs)), -4) . ' · ' . '<span class="text-danger">&#x2717 Not Writable</span>';
                }

                clearstatcache();
                if (is_writable($logs) && substr(decoct(fileperms($logs)), -4) != 750) {
                    echo ' · <span class="text-danger">&#x2717 Vulnerable or Incorrect Permissions</span>';
                } else {
                    echo ' · <span class="text-success">&#x2713 Security Check Passed</span>';
                }
                clearstatcache();
                ?>
                <br>
                &#187; <code>[public/uploads]</code>
                <?php
                if (is_writable($uploads)) {
                    echo substr(sprintf("%o", fileperms($uploads)), -4) . ' · ' . '<span class="text-success">&#x2713 Writable</span>';
                } else {
                    echo substr(sprintf("%o", fileperms($uploads)), -4) . ' · ' . '<span class="text-danger">&#x2717 Not Writable</span>';
                }

                clearstatcache();

                if (is_writable($uploads) && substr(decoct(fileperms($uploads)), -4) != 750) {
                    echo ' · <span class="text-danger">&#x2717 Vulnerable or Incorrect Permissions</span>';
                } else {
                    echo ' · <span class="text-success">&#x2713 Security Check Passed</span>';
                }

                clearstatcache();
                ?>
                <br>
                &#187; <code>[public/uploads/item_pics]</code>
                <?php
                if (is_writable($images)) {
                    echo substr(sprintf("%o", fileperms($images)), -4) . ' · ' . '<span class="text-success">&#x2713 Writable</span>';
                } else {
                    echo substr(sprintf("%o", fileperms($images)), -4) . ' · ' . '<span class="text-danger">&#x2717 Not Writable</span>';
                }

                clearstatcache();

                if (substr(decoct(fileperms($images)), -4) != 750) {
                    echo ' · <span class="text-danger">&#x2717 Vulnerable or Incorrect Permissions</span>';
                } else {
                    echo ' · <span class="text-success">&#x2713 Security Check Passed</span>';
                }

                clearstatcache();
                ?>
                <br>
                &#187; <code>[importCustomers.csv]</code>
                <?php
                if (is_readable($importCustomers)) {
                    echo substr(sprintf("%o", fileperms($importCustomers)), -4) . ' · ' . '<span class="text-success">&#x2713 Readable</span>';
                } else {
                    echo substr(sprintf("%o", fileperms($importCustomers)), -4) . ' · ' . '<span class="text-danger">&#x2717 Not Readable</span>';
                }
                clearstatcache();

                if (!((substr(decoct(fileperms($importCustomers)), -4) == 640) || (substr(decoct(fileperms($importCustomers)), -4) == 660))) {
                    echo ' · <span class="text-danger">&#x2717 Vulnerable or Incorrect Permissions</span>';
                } else {
                    echo ' · <span class="text-success">&#x2713 Security Check Passed</span>';
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
                    echo '<br><span class="text-danger"><strong>' . lang('Config.security_issue') . '</strong><br>' . lang('Config.perm_risk') . '</span><br>';
                } else {
                    echo '<br><span class="text-success"><strong>' . lang('Config.no_risk') . '</strong></span><br>';
                }

                if (substr(decoct(fileperms($logs)), -4) != 750) {
                    echo '<br><span class="text-danger">&#187; <code>[writable/logs]</code> ' . lang('Config.is_writable') . '</span>';
                }

                if (substr(decoct(fileperms($uploads)), -4) != 750) {
                    echo '<br><span class="text-danger">&#187; <code>[public/uploads]</code> ' . lang('Config.is_writable') . '</span>';
                }

                if (substr(decoct(fileperms($images)), -4) != 750) {
                    echo '<br><span class="text-danger">&#187; <code>[public/uploads/item_pics]</code> ' . lang('Config.is_writable') . '</span>';
                }

                if (!((substr(decoct(fileperms($importCustomers)), -4) == 640)
                    || (substr(decoct(fileperms($importCustomers)), -4) == 660))) {
                    echo '<br><span class="text-danger">&#187; <code>[importCustomers.csv]</code> ' . lang('Config.is_readable') . '</span>';
                }
                ?>
            </div>
        </div>
    </div>

</form>

<div class="row">
    <div class="col-xs-12 text-center">
        <br>
        <a class="copy" role="button" data-clipboard-action="copy" data-clipboard-target="#issuetemplate">Copy Info</a> | <a href="https://github.com/opensourcepos/opensourcepos/issues/new?template=bug+report.yml" target="_blank"> <?= lang('Config.report_an_issue') ?></a>
        <script type="text/javascript">
            const clipboard = new ClipboardJS('.copy');

            clipboard.on('success', function(e) {
                document.getSelection().removeAllRanges();
            });

            const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            const osTimezone = '<?= esc(!empty($config['timezone']) ? $config['timezone'] : date_default_timezone_get()) ?>';

            if (userTimezone !== osTimezone) {
                document.getElementById("timeerror").innerHTML = '<div><span class="text-danger"><?= lang('Config.timezone_error') ?></span><br><?= lang('Config.user_timezone') ?> <strong>' + userTimezone + '</strong><br><?= lang('Config.os_timezone') ?> <strong>' + osTimezone + '</strong></div><br>';
            }
        </script>
    </div>
</div>
