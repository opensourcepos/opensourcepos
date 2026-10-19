<script type="text/javascript">
    (function(lang, $) {

        const lines = {
            'common_submit': "<?= lang('Common.submit') ?>",
            'common_close': "<?= lang('Common.close') ?>",
            'common_export_all': "<?= lang('Common.export_all') ?>",
            'common_export_page': "<?= lang('Common.export_page') ?>",
            'common_export_scope_toggle': "<?= lang('Common.export_scope_toggle') ?>"
        };

        $.extend(lang, {
            line: function(key) {
                return lines[key];
            }
        });

    })(window.lang = window.lang || {}, jQuery);
</script>
