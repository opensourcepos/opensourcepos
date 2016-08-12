<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once('src/Racecore/GATracking/GATracking.php');

class Tracking_lib
{
	private $CI;
	private $tracking;

  	public function __construct()
	{
		$this->CI =& get_instance();
		
		/**
		 * Setup the class
		 * optional
		 */
		$options = array(
			'client_create_random_id' => TRUE, // create a random client id when the class can't fetch the current cliend id or none is provided by "client_id"
			'client_fallback_id' => 555, // fallback client id when cid was not found and random client id is off
			'client_id' => NULL, //substr($this->CI->config->item('commit_sha1'), 5, 40),    // override client id
			'user_id' => $_SERVER['SERVER_ADDR'],  // determine current user id
			// adapter options
			'adapter' => array(
				'async' => TRUE, // requests to google are async - don't wait for google server response
				'ssl' => FALSE // use ssl connection to google server
			)
		);

		try
		{
			$this->tracking = new \Racecore\GATracking\GATracking('UA-82359828-1', $options);
		}
		finally
		{
			
		}
	}
	
	/*
	 * Track Event function
	 */
	public function track_event($category, $action, $label = NULL, $value = NULL)
	{
		try
		{
			/** @var Tracking/Event $event */
			$event = $this->tracking->createTracking('Event');
			$event->setAsNonInteractionHit(TRUE);
			$event->setEventCategory($category);
			$event->setEventAction($action);
			$event->setEventLabel($label);
			$event->setEventValue($value);

			return $this->tracking->sendTracking($event);
		}
		finally
		{
			
		}
	}
	
	/*
	 * Track Page function
	 */
	public function track_page($path, $title, $description = NULL)
	{
		try
		{
			/** @var Tracking/Factory $event */
			$event = $this->tracking->createTracking('Factory', array(
				'an' => 'OSPOS',
				'av' => $this->CI->config->item('application_version') . ' - ' . substr($this->CI->config->item('commit_sha1'), 5, 12),
				'dh' => $_SERVER['SERVER_ADDR'],
				'dp' => $path,
				'dt' => $title,
				'cd' => $description			
			));

			return $this->tracking->sendTracking($event);
		}
		finally
		{
			
		}
	}
}

?>
