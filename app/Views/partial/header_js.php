<?php
/**
 * @var array $config
 */
?>

<script type="text/javascript">
    // Live clock
    var clock_tick = function clock_tick() {
        setInterval('update_clock();', 1000);
    }

    // Start the clock immediately
    clock_tick();

    var update_clock = function update_clock() {
        document.getElementById('liveclock').innerHTML = moment().format("<?= dateformat_momentjs($config['dateformat'] . ' ' . $config['timeformat']) ?>");
    }

    const notify = $.notify;

    $.notify = function(content, options) {
        const message = typeof content === "object" ? content.message : content;
        const sanitizedMessage = DOMPurify.sanitize(message);
        return notify(sanitizedMessage, options);
    };

    $.notifyDefaults({
        placement: {
            align: "<?= esc($config['notify_horizontal_position'], 'js') ?>",
            from: "<?= esc($config['notify_vertical_position'], 'js') ?>"
        }
    });

    var csrf_token = function() {
        return "<?= csrf_hash() ?>";
    };

    var csrf_form_base = function() {
        return {
            <?= esc(config('Security')->tokenName, 'js') ?>: function() {
                return csrf_token()
            }
        }
    };

    var setup_csrf_token = function() {
        $('input[name="<?= esc(config('Security')->tokenName, 'js') ?>"]').val(csrf_token());
    };

    var ajax = $.ajax;

    $.ajax = function() {
        var args = arguments[0];
        if (args['type'] && args['type'].toLowerCase() == 'post' && csrf_token()) {
            if (typeof args['data'] === 'string') {
                args['data'] += '&' + $.param(csrf_form_base());
            } else {
                args['data'] = $.extend(args['data'], csrf_form_base());
            }
        }

        return ajax.apply(this, arguments);
    };

    $(document).ajaxComplete(setup_csrf_token);
    $(document).ready(function() {
        $("#logout").click(function(event) {
            event.preventDefault();
            $.ajax({
                url: "<?= site_url('home/logout'); ?>",
                data: {
                    "<?= esc(config('Security')->tokenName, 'js'); ?>": csrf_token()
                },
                success: function() {
                    window.location.href = '<?= site_url(); ?>';
                },
                method: "POST"
            });
        });
    });

    var submit = $.fn.submit;

    $.fn.submit = function() {
        setup_csrf_token();
        submit.apply(this, arguments);
    };
</script>
