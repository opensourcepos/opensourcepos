/**
 * Collects all [data-plugin-field] inputs on the page and injects them as a
 * plugin_data JSON hidden field into any form marked with data-plugin-form.
 * Plugin devs only need data-plugin-field="pluginid_varname" on their inputs
 * — no JS required in plugin partials.
 *
 * Rebuilt on every change/click of a [data-plugin-field] element (not just on
 * submit) because some forms use jQuery Validate's submitHandler, which
 * bypasses the form's native submit event entirely — a submit-only listener
 * would never see those forms.
 */
$(function() {
    const syncPluginData = function($form) {
        const pluginData = {};

        $('[data-plugin-field]').each(function() {
            const key = $(this).data('plugin-field');
            const $el = $(this);
            let val;

            if ($el.is(':checkbox')) {
                // bootstrap-toggle stores state on the original hidden input via .prop('checked')
                val = $el.prop('checked');
            } else {
                val = $el.val();
            }

            pluginData[key] = val;
        });

        const $existing = $form.find('input[name="plugin_data"]');
        if ($existing.length) {
            $existing.val(JSON.stringify(pluginData));
        } else {
            $form.append(
                $('<input>').attr({ type: 'hidden', name: 'plugin_data' }).val(JSON.stringify(pluginData))
            );
        }
    };

    $('form[data-plugin-form]').each(function() {
        syncPluginData($(this));
    });

    $(document).on('change click', '[data-plugin-field]', function() {
        $('form[data-plugin-form]').each(function() {
            syncPluginData($(this));
        });
    });

    $('form[data-plugin-form]').on('submit', function() {
        syncPluginData($(this));
    });
});
