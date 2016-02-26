function checkbox_click(event)
{
	event.stopPropagation();
	do_email(enable_email.url);
	if($(event.target).attr('checked'))
	{
		$(event.target).parent().parent().find("td").addClass('selected').css("backgroundColor","");		
	}
	else
	{
		$(event.target).parent().parent().find("td").removeClass();		
	}
}

function enable_search(options)
{
	if (!options.format_item) {
		format_item = function(results) {
			return results[0];
		};
	}
	//Keep track of enable_email has been called
	if(!enable_search.enabled)
		enable_search.enabled=true;

	$('#search').click(function()
    {
    	$(this).attr('value','');
    });

    var widget = $("#search").autocomplete(options.suggest_url,{max:100,delay:10, selectFirst: false,
		formatItem : options.format_item, extraParams: options.extra_params});
    $("#search").result(function(event, data, formatted)
    {
		do_search(true, options.on_complete);
    });
    
    attach_search_listener();
    
	$('#search_form').submit(function(event)
	{
		event.preventDefault();
        // reset page number when selecting a specific page number
		$('#limit_from').val(0);
		if(get_selected_values().length >0)
		{
			if(!confirm(options.confirm_search_message))
				return;
		}
		do_search(true, options.on_complete);
	});

	return widget;
}
enable_search.enabled=false;

function attach_search_listener() 
{
	 // prevent redirecting to link when search enabled
    $("#pagination a").click(function(event) {
    	  if ($("#search").val() || $("#search_form input:checked")) {
    		  event.preventDefault();
    		  // set limit_from to value included in the link
    		  var uri_segments = event.currentTarget.href.split('/');
    		  var limit_from = uri_segments.pop();
    		  $('#limit_from').val(limit_from);
    		  do_search(true);
    	  }
    });
}

function do_search(show_feedback,on_complete)
{	
	//If search is not enabled, don't do anything
	if(!enable_search.enabled)
		return;

	if(show_feedback)
		$('#search').addClass("ac_loading");
		
	$.post(
		$('#search_form').attr('action'), 
		// serialize all the input fields in the form
		$('#search_form').serialize(),
		function(response) {
			$('#sortable_table tbody').html(response.rows);
			if(typeof on_complete=='function')
				on_complete(response);
			$('#search').removeClass("ac_loading");
			//$('#spinner').hide();
			//re-init elements in new table, as table tbody children were replaced
			dialog_support.init('#sortable_table a.modal-dlg');
			$('#pagination').html(response.pagination);
			$('#sortable_table tbody :checkbox').click(checkbox_click);
			$("#select_all").attr('checked',false);
			if (response.total_rows > 0)
			{
				update_sortable_table();	
				enable_row_selection();	
			}
		    attach_search_listener();
		}, "json"
	);
}

function enable_email(email_url)
{
	//Keep track of enable_email has been called
	if(!enable_email.enabled)
		enable_email.enabled=true;

	//store url in function cache
	if(!enable_email.url)
	{
		enable_email.url=email_url;
	}
	
	$('#select_all, #sortable_table tbody :checkbox').click(checkbox_click);
}
enable_email.enabled=false;
enable_email.url=false;

function do_email(url)
{
	//If email is not enabled, don't do anything
	if(!enable_email.enabled)
		return;

	$.post(url, { 'ids[]': get_selected_values() },function(response)
	{
		$('#email').attr('href',response);
	});

}

function enable_checkboxes()
{
	$('#sortable_table tbody :checkbox').click(checkbox_click);
}

function enable_delete(confirm_message,none_selected_message)
{
	//Keep track of enable_delete has been called
	if(!enable_delete.enabled)
		enable_delete.enabled=true;
	
	$("#delete").click(function(event)
	{
		event.preventDefault();
		if($("#sortable_table tbody :checkbox:checked").length >0)
		{
			if(confirm(confirm_message))
			{
				do_delete($(this).attr('href'));
			} else {
				return false;
			}
		}
		else
		{
			alert(none_selected_message);
		}
	});
}
enable_delete.enabled=false;

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

function enable_select_all()
{
	//Keep track of enable_select_all has been called
	if(!enable_select_all.enabled)
		enable_select_all.enabled=true;

	$('#select_all').click(function()
	{
		if($(this).attr('checked'))
		{	
			$("#sortable_table tbody :checkbox").each(function()
			{
				$(this).attr('checked',true);
				$(this).parent().parent().find("td").addClass('selected').css("backgroundColor","");

			});
		}
		else
		{
			$("#sortable_table tbody :checkbox").each(function()
			{
				$(this).attr('checked',false);
				$(this).parent().parent().find("td").removeClass();				
			});    	
		}
	 });	
}
enable_select_all.enabled=false;

