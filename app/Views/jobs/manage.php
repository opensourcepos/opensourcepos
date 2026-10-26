<?= view('partial/header') ?>

<ul class="nav nav-tabs" data-tabs="tabs">
    <li class="active" role="presentation">
        <a data-toggle="tab" href="#settings_tab" title="<?= lang('Jobs.settings_configuration') ?>"><?= lang('Jobs.settings') ?></a>
    </li>
    <li role="presentation">
        <a data-toggle="tab" href="#utilities_tab" title="<?= lang('Jobs.utilities_configuration') ?>"><?= lang('Jobs.utilities') ?></a>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade in active" id="settings_tab">
        <?= view('jobs/settings_config') ?>
    </div>
    <div class="tab-pane" id="utilities_tab">
        <?= view('jobs/utilities_config') ?>
    </div>
</div>

<?= view('partial/footer') ?>
