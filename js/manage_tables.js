(function(dialog_support, $) {

	var btn_id, dialog_ref;

	var hide = function() {
		dialog_ref.close();
	};

	var clicked_id = function() {
		return btn_id;
	};

	var submit = function(button_id) {
		return function(dlog_ref) {
			btn_id = button_id;
			dialog_ref = dlog_ref;
			if (button_id == 'submit') {
				$('form', dlog_ref.$modalBody).first().submit();
			}
		}
	};

	var button_class = {
		'submit' : 'btn-primary',
		'delete' : 'btn-danger'
	};

	var init = function(selector) {

		var buttons = function(event) {
			var buttons = [];
			var dialog_class = 'modal-dlg';
			$.each($(this).attr('class').split(/\s+/), function(classIndex, className) {
				var width_class = className.split("modal-dlg-");
				if (width_class && width_class.length > 1) {
					dialog_class = className;
				}
			});

			$.each($(this).data(), function(name, value) {
				var btn_class = name.split("btn");
				if (btn_class && btn_class.length > 1) {
					var btn_name = btn_class[1].toLowerCase();
					var is_submit = btn_name == 'submit';
					buttons.push({
						id: btn_name,
						label: value,
						cssClass: button_class[btn_name],
						hotkey: is_submit ? 13 : undefined, // Enter.
						action: submit(btn_name)
					});
				}
			});

			!buttons.length && buttons.push({
				id: 'close',
				label: lang.line('common_close'),
				cssClass: 'btn-primary',
				action: function(dialog_ref) {
					dialog_ref.close();
				}
			});
			return { buttons: buttons.sort(function(a, b) {
				return ($(b).text()) < ($(a).text()) ? -1 : 1;
			}), cssClass: dialog_class};
		};

		$(selector).each(function(index, $element) {

			return $(selector).off('click').on('click', function(event) {
				var $link = $(event.target);
				$link = !$link.is("a, button") ? $link.parents("a, button") : $link ;
				BootstrapDialog.show($.extend({
					title: $link.attr('title'),
					message: (function() {
						var node = $('<div></div>');
						$.get($link.attr('href') || $link.data('href'), function(data) {
							node.html(data);
						});
						return node;
					})
				}, buttons.call(this, event)));

				return false;
			});
		});
	};

	$.extend(dialog_support, {
		init: init,
		submit: submit,
		hide: hide,
		clicked_id: clicked_id
	});

})(window.dialog_support = window.dialog_support || {}, jQuery);