function enable_row_selection(rows)
{
	//Keep track of enable_row_selection has been called
	if(!enable_row_selection.enabled)
		enable_row_selection.enabled=true;
	
	if(typeof rows =="undefined")
		rows=$("#sortable_table tbody tr");
	
	rows.hover(
		function row_over()
		{
			$(this).find("td").addClass('over').css("backgroundColor","");
			$(this).css("cursor","pointer");
		},
		
		function row_out()
		{
			if(!$(this).find("td").hasClass("selected"))
			{
				$(this).find("td").removeClass();
			}
		}
	);
	
	rows.click(function row_click(event)
	{
		var checkbox = $(this).find(":checkbox");
		checkbox.attr('checked',!checkbox.attr('checked'));
		do_email(enable_email.url);
		
		if(checkbox.attr('checked'))
		{
			$(this).find("td").addClass('selected').css("backgroundColor","");
		}
		else
		{
			$(this).find("td").removeClass();
		}
	});
}
enable_row_selection.enabled=false;

function update_sortable_table()
{
	//let tablesorter know we changed <tbody> and then triger a resort
	$("#sortable_table").trigger("update");
	if(typeof $("#sortable_table")[0].config!="undefined")
	{
		var sorting = $("#sortable_table")[0].config.sortList; 		
		$("#sortable_table").trigger("sorton",[sorting]);
	}
	else
	{
		window['init_table_sorting'] && init_table_sorting();
	}
}

function get_table_row(id)
{
	id = id || $("input[name='sale_id']").val();
	var $element = $("#sortable_table tbody :checkbox[value='" + id + "']");
	if ($element.length === 0) {
		$element = $("#sortable_table tbody a[href*='/" + id + "/']");
	}
	return $element;
}

function update_row(row_id,url,callback)
{
	$.post(url, { 'row_id': row_id },function(response)
	{
		//Replace previous row
		var row_to_update = get_table_row(row_id).parent().parent();
		row_to_update.replaceWith(response);	
		reinit_row(row_id);
		hightlight_row(row_id);
		callback && typeof(callback) == "function" && callback(); 
	}, 'html');
}

function reinit_row(checkbox_id)
{
	var new_checkbox = $("#sortable_table tbody tr :checkbox[value="+checkbox_id+"]");
	var new_row = new_checkbox.parent().parent();
	enable_row_selection(new_row);
	//Re-init some stuff as we replaced row
	update_sortable_table();
	dialog_support.init(new_row.find("a.modal-dlg"));
	//re-enable e-mail
	new_checkbox.click(checkbox_click);	
}

function animate_row(row,color)
{
	color = color || "#e1ffdd";
	row.find("td").css("backgroundColor", "#ffffff").animate({backgroundColor:color},"slow","linear")
		.animate({backgroundColor:color},5000)
		.animate({backgroundColor:"#ffffff"},"slow","linear");
}

function hightlight_row(checkbox_id)
{
	var new_checkbox = $("#sortable_table tbody tr :checkbox[value="+checkbox_id+"]");
	var new_row = new_checkbox.parent().parent();
	
	animate_row(new_row);
}

function get_selected_values()
{
	var selected_values = new Array();
	$("#sortable_table tbody :checkbox:checked").each(function()
	{
		selected_values.push($(this).val());
	});
	return selected_values;
}

function get_selected_rows() 
{ 
	var selected_rows = new Array(); 
	$("#sortable_table tbody :checkbox:checked").each(function() 
	{ 
		selected_rows.push($(this).parent().parent()); 
	}); 
	return selected_rows; 
}

function get_visible_checkbox_ids()
{
	var row_ids = new Array();
	$("#sortable_table tbody :checkbox").each(function()
	{
		row_ids.push($(this).val());
	});
	return row_ids;
}

dialog_support = (function() {

	var btn_id, dialog_ref;

	var hide = function() {
		dialog_ref.close();
	};

	var clicked_id = function() {
		return btn_id;
	};

	var submit = function(button_id) {
		return function(dlog_ref)
		{
			btn_id = button_id;
			dialog_ref = dlog_ref;
			$('form', dlog_ref.$modalBody).submit();
		}
	};

	var init = function(selector) {
		return $(selector).click(function(event) {
			var buttons = [{
				id: 'submit',
				label: 'Submit',
				cssClass: 'btn-primary',
				action: submit('submit')
			}];

			var dialog_class = 'modal-dlg';
			$.each($(this).attr('class').split(/\s+/), function(classIndex, className) {
				var width_class = className.split("modal-dlg-");
				if (width_class && width_class.length > 1) {
					dialog_class = className;
				}

				var btn_class = className.split("modal-btn-");
				if (btn_class && btn_class.length > 1) {
					var btn_name = btn_class[1];
					buttons.push({
						id: btn_name,
						label: btn_name.charAt(0).toUpperCase() + btn_name.slice(1),
						action: submit(btn_name)
					});
				}
			});

			var $link = $(event.target);
			$link = $link.is("a") ? $link : $link.parents("a");
			BootstrapDialog.show({
				cssClass: dialog_class,
				title: $link.attr('title'),
				message: $('<div></div>').load($link.attr('href')),
				buttons: buttons
			});

			event.preventDefault();
		});
	};

	$(document).ready(function() {
		init("a.modal-dlg");
	});

	return {
		hide: hide,
		clicked_id: clicked_id,
		init: init,
		submit: submit
	};

})();

