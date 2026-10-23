const report_export_toggle = (function () {
    let export_all_enabled = false;

    const init_export_all_toggle = function () {
        const $toggleInput = $('<input>', {
            type: 'checkbox',
            id: 'export_all_rows',
            'data-toggle': 'toggle',
            'data-size': 'small',
            'data-onstyle': 'info',
            'data-offstyle': 'default',
            'data-on': lang.line('common_export_all'),
            'data-off': lang.line('common_export_page')
        }).prependTo('#title_bar');

        $toggleInput.bootstrapToggle();

        const $toggleWrapper = $toggleInput.closest('.toggle');
        $toggleWrapper.addClass('pull-right print_hide').find('.btn').css('float', 'none');
        $toggleWrapper.attr({
            role: 'switch',
            tabindex: '0',
            'aria-label': lang.line('common_export_scope_toggle'),
            'aria-checked': $toggleInput.prop('checked')
        });

        $toggleWrapper.on('keydown', function(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                $toggleWrapper.trigger('click');
            }
        });

        $toggleInput.on('change', function() {
            $toggleWrapper.attr('aria-checked', this.checked);
            export_all_enabled = this.checked;
        });
    };

    // Reports render their full dataset synchronously with positional path segments, so "export all"
    // just re-requests the same URL's path (plus any querystring) under /exportAll/{format}, rather than
    // building query params the way manage tables' sidePagination:'server' export does.
    const init_export_all_intercept = function () {
        document.addEventListener('click', function(event) {
            if (!export_all_enabled) {
                return;
            }

            const $exportItem = $(event.target).closest('.export [data-type], .export[data-type]');
            if (!$exportItem.length) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            if ($exportItem.hasClass('disabled') || $exportItem.attr('aria-disabled') === 'true') {
                return;
            }

            const format = $exportItem.attr('data-type') || $exportItem.closest('.export').data('type');

            $exportItem.closest('.dropdown-menu').prev('[data-toggle="dropdown"]').dropdown('toggle');

            const path = location.pathname.replace(/(\/reports\/[^/]+)/, '$1/exportAll/' + format);
            window.location = path + location.search;
        }, true);
    };

    const init_xlsx_export_gate = function () {
        if (window.ospos_ext_zip_enabled) {
            return;
        }

        const $xlsxItem = $('.export [data-type="xlsx"], .export[data-type="xlsx"]');

        $xlsxItem
            .addClass('disabled')
            .attr('aria-disabled', 'true')
            .attr('title', lang.line('common_export_xlsx_disabled_tooltip'))
            .off('click')
            .on('click', function(event) {
                event.preventDefault();
                event.stopImmediatePropagation();
            })
            .tooltip({ container: 'body' });
    };

    const init = function () {
        init_export_all_toggle();
        init_export_all_intercept();
        init_xlsx_export_gate();
    };

    return { init };
})();
