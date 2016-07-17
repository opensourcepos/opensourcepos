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
		align: '<?php echo $this->config->item('notify_horizontal_position'); ?>',
		from: '<?php echo $this->config->item('notify_vertical_position'); ?>'
	}});

	var post = $.post;

	var csrf_token = function() {
		return Cookies.get('<?php echo $this->config->item('csrf_cookie_name'); ?>');
	};

	var csrf_form_base = function() {
		return { <?php echo $this->security->get_csrf_token_name(); ?> : function () { return csrf_token();  } };
	};

	$.post = function() {
		arguments[1] = $.extend(arguments[1], csrf_form_base());
		post.apply(this, arguments);
	};

	var setup_csrf_token = function() {
		$('input[name="<?php echo $this->security->get_csrf_token_name(); ?>"]').val(csrf_token());
	};

	setup_csrf_token();

	$.ajaxSetup({
		dataFilter: function(data) {
			setup_csrf_token();
			return data;
		}
	});

	$.validator.methods.number = function (value, element) {
		return this.optional(element) || /^-?(?:\d+|\d{1,3}(?:[\s\.,]\d{3})+)(?:[\.,]\d+)?$/.test(value);
	}

</script>