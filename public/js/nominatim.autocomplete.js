(function($) {

    function http_s(url)
    {
        return document.location.protocol + '//' + url;
    }

    const url = http_s('nominatim.openstreetmap.org/search');

    const handle_auto_completion = function(fields) {
        return function(event, ui) {
            const results = ui.item.results;
            if (results != null && results.length > 0) {
                // Handle auto completion
                for(const i in fields) {
                    $("#" + fields[i]).val(results[i]);
                }
                return false;
            }
            return true;
        };
    };

    const create_parser = function(field_name, parse_format)
    {
        const parse_field = function(format, address)
        {
            const fields = [];
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
            const parsed = [];
            $.each(data, function(index, value)
            {
                const row = [];
                const address = value.address;
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

    const init = function(options) {

        const default_params = function(id, key, language)
        {
            return function() {
                const result = {
                    format: 'json',
                    limit: 5,
                    addressdetails: 1,
                    countrycodes: options.country_codes,
                    'accept-language' : language || navigator.language
                };
                result[key || id] = $("#"+id).val();
                return result;
            }

        };

        const unique = function(parsed) {
            let filtered = [];
            $.each(parsed, function(index, element)
            {
                filtered = $.map(filtered, function(el, ind)
                {
                    return el.label == element.label ? null : el;
                });
                filtered.push(element);

            });
            return filtered;
        };

        $.each(options.fields, function(key, value)
        {
            const handle_field_completion = handle_auto_completion(value.dependencies);

            $("#" + key).autocomplete({
                source: function (request, response) {
                    const params = default_params(key, value.response && value.response.field, options.language);
                    const request_params = {};
                    options.extra_params && $.each(options.extra_params, function(key, param) {
                        request_params[key] = typeof param == "function" ? param() : param;
                    });

                    $.ajax({
                        type: "GET",
                        url: url,
                        dataType: "json",
                        data: $.extend(request_params, params()),
                        success: function(data) {
                            response(unique($.map(data, function(item) {
                                return (create_parser(key, (value.response && value.response.format) || value.dependencies))(data)
                            })))
                        }
                    });
                },
                minChars:3,
                delay:1000,
                appendTo: '.modal-content',
                select: handle_field_completion
            });

        });
    };

    const nominatim = {

        init : init

    };

    window['nominatim'] = nominatim;

})(jQuery);