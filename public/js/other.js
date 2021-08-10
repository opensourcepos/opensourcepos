$.notifyDefaults({
  placement: {
    align: "<?= $this->config->item('notify_horizontal_position'); ?>",
    from: "<?= $this->config->item('notify_vertical_position'); ?>",
  },
});

var cookie_name = "<?= $this->config->item('cookie_prefix').$this->config->item('csrf_cookie_name'); ?>";

var csrf_token = function () {
  return Cookies.get(cookie_name);
};

var csrf_form_base = function () {
  return {
    "<?= $this->security->get_csrf_token_name(); ?>": function () {
      return csrf_token();
    },
  };
};

var setup_csrf_token = function () {
  $('input[name="<?= $this->security->get_csrf_token_name(); ?>"]').val(csrf_token());
};

var ajax = $.ajax;

$.ajax = function () {
  var args = arguments[0];
  if (args["type"] && args["type"].toLowerCase() == "post" && csrf_token()) {
    if (typeof args["data"] === "string") {
      args["data"] += "&" + $.param(csrf_form_base());
    } else {
      args["data"] = $.extend(args["data"], csrf_form_base());
    }
  }

  return ajax.apply(this, arguments);
};

$(document).ajaxComplete(setup_csrf_token);

var submit = $.fn.submit;

$.fn.submit = function () {
  setup_csrf_token();
  submit.apply(this, arguments);
};
