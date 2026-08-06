/**
 * Collects all [data-plugin-field] inputs on the page and injects them as a
 * plugin_data JSON hidden field into any form marked with data-plugin-form
 * before submission. Plugin devs only need data-plugin-field="pluginid_varname"
 * on their inputs — no JS required in plugin partials.
 */
$(function() {
    $('form[data-plugin-form]').on('submit', function() {
        var pluginData = {};

        $('[data-plugin-field]').each(function() {
            var key = $(this).data('plugin-field');
            var $el = $(this);
            var val;

            if ($el.is(':checkbox')) {
                // bootstrap-toggle stores state on the original hidden input via .prop('checked')
                val = $el.prop('checked');
            } else {
                val = $el.val();
            }

            pluginData[key] = val;
        });

        var $existing = $(this).find('input[name="plugin_data"]');
        if ($existing.length) {
            $existing.val(JSON.stringify(pluginData));
        } else {
            $(this).append(
                $('<input>').attr({ type: 'hidden', name: 'plugin_data' }).val(JSON.stringify(pluginData))
            );
        }
    });
});
