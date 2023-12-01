<?php

namespace app\Libraries;

use CodeIgniter\Encryption\Encryption;
use CodeIgniter\Encryption\EncrypterInterface;
use Config\OSPOS;
use Config\Services;


/**
 * SMS library
 *
 * Library with utilities to send texts via SMS Gateway (requires proxy implementation)
 */

class Sms_lib
{
	/**
	 * SMS sending function
	 * Example of use: $response = sendSMS('4477777777', 'My test message');
	 **/
	public function sendSMS(int $phone, string $message): bool
	{
		$config = config(OSPOS::class)->settings;

		$encrypter = Services::encrypter();

		$password = $config['msg_pwd'];
		if(!empty($password))
		{
			$password = $encrypter->decrypt($password);
		}

		$username = $config['msg_uid'];
		$originator = $config['msg_src'];

		$response = FALSE;

		// if any of the parameters is empty return with a FALSE
		if(empty($username) || empty($password) || empty($phone) || empty($message) || empty($originator))	//TODO: This if/else needs to be flipped. and shortened.  No else needed in the code example below.
			//$parameters = [$username, $password, $phone, $message, $originator];
			//if(count(array_filter($parameters)) === 5)
			//{
			//	$response = TRUE;
			//	$message = rawurlencode($message);
			//}
		{
			//echo $username . ' ' . $password . ' ' . $phone . ' ' . $message . ' ' . $originator;
		}
		else
		{
			$response = TRUE;
//TODO: These comments should be moved to the documentation.  As is, they tend to get out of date.
			// make sure passed string is url encoded
			$message = rawurlencode($message);	//TODO: $message needs to be passed by reference if you want this line to actually do anything

			// add call to send a message via 3rd party API here
			// Some examples

			/*
			$url = "http://xxx.xxx.xxx.xxx/send_sms?username=$username&password=$password&src=$originator&dst=$phone&msg=$message&dr=1";

			$c = curl_init();
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_URL, $url);
			$response = curl_exec($c);
			curl_close($c);
			*/

			// This is a textmarketer.co.uk API call, see: http://wiki.textmarketer.co.uk/display/DevDoc/Text+Marketer+Developer+Documentation+-+Wiki+Home
			/*
			$url = 'https://api.textmarketer.co.uk/gateway/'."?username=$username&password=$password&option=xml";
			$url .= "&to=$phone&message=".urlencode($message).'&orig='.urlencode($originator);
			$fp = fopen($url, 'r');
			$response = fread($fp, 1024);
			*/
		}

		return $response;
	}
}
