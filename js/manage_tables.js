
function do_delete(url)
{
	//If delete is not enabled, don't do anything
	if(!enable_delete.enabled)
		return;
	
	var row_ids = get_selected_values();
	var selected_rows = get_selected_rows();
	$.post(url, { 'ids[]': row_ids },function(response)
	{
		//delete was successful, remove checkbox rows
		if(response.success)
		{
			$(selected_rows).each(function(index, dom)
			{
				$(this).find("td").animate({backgroundColor:"green"},1200,"linear")
				.end().animate({opacity:0},1200,"linear",function()
				{
					$(this).remove();
					//Re-init sortable table as we removed a row
					$("#sortable_table tbody tr").length > 0 && update_sortable_table();
					
				});
			});
			
			set_feedback(response.message, 'alert alert-dismissible alert-success', false);	
		}
		else
		{
			set_feedback(response.message, 'alert alert-dismissible alert-danger', true);	
		}
	},"json");
}

function enable_bulk_edit(none_selected_message)
{
	//Keep track of enable_bulk_edit has been called
	if(!enable_bulk_edit.enabled)
		enable_bulk_edit.enabled=true;
	
	$('#bulk_edit').click(function(event)
	{
		if($("#sortable_table tbody :checkbox:checked").length == 0)
		{
			alert(none_selected_message);
			return false;
		}
		event.preventDefault();
	});
}
enable_bulk_edit.enabled=false;


dialog_support = (function() {

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

			if (button_id == 'delete')	{
				$("form[id*='delete_form']").submit();
			} else {
				$('form', dlog_ref.$modalBody).first().submit();
			}
		}
	};

	var init = function(selector) {
		$(selector).each(function(index, $element) {
			return $(selector).off('click').on('click', function(event) {
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
							cssClass: is_submit ? 'btn-primary' : (btn_name == 'delete' ? 'btn-danger' : ''),
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

				var $link = $(event.target);
				$link = $link.is("a") ? $link : $link.parents("a");
				BootstrapDialog.show({
					cssClass: dialog_class,
					title: $link.attr('title'),
					buttons: buttons,
					message: (function() {
						var node = $('<div></div>');
						$.get($link.attr('href'), function(data) {
							node.html(data);
						});
						return node;
					})
				});

				event.preventDefault();
			});
		});
	};

	$(document).ajaxComplete(function() {
		init("a.modal-dlg");
	});

	return {
		hide: hide,
		clicked_id: clicked_id,
		init: init,
		submit: submit,
		error: {
			errorClass: "has-error",
			errorLabelContainer: "#error_message_box",
			wrapper: "li",
			highlight: function (e) {
				$(e).closest('.form-group').addClass('has-error');
			},
			unhighlight: function (e) {
				$(e).closest('.form-group').removeClass('has-error');
			}
		}
	};

})();

table_support = (function() {

	var init_autocomplete = function() {

		var widget = $("#search").autocomplete({
			source: function (request, response) {
				var extra_params = {limit: 100};
				$.each(options.extra_params, function(key, param) {
					extra_params[key] = typeof param == "function" ? param() : param;
				});

				$.ajax({
					type: "POST",
					url: options.suggest_url,
					dataType: "json",
					data: $.extend(request, extra_params),
					success: function(data) {
						response($.map(data, function(item) {
							return {
								value: item.label,
							};
						}))}
				});
			},
			delay:10,
			autoFocus: false,
			select: function (a, ui) {
				$(this).val(ui.item.value);
				do_search(true, options.on_complete);
			}
		});
	};

	var highlight_rows = function(id, color) {
		var original = $("tr.selected").css('backgroundColor');
		var selector = ((id && "tr[data-uniqueid='" + id + "']")) || "tr.selected";
		$(selector).removeClass("selected").animate({backgroundColor:color||'#e1ffdd'},"slow","linear")
			.animate({backgroundColor:color||'#e1ffdd'},5000)
			.animate({backgroundColor:original},"slow","linear");
		$("tr input:checkbox:checked").prop("checked", false);
	};

	return {

		init: function(resource, headers) {
			$('#table').bootstrapTable({
				columns: headers,
				url: resource + '/search',
				sidePagination: 'server',
				striped: true,
				pagination: true,
				search: true,
				showColumns: true,
				clickToSelect: true,
				toolbar: '#toolbar',
				uniqueId: 'id'
			});
		},

		handle_submit : function (resource, response) {
			var $table = $("#table").data('bootstrap.table');
			var id = response.id;

			if(!response.success) {
				set_feedback(response.message, 'alert alert-dismissible alert-danger', true);
			} else {
				//This is an update, just update one row
				var message = response.message;
				var selected_ids = $.map($table.getSelections(), function(element) {
					return element.id;
				});

				if(jQuery.inArray(id, selected_ids) != -1) {
					$.get(resource + '/get_row/' + id, function(response)
					{
						$table.updateByUniqueId({id: id, row: response});
						highlight_rows();
						set_feedback(message, 'alert alert-dismissible alert-success', false);
					});
				} else {
					$table.refresh();
					hightlight_rows(response.id);
					set_feedback(message, 'alert alert-dismissible alert-success', false);
				}
			}
		}
	}

})();
