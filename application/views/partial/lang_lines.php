<script type="text/javascript">
(function(lang, $) {

    var lines = {
        'common_submit' : "<?php echo $this->lang->line('common_submit') ?>",
        'common_close' : "<?php echo $this->lang->line('common_close') ?>"
    };

    $.extend(lang, {
        line: function(key) {
            return lines[key];
        }
    });


})(window.lang = window.lang || {}, jQuery);
</script>