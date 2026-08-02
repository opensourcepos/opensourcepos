<?php
/**
 * @var string $controller_name
 * @var object $person_info
 * @var array $packages
 * @var int $selected_package
 * @var bool $use_destination_based_tax
 * @var string $sales_tax_code_label
 * @var string $employee
 * @var array $config
 */
?>

<?= form_open("$controller_name/save/$person_info->person_id", ['id' => 'customer_form']) ?>

    <?php if (!empty($stats) || (!empty($mailchimp_info) && !empty($mailchimp_activity))) { ?>
        <ul class="nav nav-pills nav-justified mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link active" data-bs-toggle="pill" data-bs-target="#customer_basic_info" role="tab"><?= lang('Customers.basic_information') ?></button>
            </li>
            <?php if (!empty($stats)) { ?>
                <li class="nav-item" role="presentation">
                    <button type="button" class="nav-link" data-bs-toggle="pill" data-bs-target="#customer_stats_info" role="tab"><?= lang('Customers.stats_info') ?></button>
                </li>
            <?php } ?>
            <?php if (!empty($mailchimp_info) && !empty($mailchimp_activity)) { ?>
                <li class="nav-item" role="presentation">
                    <button type="button" class="nav-link" data-bs-toggle="pill" data-bs-target="#customer_mailchimp_info" role="tab"><?= lang('Customers.mailchimp_info') ?></button>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>

    <ul id="error_message_box" class="alert alert-warning d-none"></ul>

    <div class="tab-content">
        <div class="tab-pane show active" id="customer_basic_info" role="tabpanel" tabindex="0">
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="consent" id="consent" value="1" required <?php $checked = ($person_info->consent == '' ? !$config['enforce_privacy'] : (bool)$person_info->consent); if ($checked) { echo 'checked';} ?>>
                <label class="form-check-label" for="consent"><?= lang('Customers.consent') ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
            </div>

            <?= view('people/form_basic_info') ?>

            <label for="discount_type" class="form-label"><?= lang('Customers.discount_type') ?></label>
            <div class="mb-3">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="discount_type" id="discount_type_percent" value="0" <?php if ($person_info->discount_type == PERCENT) echo 'checked'; ?>>
                    <label class="form-check-label" for="discount_type_percent"><?= lang('Customers.discount_percent') ?></label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="discount_type" id="discount_type_fixed" value="1" <?php if ($person_info->discount_type == FIXED) echo 'checked'; ?>>
                    <label class="form-check-label" for="discount_type_fixed"><?= lang('Customers.discount_fixed') ?></label>
                </div>
            </div>

            <label for="discount" class="form-label"><?= lang('Customers.discount'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="discount-icon"><i class="bi bi-patch-minus"></i></span>
                <input type="number" step="any" class="form-control" name="discount" id="discount" aria-describedby="discount-icon" value="<?= $person_info->discount_type === FIXED ? to_currency_no_money($person_info->discount) : to_decimals($person_info->discount)?>">
            </div>

            <label for="company_name" class="form-label"><?= lang('Customers.company_name'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="company_name-icon"><i class="bi bi-building"></i></span>
                <input type="text" class="form-control" name="company_name" id="company_name" aria-describedby="company_name-icon" value="<?= $person_info->company_name ?>">
            </div>

            <label for="account_number" class="form-label"><?= lang('Customers.account_number'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="account_number-icon"><i class="bi bi-hash"></i></span>
                <input type="text" class="form-control" name="account_number" id="account_number" aria-describedby="account_number-icon" value="<?= $person_info->account_number ?>">
            </div>

            <label for="tax_id" class="form-label"><?= lang('Customers.tax_id'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="tax_id-icon"><i class="bi bi-bank"></i></span>
                <input type="text" class="form-control" name="tax_id" id="tax_id" aria-describedby="tax_id-icon" value="<?= $person_info->tax_id ?>">
            </div>

            <?php if ($config['customer_reward_enable']): ?>
                <label for="rewards" class="form-label"><?= lang('Customers.rewards_package'); ?></label>
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-trophy"></i></span>
                    <select class="form-select" name="package_id">
                        <?php foreach ($packages as $id => $label): ?>
                            <option value="<?= $id ?>" <?= $id == $selected_package ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <label for="available_points" class="form-label"><?= lang('Customers.available_points'); ?></label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="available_points-icon"><i class="bi bi-hand-thumbs-up"></i></span>
                    <input type="text" class="form-control" name="available_points" id="available_points" aria-describedby="available_points-icon" value="<?= $person_info->points ?>" disabled readonly>
                </div>
            <?php endif; ?>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="taxable" id="taxable" value="1" <?php if ($person_info->taxable == 1) echo 'checked'; ?>>
                <label class="form-check-label" for="taxable"><?= lang('Customers.taxable') ?></label>
            </div>

            <?php if ($use_destination_based_tax): ?>
                <label for="sales_tax_code_name" class="form-label"><?= lang('Customers.tax_code'); ?></label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="sales_tax_code_name-icon"><i class="bi bi-bank"></i></span>
                    <input type="hidden" name="sales_tax_code_id" value="<?= $person_info->sales_tax_code_id ?>">
                    <input type="text" class="form-control" name="sales_tax_code_name" id="sales_tax_code_name" aria-describedby="sales_tax_code_name-icon" size="50" value="<?= $sales_tax_code_label ?>">
                </div>
            <?php endif; ?>

            <label for="datetime" class="form-label"><?= lang('Customers.date'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="datetime-icon"><i class="bi bi-calendar2"></i></span>
                <input type="hidden" name="date" id="datetime" aria-describedby="datetime-icon" value="<?= to_datetime(strtotime($person_info->date)) ?>">
                <input type="text" class="form-control" value="<?= to_datetime(strtotime($person_info->date)) ?>" disabled readonly>
            </div>

            <label for="employee" class="form-label"><?= lang('Customers.employee'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="employee-icon"><i class="bi bi-person"></i></span>
                <input type="hidden" name="employee_id" value="<?= $person_info->employee_id ?>">
                <input type="text" class="form-control" name="employee" id="employee" aria-describedby="employee-icon" value="<?= $employee ?>" disabled readonly>
            </div>
        </div>

        <?php if (!empty($stats)) { ?>
            <div class="tab-pane" id="customer_stats_info" role="tabpanel" tabindex="0">
                <label for="total" class="form-label"><?= lang('Customers.total'); ?></label>
                <div class="input-group mb-3">
                    <?php if (!is_right_side_currency_symbol()): ?>
                        <span class="input-group-text" id="total-icon"><?= esc($config['currency_symbol']) ?></span>
                    <?php endif; ?>
                    <input type="text" class="form-control" name="total" id="total" aria-describedby="total-icon" value="<?= to_currency_no_money($stats->total) ?>" disabled readonly>
                    <?php if (is_right_side_currency_symbol()): ?>
                        <span class="input-group-text" id="total-icon"><?= esc($config['currency_symbol']) ?></span>
                    <?php endif; ?>
                </div>

                <label for="max" class="form-label"><?= lang('Customers.max'); ?></label>
                <div class="input-group mb-3">
                    <?php if (!is_right_side_currency_symbol()): ?>
                        <span class="input-group-text" id="max-icon"><?= esc($config['currency_symbol']) ?></span>
                    <?php endif; ?>
                    <input type="text" class="form-control" name="max" id="max" aria-describedby="max-icon" value="<?= to_currency_no_money($stats->max) ?>" disabled readonly>
                    <?php if (is_right_side_currency_symbol()): ?>
                        <span class="input-group-text" id="max-icon"><?= esc($config['currency_symbol']) ?></span>
                    <?php endif; ?>
                </div>

                <label for="min" class="form-label"><?= lang('Customers.min'); ?></label>
                <div class="input-group mb-3">
                    <?php if (!is_right_side_currency_symbol()): ?>
                        <span class="input-group-text" id="min-icon"><?= esc($config['currency_symbol']) ?></span>
                    <?php endif; ?>
                    <input type="text" class="form-control" name="min" id="min" aria-describedby="min-icon" value="<?= to_currency_no_money($stats->min) ?>" disabled readonly>
                    <?php if (is_right_side_currency_symbol()): ?>
                        <span class="input-group-text" id="min-icon"><?= esc($config['currency_symbol']) ?></span>
                    <?php endif; ?>
                </div>

                <label for="average" class="form-label"><?= lang('Customers.average'); ?></label>
                <div class="input-group mb-3">
                    <?php if (!is_right_side_currency_symbol()): ?>
                        <span class="input-group-text" id="average-icon"><?= esc($config['currency_symbol']) ?></span>
                    <?php endif; ?>
                    <input type="text" class="form-control" name="average" id="average" aria-describedby="average-icon" value="<?= to_currency_no_money($stats->average) ?>" disabled readonly>
                    <?php if (is_right_side_currency_symbol()): ?>
                        <span class="input-group-text" id="average-icon"><?= esc($config['currency_symbol']) ?></span>
                    <?php endif; ?>
                </div>

                <label for="quantity" class="form-label"><?= lang('Customers.quantity'); ?></label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="quantity-icon"><i class="bi bi-123"></i></span>
                    <input type="text" class="form-control" name="quantity" id="quantity" aria-describedby="quantity-icon" value="<?= to_quantity_decimals($stats->quantity) ?>" disabled readonly>
                </div>

                <label for="avg_discount" class="form-label"><?= lang('Customers.avg_discount'); ?></label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="avg_discount-icon"><i class="bi bi-percent"></i></span>
                    <input type="text" class="form-control" name="avg_discount" id="avg_discount" aria-describedby="avg_discount-icon" value="<?= to_decimals($stats->avg_discount) ?>" disabled readonly>
                </div>
            </div>
        <?php } ?>

        <?php if (!empty($mailchimp_info) && !empty($mailchimp_activity)) { ?>
            <div class="tab-pane" id="customer_mailchimp_info" role="tabpanel" tabindex="0">
                <label for="mailchimp_status" class="form-label"><?= lang('Customers.mailchimp_status'); ?></label>
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-envelope-check"></i></span>
                    <select class="form-select" name="mailchimp_status" id="mailchimp_status">
                        <option value="subscribed" <?= $mailchimp_info['status'] === 'subscribed' ? 'selected' : '' ?>>Subscribed</option>
                        <option value="unsubscribed" <?= $mailchimp_info['status'] === 'unsubscribed' ? 'selected' : '' ?>>Unsubscribed</option>
                        <option value="cleaned" <?= $mailchimp_info['status'] === 'cleaned' ? 'selected' : '' ?>>Cleaned</option>
                        <option value="pending" <?= $mailchimp_info['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                    </select>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="mailchimp_vip" id="mailchimp_vip" value="1" <?= $mailchimp_info['vip'] == 1 ? 'checked' : '' ?>>
                    <label class="form-check-label" for="mailchimp_vip"><?= lang('Customers.mailchimp_vip') ?></label>
                </div>

                <label for="mailchimp_member_rating" class="form-label"><?= lang('Customers.mailchimp_member_rating'); ?></label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="mailchimp_member_rating-icon"><i class="bi bi-hand-thumbs-up"></i></span>
                    <input type="text" class="form-control" name="mailchimp_member_rating" id="mailchimp_member_rating" aria-describedby="mailchimp_member_rating-icon" value="<?= $mailchimp_info['member_rating'] ?>" disabled readonly>
                </div>

                <label for="mailchimp_activity_total" class="form-label"><?= lang('Customers.mailchimp_activity_total'); ?></label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="mailchimp_activity_total-icon"><i class="bi bi-envelope-arrow-up"></i></span>
                    <input type="text" class="form-control" name="mailchimp_activity_total" id="mailchimp_activity_total" aria-describedby="mailchimp_activity_total-icon" value="<?= $mailchimp_activity['total'] ?>" disabled readonly>
                </div>

                <label for="mailchimp_activity_lastopen" class="form-label"><?= lang('Customers.mailchimp_activity_lastopen'); ?></label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="mailchimp_activity_lastopen-icon"><i class="bi bi-calendar2-check"></i></span>
                    <input type="text" class="form-control" name="mailchimp_activity_lastopen" id="mailchimp_activity_lastopen" aria-describedby="mailchimp_activity_lastopen-icon" value="<?= $mailchimp_activity['lastopen'] ?>" disabled readonly>
                </div>

                <label for="mailchimp_activity_open" class="form-label"><?= lang('Customers.mailchimp_activity_open'); ?></label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="mailchimp_activity_open-icon"><i class="bi bi-envelope-open"></i></span>
                    <input type="text" class="form-control" name="mailchimp_activity_open" id="mailchimp_activity_open" aria-describedby="mailchimp_activity_open-icon" value="<?= $mailchimp_activity['open'] ?>" disabled readonly>
                </div>

                <label for="mailchimp_activity_click" class="form-label"><?= lang('Customers.mailchimp_activity_click'); ?></label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="mailchimp_activity_click-icon"><i class="bi bi-hand-index"></i></span>
                    <input type="text" class="form-control" name="mailchimp_activity_click" id="mailchimp_activity_click" aria-describedby="mailchimp_activity_click-icon" value="<?= $mailchimp_activity['click'] ?>" disabled readonly>
                </div>

                <label for="mailchimp_activity_unopen" class="form-label"><?= lang('Customers.mailchimp_activity_unopen'); ?></label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="mailchimp_activity_unopen-icon"><i class="bi bi-envelope-slash"></i></span>
                    <input type="text" class="form-control" name="mailchimp_activity_unopen" id="mailchimp_activity_unopen" aria-describedby="mailchimp_activity_unopen-icon" value="<?= $mailchimp_activity['unopen'] ?>" disabled readonly>
                </div>

                <label for="mailchimp_email_client" class="form-label"><?= lang('Customers.mailchimp_email_client'); ?></label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="mailchimp_email_client-icon"><i class="bi bi-inbox"></i></span>
                    <input type="text" class="form-control" name="mailchimp_email_client" id="mailchimp_email_client" aria-describedby="mailchimp_email_client-icon" value="<?= $mailchimp_info['email_client'] ?>" disabled readonly>
                </div>
            </div>
        <?php } ?>
    </div>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        $("input[name='sales_tax_code_name']").change(function() {
            if (!$("input[name='sales_tax_code_name']").val()) {
                $("input[name='sales_tax_code_id']").val('');
            }
        });

        var fill_value = function(event, ui) {
            event.preventDefault();
            $("input[name='sales_tax_code_id']").val(ui.item.value);
            $("input[name='sales_tax_code_name']").val(ui.item.label);
        };

        $('#sales_tax_code_name').autocomplete({
            source: "<?= esc('taxes/suggestTaxCodes') ?>",
            minChars: 0,
            delay: 15,
            cacheLength: 1,
            appendTo: '.modal-content',
            select: fill_value,
            focus: fill_value
        });

        $('#customer_form').validate($.extend({
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    success: function(response) {
                        dialog_support.hide();
                        table_support.handle_submit("<?= $controller_name ?>", response);
                    },
                    dataType: 'json'
                });
            },

            errorLabelContainer: '#error_message_box',

            rules: {
                first_name: 'required',
                last_name: 'required',
                consent: 'required',
                email: {
                    remote: {
                        url: "<?= "$controller_name/checkEmail" ?>",
                        type: 'POST',
                        data: {
                            'person_id': "<?= $person_info->person_id ?>"
                            // Email is posted by default
                        }
                    }
                },
                account_number: {
                    remote: {
                        url: "<?= "$controller_name/checkAccountNumber" ?>",
                        type: 'POST',
                        data: {
                            'person_id': "<?= $person_info->person_id ?>"
                            // Account_number is posted by default
                        }
                    }
                }
            },

            messages: {
                first_name: "<?= lang('Common.first_name_required') ?>",
                last_name: "<?= lang('Common.last_name_required') ?>",
                consent: "<?= lang('Customers.consent_required') ?>",
                email: "<?= lang('Customers.email_duplicate') ?>",
                account_number: "<?= lang('Customers.account_number_duplicate') ?>"
            }
        }, form_support.error));
    });
</script>
