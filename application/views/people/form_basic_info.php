<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('common_first_name').':', 'first_name',array('class'=>'required')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'first_name',
		'id'=>'first_name',
		'value'=>$person_info->first_name)
	);?>
	</div>
</div>
<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('common_last_name').':', 'last_name',array('class'=>'required')); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'last_name',
		'id'=>'last_name',
		'value'=>$person_info->last_name)
	);?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('common_email').':', 'email'); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'email',
		'id'=>'email',
		'value'=>$person_info->email)
	);?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('common_phone_number').':', 'phone_number'); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'phone_number',
		'id'=>'phone_number',
		'value'=>$person_info->phone_number));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('common_address_1').':', 'address_1'); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'address_1',
		'id'=>'address_1',
		'value'=>$person_info->address_1));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('common_address_2').':', 'address_2'); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'address_2',
		'id'=>'address_2',
		'value'=>$person_info->address_2));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('common_city').':', 'city'); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'city',
		'id'=>'city',
		'value'=>$person_info->city));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('common_state').':', 'state'); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'state',
		'id'=>'state',
		'value'=>$person_info->state));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('common_zip').':', 'zip'); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'zip',
		'id'=>'postcode',
		'value'=>$person_info->zip));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('common_country').':', 'country'); ?>
	<div class='form_field'>
	<?php echo form_input(array(
		'name'=>'country',
		'id'=>'country',
		'value'=>$person_info->country));?>
	</div>
</div>

<div class="field_row clearfix">	
<?php echo form_label($this->lang->line('common_comments').':', 'comments'); ?>
	<div class='form_field'>
	<?php echo form_textarea(array(
		'name'=>'comments',
		'id'=>'comments',
		'value'=>$person_info->comments,
		'rows'=>'5',
		'cols'=>'17')		
	);?>
	</div>
</div>

<script type='text/javascript' language="javascript">
//validation and submit handling
$(document).ready(function()
{
		
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

	var request_params = function(id, key) 
	{
		return function() {
			var result = {
				 format: 'json',
	             limit: 5,
			     addressdetails: 1,
			     country: window['sessionStorage'] ? sessionStorage['country'] : ''
			};			
			result[key || id] = $("#"+id).val();
			return result;
		}

	};
	// TODO make endpoint configurable
	var url = http_s('nominatim.openstreetmap.org/search');
	var handle_city_completion = handle_auto_completion(["postcode", "city", "state", "country"]);
	$("#postcode").autocomplete(url,{
		max:100,
		minChars:3,
		delay:500,
		formatItem: set_field_values,
		type: 'GET',
		dataType:'json',
		extraParams: request_params("postcode", "postalcode"),
		parse: create_parser('postcode', ["postcode", "village|city_district|town|hamlet|city|county", "state", "country"])
	});
    $("#postcode").result(handle_city_completion);

	$("#city").autocomplete(url,{
		max:100,
		minChars:2,
		delay:500,
		formatItem: set_field_values,
		type: 'GET',
		dataType:'json',
		extraParams: request_params("city"),
		parse: create_parser('city', ["postcode", "village|city_district|town|hamlet|city|county", "state", "country"])
	});
   	$("#city").result(handle_city_completion);

	$("#state").autocomplete(url, {
		max:100, 
		minChars:2, 
		delay:500,
		type: 'GET',
		dataType:'json',
		extraParams: request_params("state"),
		parse: create_parser('state', ["state", "country"]),
	});
	$("#state").result(handle_auto_completion(["state", "country"]));

	$("#country").autocomplete(url,{
		max:100,
		minChars:2,
		delay:500,
		type: 'GET',
		dataType:'json',
		extraParams: request_params("country"),
		parse: create_parser('country', ["country"]), 
	});
	$("#country").result(handle_auto_completion(["country"]));

});
</script>