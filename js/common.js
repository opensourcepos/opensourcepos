function set_feedback(text, classname, keep_displayed)
{
	if(text)
	{
		$('#feedback_bar').removeClass().addClass(classname).html(text).css('opacity','1');

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

;(function($){
	//keylisteners
	$.each(['customers', 'items', 'reports', 'receivings', 'sales'], function(key, value) {
		$(window).jkey('f' + (key+1), function(){
			window.location = BASE_URL + '/' + value + '/index';
		});	
	});
})(jQuery);
