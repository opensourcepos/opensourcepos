<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Item_lib
{
	private $CI;

  	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public function get_item_location()
	{
		if(!$this->CI->session->userdata('item_location'))
		{
			$location_id = $this->CI->Stock_location->get_default_location_id();
			$this->set_item_location($location_id);
		}

		return $this->CI->session->userdata('item_location');
	}

	public function set_item_location($location)
	{
		$this->CI->session->set_userdata('item_location',$location);
	}

	public function clear_item_location()
	{
		$this->CI->session->unset_userdata('item_location');
	}	
}

?>