(function(table_support, $) {

	var enable_actions = function(callback) {
		return function() {
			var selection_empty = selected_rows().length == 0;
			$("#toolbar button:not(.dropdown-toggle)").attr('disabled', selection_empty);
			typeof callback == 'function' && callback();
		}
	};

	var table = function() {
		return $("#table").data('bootstrap.table');
	}

	var selected_ids = function () {
		return $.map(table().getSelections(), function (element) {
			return element[options.uniqueId || 'id'] !== '-' ? element[options.uniqueId || 'id'] : null;
		});
	};

	var selected_rows = function () {
		return $("#table td input:checkbox:checked").parents("tr");
	};

	var row_selector = function(id) {
		return "tr[data-uniqueid='" + id + "']";
	};

	var rows_selector = function(ids) {
		var selectors = [];
		ids = ids instanceof Array ? ids : ("" + ids).split(":");
		$.each(ids, function(index, element) {
			selectors.push(row_selector(element));
		});
		return selectors;;
	};

	var highlight_row = function (id, color) {
		$(rows_selector(id)).each(function(index, element) {
			var original = $(element).css('backgroundColor');
			$(element).find("td").animate({backgroundColor: color || '#e1ffdd'}, "slow", "linear")
				.animate({backgroundColor: color || '#e1ffdd'}, 5000)
				.animate({backgroundColor: original}, "slow", "linear");
		});
	};

	var do_delete = function (url, ids) {
		if (confirm($.fn.bootstrapTable.defaults.formatConfirmDelete())) {
			$.post((url || options.resource) + '/delete', {'ids[]': ids || selected_ids()}, function (response) {
				//delete was successful, remove checkbox rows
				if (response.success) {
					var selector = ids ? row_selector(ids) : selected_rows();
					table().collapseAllRows();
					$(selector).each(function (index, element) {
						$(this).find("td").animate({backgroundColor: "green"}, 1200, "linear")
							.end().animate({opacity: 0}, 1200, "linear", function () {
								table().remove({
									field: options.uniqueId,
									values: selected_ids()
								});
								if (index == $(selector).length - 1) {
									refresh();
									enable_actions();
								}
							});
					});
					$.notify(response.message, { type: 'success' });
				} else {
					$.notify(response.message, { type: 'danger' });
				}
			}, "json");
		} else {
			return false;
		}
	};

	var load_success = function(callback) {
		return function(response) {
			typeof options.load_callback == 'function' && options.load_callback();
			options.load_callback = undefined;
			dialog_support.init("a.modal-dlg");
			typeof callback == 'function' && callback.call(this, response);
		}
	};

	var options;

	var toggle_column_visbility = function() {
		if (localStorage[options.employee_id]) {
			var user_settings = JSON.parse(localStorage[options.employee_id]);
			user_settings[options.resource] && $.each(user_settings[options.resource], function(index, element) {
				element ? table().showColumn(index) : table().hideColumn(index);
			});
		}
	};

	var init = function (_options) {
		options = _options;
		enable_actions = enable_actions(options.enableActions);
		$('#table').bootstrapTable($.extend(options, {
			columns: options.headers,
			url: options.resource + '/search',
			sidePagination: 'server',
			pageSize: options.pageSize,
			striped: true,
			pagination: true,
			search: options.resource || false,
			showColumns: true,
			clickToSelect: true,
			showExport: true,
			exportOptions: {
				fileName: options.resource.replace(/.*\/(.*?)$/g, '$1')
			},
			onPageChange: load_success(options.onLoadSuccess),
			toolbar: '#toolbar',
			uniqueId: options.uniqueId || 'id',
			onCheck: enable_actions,
			onUncheck: enable_actions,
			onCheckAll: enable_actions,
			onUncheckAll: enable_actions,
			onLoadSuccess: load_success(options.onLoadSuccess),
			onColumnSwitch : function(field, checked) {
				var user_settings = localStorage[options.employee_id];
				user_settings = (user_settings && JSON.parse(user_settings)) || {};
				user_settings[options.resource] = user_settings[options.resource] || {};
				user_settings[options.resource][field] = checked;
				localStorage[options.employee_id] = JSON.stringify(user_settings);
			},
			queryParamsType: 'limit',
			iconSize: 'sm',
			silentSort: true,
			paginationVAlign: 'bottom',
			escape: false
		}));
		enable_actions();
		init_delete();
		toggle_column_visbility();
		dialog_support.init("button.modal-dlg");
	};

	var init_delete = function (confirmMessage) {
		$("#delete").click(function (event) {
			do_delete();
		});
	};

	var refresh = function() {
		table().refresh();
	}

	var submit_handler = function(url) {
		return function (resource, response) {
			var id = response.id;

			if (!response.success) {
				$.notify(response.message, { type: 'danger' });
			} else {
				var message = response.message;
				var selector = rows_selector(response.id);
				var rows = $(selector.join(",")).length;
				if (rows > 0 && rows < 15) {
					var ids = response.id.split(":");
				    $.get({
						url: [url || resource + '/get_row', id].join("/"),
						success: function (response) {
							$.each(selector, function (index, element) {
								var id = $(element).data('uniqueid');
								table().updateByUniqueId({id: id, row: response[id] || response});
							});
							dialog_support.init("a.modal-dlg");
							highlight_row(ids);
						},
						dataType: 'json'
					});
				} else {
					// call hightlight function once after refresh
					options.load_callback = function () {
						enable_actions();
						highlight_row(id);
					};
					refresh();
				}
				$.notify(message, {type: 'success' });
			}
		};
	};

	var handle_submit = submit_handler();

	$.extend(table_support, {
		submit_handler: function(url) {
			this.handle_submit = submit_handler(url);
		},
		handle_submit: handle_submit,
		init: init,
		do_delete: do_delete,
		refresh : refresh,
		selected_ids : selected_ids,
	});

})(window.table_support = window.table_support || {}, jQuery);

(function(form_support, $) {

	form_support.error = {
		errorClass: "has-error",
		errorLabelContainer: "#error_message_box",
		wrapper: "li",
		highlight: function (e) {
			$(e).closest('.form-group').addClass('has-error');
		},
		unhighlight: function (e) {
			$(e).closest('.form-group').removeClass('has-error');
		}
	};

	form_support.handler = $.extend({

		submitHandler: function(form) {
			$(form).ajaxSubmit({
				success: function(response)
				{
					$.notify(response.message, { type: response.success ? 'success' : 'danger' });
				},
				dataType: 'json'
			});
		},

		rules:
		{

		},

		messages:
		{

		}
	}, form_support.error);

})(window.form_support = window.form_support || {}, jQuery);