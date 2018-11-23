(function($) {
    var serial_ports = [], baud_rates = [];
    var settings = (localStorage['serial_settings'] && JSON.parse(localStorage['serial_settings'])) || {};
    var default_baud_rate = 9600;

    var load_baud_rate = function () {
        var id = $(".input_device_check:checked").attr('id');
        var serial_port = settings[id];
        var baud_rate_index = serial_port && serial_port.baud_rate_index;
        $("#baud_rate_picker").val(baud_rate_index);
    };

    var enable_disable_units = function () {
        var selected = $(".input_device_check").is(":checked")
        // select max. 2 inputs
        var oversized = $(settings).size() > 2;
        $(".input_device_check").each(function () {
            $(this).prop("disabled", oversized || (selected && !$(this).is(":checked")));
        });
    };

    var load_serial_device = function() {
        $.each(settings, function(index, element) {
            var device_index = element.device_index;
            $("#input_device_picker").val(device_index);
            return !element.device_index;
        });
    };

    var load_configured_device = function() {
        load_serial_device();
        load_selected_units();
        enable_disable_units();
        load_baud_rate();
    };

    var load_selected_units = function () {
        $.each(settings, function(index, element)
        {
            var value = $("#input_device_picker option:selected").val();
            var checked =  element && element.device_index === value;
            $("#"+index).prop("checked", checked);
        });
    };

    var load_serial_settings = function() {
        load_selected_units();
        // if at least one checked, disable others!!
        enable_disable_units();
        load_baud_rate();
    };

    var save_serial_settings = function()
    {
        var device_index = $("#input_device_picker").val();
        var id = $(".input_device_check:checked").attr('id');
        // delete if unselected
        if (!id)
        {
            $(".input_device_check").each(function(index) {
                var id = $(this).attr('id');
                var serial_setting = settings[id];
                serial_setting && serial_setting.device_index ===
                    $("#input_device_picker option:selected").val() && delete settings[id];
                var text = $("#input_device_picker option:selected").text().replace(/\s\*/g, '');
                $("#input_device_picker option:selected").text(text);
            });
        } else {
            // find serial device settings
            settings[id] = serial_ports[device_index];
            // save baud rate
            var baud_rate_index = $("#baud_rate_picker").val();
            settings[id].baud_rate_index = baud_rate_index;
            // save device index
            settings[id].device_index = device_index;
            // mark device text
            var text = $("#input_device_picker option:selected").text();
            !text.match(/\*$/g) && $("#input_device_picker option:selected").text(text + " *");
        }
        // write to localStorage
        localStorage['serial_settings'] = JSON.stringify(settings);
        enable_disable_units();
    };

    var load_url = function () {
        var url = settings.url || $("#serialport_server_url").val();
        $("serialport_server_url").val(url);
        return url;
    };

    var save_url = function() {
        var url = $("#serialport_server_url").val();
        settings.url = url;
        localStorage['serial_settings'] = JSON.stringify(settings);
        return url;
    };

    var parse_baud_rates = function(nbaud_rates)
    {
        baud_rates = nbaud_rates;
        $("#baud_rate_picker").empty();
        $.each(baud_rates, function(index, baud_rate) {
            $("#baud_rate_picker").append($("<option>", {value: index}).text(baud_rate));
        });
        load_baud_rate();
    };

    var parse_ports = function(nserial_ports)
    {
        serial_ports = nserial_ports;
        $("#input_device_picker").empty().parents(".field_row").removeClass("hidden").addClass("clearfix");
        $.each(serial_ports, function(index, serial_port) {
            var configured = '';
            $.each(settings, function(device_index, element) {
                if (index == element.device_index) {
                    configured = ' *';
                }
            });
            $("#input_device_picker").append($("<option>", {value: index}).text(serial_port.Name + configured));
        });
    };

    var get_settings = function(index)
    {
        return settings[index];
    };

    var fill_field = function(message, serial_port, index)
    {
        var object = jQuery.parseJSON(message.data);
        if (object.P === serial_port.Name)
        {
            var re = /.*?(\d+\.\d+).*?/ig;
            var result = re.exec(object.D.trim());
            var float = parseFloat(result[1]);
            if ($("." + index).is(":focus"))
            {
                return $("." + index + ":focus").val(float);
            }
            return $("." + index).first().val(float);
        }
    };

    var enable_fields = function()
    {
        $("input[class*='input_device']").each(function(index, element) {
            $.each($(element).attr("class").split(" "), function(index, className) {
                if (serial_config.get_settings(className))
                {
                    $(element).prop("readonly", true);
                    $(element).val() == "0" && $("#input_device_warning").removeClass("hidden");
                }
            });
        });
    };

    var bind_keys = function ()
    {
        var keys = [8, 9], key = 0;
        for (var index in settings)
        {
            if (typeof settings[index] == 'string')
            {
                continue;
            }
            // just attach F8 and F9 to first two serial devices found
            (function(key, index) {
                $(window).jkey('f' + key, function() {
                    var serial_port = settings[index];
                    var websocket = new WebSocket(settings.url);
                    websocket.onopen = function (data) {
                        var index = serial_port.baud_rate_index;
                        var baud_rate = baud_rates[index];
                        baud_rate = baud_rate ? baud_rate : default_baud_rate;
                        websocket.send("open " + serial_port.Name + " " + baud_rate);
                    };
                    var field_to_fill;
                    websocket.onmessage = function (message) {
                        console.log(message.data);
                        if (!field_to_fill && message.data.match(/\"D\":.*?(\d+\.\d+).*?/g))
                        {
                            field_to_fill = fill_field(message, serial_port, index);
                            websocket.send("close " + serial_port.Name);
                            var form_id = field_to_fill.parents("tr").index();
                            $("#edit_form_" + form_id).submit();
                        }
                    }
                });
            })(keys[key++], index);
        }
        enable_fields();
    };

    window.serial_config = {
        bind_keys : bind_keys,
        load_url : load_url,
        save_url : save_url,
        save_serial_settings : save_serial_settings,
        load_serial_settings : load_serial_settings,
        parse_baud_rates : parse_baud_rates,
        parse_ports : parse_ports,
        get_settings : get_settings,
        load_configured_device : load_configured_device
    };

})(jQuery);
