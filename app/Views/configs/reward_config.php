<?php
/**
 * @var array $customer_rewards
 * @var array $config
 */
?>
<?= form_open('config/saveRewards/', ['id' => 'reward_config_form']) ?>

    <?php
    $title_info['config_title'] = lang('Config.reward_configuration');
    echo view('configs/config_header', $title_info);
    ?>

    <ul id="reward_error_message_box" class="error_message_box"></ul>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="customer_reward_enable" name="customer_reward_enable" value="customer_reward_enable" <?= $config['customer_reward_enable'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="customer_reward_enable"><?= lang('Config.customer_reward_enable'); ?></label>
    </div>

    <div class="row" id="customer_rewards">
        <?= view('partial/customer_rewards', ['customer_rewards' => $customer_rewards]) ?>
    </div>

    <div class="d-flex justify-content-end">
        <button class="btn btn-primary" type="submit" name="submit_reward"><?= lang('Common.submit'); ?></button>
    </div>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {

        var enable_disable_customer_reward_enable = (function() {
            var customer_reward_enable = $("#customer_reward_enable").is(":checked");
            $("input[name*='customer_reward']:not(input[name=customer_reward_enable])").prop("disabled", !customer_reward_enable);
            $("input[name*='reward_points_']:not(input[name=customer_reward_enable])").prop("disabled", !customer_reward_enable);
            if (customer_reward_enable) {
                $(".add_customer_reward, .remove_customer_reward").show();
            } else {
                $(".add_customer_reward, .remove_customer_reward").hide();
            }
            return arguments.callee;
        })();

        $("#customer_reward_enable").change(enable_disable_customer_reward_enable);

        var table_count = <?= sizeof($customer_rewards) ?>;

        var hide_show_remove = function() {
            if ($("input[name*='customer_rewards']:enabled").length > 1) {
                $(".remove_customer_rewards").show();
            } else {
                $(".remove_customer_rewards").hide();
            }
        };

        var add_customer_reward = function() {
            var id = $(this).parent().find('input').attr('id');
            id = id.replace(/.*?_(\d+)$/g, "$1");
            var previous_id = 'customer_reward_' + id;
            var previous_id_next = 'reward_points_' + id;
            var block = $(this).parent().clone(true);
            var new_block = block.insertAfter($(this).parent());
            var new_block_id = 'customer_reward_' + ++id;
            var new_block_id_next = 'reward_points_' + id;
            $(new_block).find('label').html("<?= lang('Config.customer_reward') ?> " + ++table_count).attr('for', new_block_id).attr('class', 'control-label col-xs-2');
            $(new_block).find("input[id='" + previous_id + "']").attr('id', new_block_id).removeAttr('disabled').attr('name', new_block_id).attr('class', 'form-control input-sm').val('');
            $(new_block).find("input[id='" + previous_id_next + "']").attr('id', new_block_id_next).removeAttr('disabled').attr('name', new_block_id_next).attr('class', 'form-control input-sm').val('');
            hide_show_remove();
        };

        var remove_customer_reward = function() {
            $(this).parent().remove();
            hide_show_remove();
        };

        var init_add_remove_tables = function() {
            $('.add_customer_reward').click(add_customer_reward);
            $('.remove_customer_reward').click(remove_customer_reward);
            hide_show_remove();
            // Set back disabled state
            enable_disable_customer_reward_enable();
        };
        init_add_remove_tables();

        var duplicate_found = false;
        // Run validator once for all fields
        $.validator.addMethod('customer_reward', function(value, element) {
            var value_count = 0;
            $("input[name*='customer_reward']:not(input[name=customer_reward_enable])").each(function() {
                value_count = $(this).val() == value ? value_count + 1 : value_count;
            });
            return value_count < 2;
        }, "<?= lang('Config.customer_reward_duplicate') ?>");

        $.validator.addMethod('valid_chars', function(value, element) {
            return value.indexOf('_') === -1;
        }, "<?= lang('Config.customer_reward_invalid_chars') ?>");

        $('#reward_config_form').validate($.extend(form_support.handler, {
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    beforeSerialize: function(arr, $form, options) {
                        $("input[name*='customer_reward']:not(input[name=customer_reward_enable])").prop("disabled", false);
                        return true;
                    },
                    success: function(response) {
                        $.notify({
                            icon: 'bi bi-bell-fill',
                            message: response.message
                        }, {
                            type: response.success ? 'success' : 'danger'
                        });
                        $("#customer_rewards").load('<?= "config/customerRewards" ?>', init_add_remove_tables);
                    },
                    dataType: 'json'
                });
            },

            errorLabelContainer: "#reward_error_message_box",

            rules: {
                <?php
                $i = 0;

                foreach ($customer_rewards as $customer_reward => $table) {
                ?>
                    <?= 'customer_reward_' . ++$i ?>: {
                        required: true,
                        customer_reward: true,
                        valid_chars: true
                    },
                <?php } ?>
            },

            messages: {
                <?php
                $i = 0;

                foreach ($customer_rewards as $customer_reward => $table) {
                ?>
                    <?= 'customer_reward_' . ++$i ?>: "<?= lang('Config.customer_reward_required') ?>",
                <?php } ?>
            }
        }));
    });
</script>
