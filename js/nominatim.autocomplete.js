(function($) {
	
	function http_s(url)
	{
		return document.location.protocol + '//' + url;
	}
	
	if (window.sessionStorage && !sessionStorage['country'])
	{
		$.ajax({
			type: "GET",
			url: http_s('ipinfo.io/json'),
			success: function(response) {
				sessionStorage['country'] = response.country;
			}, dataType: 'jsonp'
		});
	}
	
	var url = http_s('nominatim.openstreetmap.org/search');

	var handle_auto_completion = function(fields) {
		return function(event, results, formatted) {
			if (results != null && results.length > 0) {
				// handle auto completion
				for(var i in fields) {
					$("#" + fields[i]).val(results[i]);
				}
		        return false;
			}
			return true;
		};
	};

	var set_field_values = function(results) {
		return results[0] + ' - ' + results[1];
	};

	var create_parser = function(field_name, parse_format)
	{
		var parse_field = function(format, address) 
		{
			var fields = [];
			$.each(format.split("|"), function(key, value)
           	{
                if (address[value] && fields.length < 2 && $.inArray(address[value], fields) === -1)
                {
	                fields.push(address[value]);
                }
       	    });
       	    return fields[0] + (fields[1] ? ' (' + fields[1] + ')' : '');
		};
		
		return function(data)
		{
            var parsed = [];
            $.each(data, function(index, value)
            {
                var address = value.address;
                var row = [];
                $.each(parse_format, function(key, format)
                {
                    row.push(parse_field(format, address));
                });
                parsed[index] = {
        	        data: row,
    	            value: address[field_name],
    	            result: address[field_name]
                };
            });
            return parsed;
		};
	};

	var request_params = function(id, key, language) 
	{
		return function() {
			var result = {
				 format: 'json',
	             limit: 5,
			     addressdetails: 1,
			     country: window['sessionStorage'] ? sessionStorage['country'] : 'be',
			     'accept-language' : language || navigator.language
			};			
			result[key || id] = $("#"+id).val();
			return result;
		}

	};
	
	var nominatim = {
			
		init : function(options) {
			
			$.each(options.fields, function(key, value)
			{
				var handle_field_completion = handle_auto_completion(value.dependencies);


				$("#" + key).autocomplete({
					source: function (request, response) {
						var extra_params = request_params(key, value.response && value.response.field, options.language);
						$.each(options.extra_params, function(key, param) {
							extra_params[key] = typeof param == "function" ? param() : param;
						});

						$.ajax({
							type: "GET",
							url: url,
							dataType: "json",
							data: $.extend(request, extra_params),
							success: function(data) {
								response($.map(data, function(item) {
									return {
										value: item.label
									};
								}))
							}
						});
					},
					minChars:3,
					delay:500,
					formatItem: set_field_values,
					parse: create_parser(key, (value.response && value.response.format) || value.dependencies),
					result: function(a, ui) {
						$("#" + ui.item.value).result(handle_field_completion);
					}
				});

			});
		}
	
	};
	
	window['nominatim'] = nominatim;
	
})(jQuery);