<br>
<div class="container-fluid">
    <ul class="nav nav-tabs" id="myTabs" data-toggle="tab">
        <li class="active"><a href="#system_tabs" data-toggle="tab" title="<?= lang('Config.system_conf') ?>"><?= lang('Config.system_conf') ?></a></li>
        <li><a href="#email_tabs" data-toggle="tab" title="<?= lang('Config.email_configuration') ?>"><?= lang('Config.email') ?></a></li>
        <li><a href="#message_tabs" data-toggle="tab" title="<?= lang('Config.message_configuration') ?>"><?= lang('Config.message') ?></a></li>
        <li><a href="#plugins_tabs" data-toggle="tab" title="<?= lang('Config.plugins_configuration') ?>"><?= lang('Config.plugins') ?></a></li>
        <li><a href="#license_tabs" data-toggle="tab" title="<?= lang('Config.license_configuration') ?>"><?= lang('Config.license') ?></a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="system_tabs"><?= view('configs/system_info') ?></div>
        <div class="tab-pane" id="email_tabs"><?= view('configs/email_config') ?></div>
        <div class="tab-pane" id="message_tabs"><?= view('configs/message_config') ?></div>
        <div class="tab-pane" id="plugins_tabs"><?= view('configs/plugins_config') ?></div>
        <div class="tab-pane" id="license_tabs"><br><?= view('configs/license_config') ?></div>
    </div>
</div>
