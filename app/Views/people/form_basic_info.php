<?php
/**
 * @var object $person_info
 * @var array $config
 */
?>

<label for="first_name" class="form-label"><?= lang('Common.first_name'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
<div class="input-group mb-3">
    <span class="input-group-text" id="first_name-icon"><i class="bi bi-person-square"></i></span>
    <input type="text" class="form-control" name="first_name" id="first_name" aria-describedby="first_name-icon" value="<?= $person_info->first_name; ?>" required>
</div>

<label for="last_name" class="form-label"><?= lang('Common.last_name'); ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
<div class="input-group mb-3">
    <span class="input-group-text" id="last_name-icon"><i class="bi bi-person-square"></i></span>
    <input type="text" class="form-control" name="last_name" id="last_name" aria-describedby="last_name-icon" value="<?= $person_info->last_name; ?>" required>
</div>

<label for="gender" class="form-label"><?= lang('Common.gender'); ?><?php if (!empty($basic_version)): ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup><?php endif ?></label>
<div class="row mb-3">
    <div class="col-12">
        <div class="input-group">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="gender" id="gender_male" value="1" <?= $person_info->gender === '1' ? 'checked' : '' ?>>
                <label class="form-check-label" for="gender_male"><i class="bi bi-gender-male me-1"></i><?= lang('Common.gender_male') ?></label>
            </div>

            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="gender" id="gender_female" value="0" <?= $person_info->gender === '0' ? 'checked' : '' ?>>
                <label class="form-check-label" for="gender_female"><i class="bi bi-gender-female me-1"></i><?= lang('Common.gender_female') ?></label>
            </div>
        </div>
    </div>
</div>

<label for="email" class="form-label"><?= lang('Common.email'); ?></label>
<div class="input-group mb-3">
    <span class="input-group-text" id="email-icon"><i class="bi bi-envelope"></i></span>
    <input type="email" class="form-control" name="email" id="email" aria-describedby="email-icon" value="<?= $person_info->email; ?>">
</div>

<label for="phone_number" class="form-label"><?= lang('Common.phone_number'); ?></label>
<div class="input-group mb-3">
    <span class="input-group-text" id="phone_number-icon"><i class="bi bi-telephone"></i></span>
    <input type="tel" class="form-control" name="phone_number" id="phone_number" aria-describedby="phone_number-icon" value="<?= $person_info->phone_number; ?>">
</div>

<label for="address_1" class="form-label"><?= lang('Common.address_1'); ?></label>
<div class="input-group mb-3">
    <span class="input-group-text" id="address_1-icon"><i class="bi bi-geo-alt"></i></span>
    <input type="text" class="form-control" name="address_1" id="address_1" aria-describedby="address_1-icon" value="<?= $person_info->address_1; ?>">
</div>

<label for="address_2" class="form-label"><?= lang('Common.address_2'); ?></label>
<div class="input-group mb-3">
    <span class="input-group-text" id="address_2-icon"><i class="bi bi-geo-alt"></i></span>
    <input type="text" class="form-control" name="address_2" id="address_2" aria-describedby="address_2-icon" value="<?= $person_info->address_2; ?>">
</div>

<label for="city" class="form-label"><?= lang('Common.city'); ?></label>
<div class="input-group mb-3">
    <span class="input-group-text" id="city-icon"><i class="bi bi-geo-alt"></i></span>
    <input type="text" class="form-control" name="city" id="city" aria-describedby="city-icon" value="<?= $person_info->city; ?>">
</div>

<label for="state" class="form-label"><?= lang('Common.state'); ?></label>
<div class="input-group mb-3">
    <span class="input-group-text" id="state-icon"><i class="bi bi-geo-alt"></i></span>
    <input type="text" class="form-control" name="state" id="state" aria-describedby="state-icon" value="<?= $person_info->state; ?>">
</div>

<label for="postcode" class="form-label"><?= lang('Common.zip'); ?></label>
<div class="input-group mb-3">
    <span class="input-group-text" id="zip-icon"><i class="bi bi-geo-alt"></i></span>
    <input type="text" class="form-control" name="zip" id="postcode" aria-describedby="zip-icon" value="<?= $person_info->zip; ?>">
</div>

<label for="country" class="form-label"><?= lang('Common.country'); ?></label>
<div class="input-group mb-3">
    <span class="input-group-text" id="country-icon"><i class="bi bi-globe-americas"></i></span>
    <input type="text" class="form-control" name="country" id="country" aria-describedby="country-icon" value="<?= $person_info->country; ?>">
</div>

<label for="comments" class="form-label"><?= lang('Common.comments'); ?></label>
<div class="input-group mb-3">
    <span class="input-group-text"><i class="bi bi-chat"></i></span>
    <textarea class="form-control" name="comments" id="comments" rows="6"><?= $person_info->comments; ?></textarea>
</div>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        nominatim.init({
            fields: {
                postcode: {
                    dependencies: ["postcode", "city", "state", "country"],
                    response: {
                        field: 'postalcode',
                        format: ["postcode", "village|town|hamlet|city_district|city", "state", "country"]
                    }
                },

                city: {
                    dependencies: ["postcode", "city", "state", "country"],
                    response: {
                        format: ["postcode", "village|town|hamlet|city_district|city", "state", "country"]
                    }
                },

                state: {
                    dependencies: ["state", "country"]
                },

                country: {
                    dependencies: ["state", "country"]
                }
            },
            language: '<?= current_language_code() ?>',
            country_codes: '<?= esc($config['country_codes'], 'js') ?>'
        });
    });
</script>
