function get_dimensions() 
{
	var dims = {width:0,height:0};
	
  if( typeof( window.innerWidth ) == 'number' ) {
    //Non-IE
    dims.width = window.innerWidth;
    dims.height = window.innerHeight;
  } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
    //IE 6+ in 'standards compliant mode'
    dims.width = document.documentElement.clientWidth;
    dims.height = document.documentElement.clientHeight;
  } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
    //IE 4 compatible
    dims.width = document.body.clientWidth;
    dims.height = document.body.clientHeight;
  }
  
  return dims;
}

function set_feedback(text, classname, keep_displayed)
{
	if(text)
	{
		$('#feedback_bar').removeClass().addClass(classname).text(text).css('opacity','1');

		if(!keep_displayed)
		{
			$('#feedback_bar').fadeTo(5000, 1).fadeTo("fast",0);
		}
	}
	else
	{
		$('#feedback_bar').css('opacity','0');
	}
}

function http_s(url)
{
	return document.location.protocol + '//' + url;
}

//keylisteners
$.each(['customers', 'items', 'reports', 'receivings', 'sales', 'employees', 'config', 'giftcards'], function(key, value) {
	$(window).jkey('f' + (key+1), function(){
		window.location = BASE_URL + '/' + value + ' /index';
	});	
});

function handle_validation(response) 
{
	if (!response.success && !response.validated) 
	{
		var error_message_box = '.error_message_box';
		// server side validation failed.. record won't be saved
		$(error_message_box).empty();
		for(var index in response.error_messages) 
		{
			// get validation messages from array and show those to the user
			var message = response.error_messages[index];
			$(error_message_box).append("<li>" + message +  "</li>").css("display", "");
		}
		return false;
	}
	return true;
}
