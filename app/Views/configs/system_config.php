<div class="container-fluid" style="padding-inline: 0;">
    <ul class="nav nav-tabs" id="myTabs">
        <li class="active"><a href="#system_tabs" data-toggle="tab" title="<?= lang('Config.system_info') ?>"><?= lang('Config.system_info') ?></a></li>
        <li><a href="#email_tabs" data-toggle="tab" title="<?= lang('Config.email_configuration') ?>"><?= lang('Config.email') ?></a></li>
        <li><a href="#message_tabs" data-toggle="tab" title="<?= lang('Config.message_configuration') ?>"><?= lang('Config.message') ?></a></li>
        <li><a href="#integrations_tabs" data-toggle="tab" title="<?= lang('Config.integrations_configuration') ?>"><?= lang('Config.integrations') ?></a></li>
        <li><a href="#license_tabs" data-toggle="tab" title="<?= lang('Config.license_configuration') ?>"><?= lang('Config.license') ?></a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="system_tabs"><?= view('configs/system_info') ?></div>
        <div class="tab-pane" id="email_tabs"><?= view('configs/email_config') ?></div>
        <div class="tab-pane" id="message_tabs"><?= view('configs/message_config') ?></div>
        <div class="tab-pane" id="integrations_tabs"><?= view('configs/integrations_config') ?></div>
        <div class="tab-pane" id="license_tabs"><br><?= view('configs/license_config') ?></div>
    </div>
</div>
