(function (lang, $) {
  var lines = {
    common_submit: "<?= $this->lang->line('common_submit') ?>",
    common_close: "<?= $this->lang->line('common_close') ?>",
  };

  $.extend(lang, {
    line: function (key) {
      return lines[key];
    },
  });
})((window.lang = window.lang || {}), jQuery);
