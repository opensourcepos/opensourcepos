<script type="text/javascript">
    (function(lang, $) {

        var lines = {
            'common_submit': "<?= lang('Common.submit') ?>",
            'common_close': "<?= lang('Common.close') ?>"
        };

        $.extend(lang, {
            line: function(key) {
                return lines[key];
            }
        });

    })(window.lang = window.lang || {}, jQuery);
</script>
