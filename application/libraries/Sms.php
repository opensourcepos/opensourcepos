<?php
 
class Sms
{
	/*
	 * SMS send function
	 * Example of use: $response = sendSMS('myUsername', 'myPassword', '4477777777', 'My test message', 'TextMessage');
	 */
	function sendSMS($username, $password, $phone, $message, $originator)
	{
		if( empty($username) || empty($password) || empty($phone) || empty($message) || empty($originator) )
		{
			//echo $username . ' ' . $password . ' ' . $phone . ' ' . $message . ' ' . $originator;

			return FALSE;
		}

		// add call to send a message via 3rd party API here
		
		// EXAMPLE OR URL LINK
		/*
    		$url="https://xxx.xxx.xxx.xxx/send_sms?username=$uid&password=$pwd&src=$src&dst=$phone&msg=$msg&dr=1";
		 
		$c = curl_init(); 
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($c, CURLOPT_URL, $url); 
		$response = curl_exec($c); 
		curl_close($c); 
		*/
		
		// This is a textmarketer.co.uk API call example see: http://wiki.textmarketer.co.uk/display/DevDoc/Text+Marketer+Developer+Documentation+-+Wiki+Home
		/*
		$url = 'https://api.textmarketer.co.uk/gateway/'."?username=$username&password=$password&option=xml";
		$url .= "&to=$phone&message=".urlencode($message).'&orig='.urlencode($originator);
		$fp = fopen($url, 'r');
		return fread($fp, 1024);
		*/

		return TRUE;
	}
}
?>
