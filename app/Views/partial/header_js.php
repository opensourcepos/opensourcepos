<script type="text/javascript">
	// live clock
	var clock_tick = function clock_tick() {
		setInterval('update_clock();', 1000);
	}

	// start the clock immediatly
	clock_tick();

	var update_clock = function update_clock() {
		document.getElementById('liveclock').innerHTML = moment().format("<?php echo dateformat_momentjs($this->config->item('dateformat').' '.$this->config->item('timeformat'))?>");
	}

	$.notifyDefaults({ placement: {
		align: "<?php echo $this->config->item('notify_horizontal_position'); ?>",
		from: "<?php echo $this->config->item('notify_vertical_position'); ?>"
	}});

	var cookie_name = "<?php echo $this->config->item('cookie_prefix').$this->config->item('csrf_cookie_name'); ?>";

	var csrf_token = function() {
		return Cookies.get(cookie_name);
	};

	var csrf_form_base = function() {
		return { <?php echo $this->security->get_csrf_token_name(); ?> : function () { return csrf_token();  } };
	};

	var setup_csrf_token = function() {
		$('input[name="<?php echo $this->security->get_csrf_token_name(); ?>"]').val(csrf_token());
	};

	var ajax = $.ajax;

	$.ajax = function() {
		var args = arguments[0];
		if (args['type'] && args['type'].toLowerCase() == 'post' && csrf_token()) {
			if (typeof args['data'] === 'string')
			{
				args['data'] += '&' + $.param(csrf_form_base());
			}
			else
			{
				args['data'] = $.extend(args['data'], csrf_form_base());
			}
		}

		return ajax.apply(this, arguments);
	};

	$(document).ajaxComplete(setup_csrf_token);

	var submit = $.fn.submit;

	$.fn.submit = function() {
		setup_csrf_token();
		submit.apply(this, arguments);
	};
</script>
