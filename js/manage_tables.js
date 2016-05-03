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
				var btn_class = className.split("modal-btn-");
				if (btn_class && btn_class.length > 1) {
					var btn_name = btn_class[1];
					var is_submit = btn_name == 'submit';
					buttons.push({
						id: btn_name,
						label: btn_name.charAt(0).toUpperCase() + btn_name.slice(1),
						cssClass: button_class[btn_name],
						hotkey: is_submit ? 13 : undefined, // Enter.
						action: submit(btn_name)
					});
				}
			});

			!buttons.length && buttons.push({
				id: 'close',
				label: 'Close',
				cssClass: 'btn-primary',
				action: function(dialog_ref) {
					dialog_ref.close();
				}
			});
			return { buttons: buttons, cssClass: dialog_class};
		};

		$(selector).each(function(index, $element) {

			return $(selector).off('click').on('click', function(event) {
				var $link = $(event.target);
				$link = !$link.is("a, button") ? $link.parents("a") : $link ;
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

	dialog_support.error = {
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
			return element.id;
		});
	};

	var selected_rows = function () {
		return $("#table input:checkbox:checked").parents("tr");
	};

	var row_selector = function(id) {
		return "tr[data-uniqueid='" + id + "']";
	};

	var rows_selector = function(ids) {
		var selectors = [];
		ids = ids instanceof Array ? ids : ("" + ids).split(",");
		$.each(ids, function(index, element) {
			selectors.push(row_selector(element));
		});
		return selectors;;
	};

	var highlight_row = function (id, color) {
		var original = $(row_selector(id)).css('backgroundColor');
		$(row_selector(id)).find("td").animate({backgroundColor: color || '#e1ffdd'}, "slow", "linear")
			.animate({backgroundColor: color || '#e1ffdd'}, 5000)
			.animate({backgroundColor: original}, "slow", "linear");
	};

	var do_delete = function () {
		if (confirm(options.confirmDeleteMessage)) {
			$.post(options.resource + '/delete', {'ids[]': selected_ids()}, function (response) {
				//delete was successful, remove checkbox rows
				if (response.success) {
					$(selected_rows()).each(function (index, element) {
						$(this).find("td").animate({backgroundColor: "green"}, 1200, "linear")
							.end().animate({opacity: 0}, 1200, "linear", function () {
								table().remove({
									field: 'id',
									values: selected_ids()
								});
								enable_actions();
							});
					});
					set_feedback(response.message, 'alert alert-dismissible alert-success', false);
				} else {
					set_feedback(response.message, 'alert alert-dismissible alert-danger', true);
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
			dialog_support.init("a.modal-dlg, button.modal-dlg");
			typeof callback == 'function' && callback.call(this, response);
		}
	};

	var options;

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
			search: true,
			showColumns: true,
			clickToSelect: true,
			toolbar: '#toolbar',
			uniqueId: 'id',
			onCheck: enable_actions,
			onUncheck: enable_actions,
			onCheckAll: enable_actions,
			onUncheckAll: enable_actions,
			onLoadSuccess: load_success(options.onLoadSuccess),
			queryParamsType: 'limit',
			iconSize: 'sm',
			silentSort: true,
			paginationVAlign: 'bottom'
		}));
		enable_actions();
		init_delete();
	};

	var init_delete = function (confirmMessage) {
		$("#delete").click(function (event) {
			do_delete();
		});
	};

	var refresh = function() {
		table().refresh();
	}

	var handle_submit = function (resource, response) {
		var id = response.id;

		if (!response.success) {
			set_feedback(response.message, 'alert alert-dismissible alert-danger', true);
		} else {
			var message = response.message;
			var selector = rows_selector(response.id);
			if ($(selector.join(",")).length > 0) {
				$.each(selector, function(index, element) {
					var id = $(element).data('uniqueid');
					$.get({
						url: resource + '/get_row/' + id,
						success: function (response) {
							table().updateByUniqueId({id: id, row: response});
							dialog_support.init("a.modal-dlg");
							enable_actions();
							highlight_row(id);
						},
						dataType: 'json'
					});
				});
			} else {
				// call hightlight function once after refresh
				options.load_callback = function()  {
					enable_actions();
					highlight_row(id);
				};
				refresh();
			}
			set_feedback(message, 'alert alert-dismissible alert-success', false);
		}
	};

	$.extend(table_support, {
		handle_submit: handle_submit,
		init: init,
		do_delete: do_delete,
		refresh : refresh,
		selected_ids : selected_ids
	});

})(window.table_support = window.table_support || {}, jQuery);