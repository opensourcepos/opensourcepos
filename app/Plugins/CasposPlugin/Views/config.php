<div class="form-group">
    <label class="control-label">
        <span class="glyphicon glyphicon-print">&nbsp;</span>
        <?= esc(lang('Plugins.caspos_config') ?? 'CASPOS Configuration') ?>
    </label>
</div>

<div class="form-group">
    <label for="api_url"><?= esc(lang('Plugins.caspos_api_url') ?? 'API URL') ?></label>
    <input type="text" class="form-control" name="api_url" 
           value="<?= esc($settings['api_url'] ?? '') ?>" 
           placeholder="https://api.caspos.gov.az/v1">
</div>

<div class="form-group">
    <label for="api_key"><?= esc(lang('Plugins.caspos_api_key') ?? 'API Key') ?></label>
    <input type="text" class="form-control" name="api_key" 
           value="<?= esc($settings['api_key'] ?? '') ?>">
</div>

<div class="form-group">
    <label for="merchant_id"><?= esc(lang('Plugins.caspos_merchant_id') ?? 'Merchant ID') ?></label>
    <input type="text" class="form-control" name="merchant_id" 
           value="<?= esc($settings['merchant_id'] ?? '') ?>">
</div>

<div class="form-group">
    <div class="checkbox">
        <label>
            <input type="checkbox" name="show_receipt_button" value="1" 
                   <?= ($settings['show_receipt_button'] ?? '1') === '1' ? 'checked' : '' ?>>
            <?= esc(lang('Plugins.caspos_show_button') ?? 'Show fiscal receipt button on receipts') ?>
        </label>
    </div>
</div>