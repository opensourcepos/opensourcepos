<?php
/**
 * @var array $customer_rewards
 */
?>

<span class="d-flex justify-content-start add_customer_reward">
    <button class="btn btn-outline-success mb-3" type="button"><i class="bi bi-plus-lg"></i>&nbsp;Add reward</button> <!-- TODO-BS5 translate -->
</span>

<?php
$i = 0;

foreach ($customer_rewards as $reward_key => $reward_category) {
    $customer_reward_id = $reward_category['package_id'];
    $customer_reward_name = $reward_category['package_name'];
    $customer_points_percent = $reward_category['points_percent'];
    ++$i;
?>

    <div class="col-12 col-lg-6 <?= $reward_category['deleted'] ? 'd-none' : '' ?>">
        <label for="customer_reward_<?= $i ?>" class="form-label"><?= lang('Config.customer_reward') . " $i"; ?></label>
        <div class="input-group mb-3">
            <span class="input-group-text"><?= $customer_reward_id ?>.</span>
            <input type="text" class="form-control customer_reward valid_chars w-25" name="customer_reward_<?= $customer_reward_id ?>" id="customer_reward_<?= $customer_reward_id ?>" value="<?= $customer_reward_name ?>" required <?= $reward_category['deleted'] ? 'disabled' : '' ?>>
            <input type="number" min="0" class="form-control customer_reward valid_chars" name="reward_points_<?= $customer_reward_id ?>" id="reward_points_<?= $customer_reward_id ?>" value="<?= $customer_points_percent ?>" required <?= $reward_category['deleted'] ? 'disabled' : '' ?>>
            <button class="btn btn-outline-danger remove_customer_reward" type="button"><i class="bi bi-x-lg"></i></button>
        </div>
    </div>

<?php } ?>
