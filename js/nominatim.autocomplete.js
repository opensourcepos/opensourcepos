(function($) {
	
	function http_s(url)
	{
		return document.location.protocol + '//' + url;
	}
	
	if (window.sessionStorage && !sessionStorage['country'])
	{
		/*$.ajax({
			type: "GET",
			url: http_s('ipinfo.io/json'),
			success: function(response) {
				sessionStorage['country'] = response.country;
			}, dataType: 'jsonp'
		})*/;
	}
	
	var url = http_s('nominatim.openstreetmap.org/search');

	var handle_auto_completion = function(fields) {
		return function(event, ui) {
			var results = ui.item.results;
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
				var row = [];
				var address = value.address;
                $.each(parse_format, function(key, format)
                {
                    row.push(parse_field(format, address));
                });
                parsed[index] = {
        	        label: row.join(", "),
					results: row,
    	            value: address[field_name]
                };
            });
            return parsed;
		};
	};

	var init = function(options) {

		var default_params = function(id, key, language)
		{
			return function() {
				var result = {
					format: 'json',
					limit: 5,
					addressdetails: 1,
					countrycodes: window['sessionStorage'] ? sessionStorage['country'] : 'be',
					'accept-language' : language || navigator.language
				};
				result[key || id] = $("#"+id).val();
				return result;
			}

		};

		$.each(options.fields, function(key, value)
		{
			var handle_field_completion = handle_auto_completion(value.dependencies);

			$("#" + key).autocomplete({
				source: function (request, response) {
					var params = default_params(key, value.response && value.response.field, options.language);
					var request_params = {q: request.term};
					$.each(options.extra_params, function(key, param) {
						request_params[key] = typeof param == "function" ? param() : param;
					});

					$.ajax({
						type: "GET",
						url: url,
						dataType: "json",
						data: $.extend(request_params, params()),
						success: function(data) {
							response($.map(data, function(item) {
								return (create_parser(key, (value.response && value.response.format) || value.dependencies))(data)
							}))
						}
					});
				},
				minChars:3,
				delay:500,
				appendTo: '.modal-content',
				select: handle_field_completion
			});

		});
	};
	
	var nominatim = {
			
		init : init
	
	};
	
	window['nominatim'] = nominatim;
	
})(jQuery);