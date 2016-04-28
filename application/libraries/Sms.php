<?php
 
class Sms
{

  	function sendsms($uid, $pwd, $src, $phone, $msg)
  		{		
		 
   		//SMS Gateway API Link (Will be provided by your SMS Service Provider)//  
		
    		$url="http://xxx.xxx.xxx.xxx/send_sms?username=$uid&password=$pwd&src=$src&dst=$phone&msg=$msg&dr=1"; // EXAMPLE OF URL LINK //
		 
		$c = curl_init(); 
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($c, CURLOPT_URL, $url); 
		$response = curl_exec($c); 
		curl_close($c); 
		
		echo "Message Sent Successfully";

 	 	}
}
?>
