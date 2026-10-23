<script type="text/javascript">
    (function(lang, $) {

        const lines = {
            'common_submit': "<?= lang('Common.submit') ?>",
            'common_close': "<?= lang('Common.close') ?>",
            'common_export_all': "<?= lang('Common.export_all') ?>",
            'common_export_page': "<?= lang('Common.export_page') ?>",
            'common_export_scope_toggle': "<?= lang('Common.export_scope_toggle') ?>",
            'common_export_xlsx_disabled_tooltip': "<?= lang('Common.export_xlsx_disabled_tooltip') ?>"
        };

        $.extend(lang, {
            line: function(key) {
                return lines[key];
            }
        });

    })(window.lang = window.lang || {}, jQuery);

    window.ospos_ext_zip_enabled = <?= extension_loaded('zip') ? 'true' : 'false' ?>;
</script>
