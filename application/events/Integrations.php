<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Integrations
 *
 * This Class contains all functions pertaining to Third-Party Integrations
 *
 * @package		CodeIgniter
 */

/**
 * Integrations Library
 */
class Integrations
{
	private $CI;
	
	/**
	 *	Registers listeners to be kicked off on trigger
	 */
	public function __construct()
	{
		//Third-Party Integrations by adding all code related to the integration into a library and referencing the library here with $this->ci->load->library();
		$this->CI =& get_instance();
		//$this->CI->load->library('LIB_NAME_HERE');
		
		Events::register('event_create', array($this,'integrations_create'));
		Events::register('event_read', array($this,'integrations_read'));
		Events::register('event_update', array($this,'integrations_update'));
		Events::register('event_delete', array($this,'integrations_delete'));
	}
	
	/**
	 * Event trigger for integrations on Create CRUD operation
	 *
	 * @param	array		$data	Data to be made available to Third Party Integrations separated into "type" which is the type of data being sent (ITEMS, ITEM_KITS, CUSTOMERS, SUPPLIERS and GIFTCARDS) and "data"
	 * @return	string|NULL			NULL is returned on a successful completion of integration tasks or the integration and failure message
	 */
	public function integrations_create(array $data)
	{
		$results = NULL;
		
		//calls to create functions for the different Integrations
		switch($data['type'])
		{
			case 'ITEMS':
				//$results = $this->CI->LIB_NAME->METHOD_NAME();
				break;
				
			case 'ITEM_KITS':
				break;
				
			case 'CUSTOMERS':
				break;
				
			case 'SUPPLIERS':
				break;
				
			case 'GIFTCARDS':
				break;
				
			default:
				$results = 'Improper integrations create type';
				break;
		}
		
		return $results;
	}
	
	/**
	 * Event trigger for integrations on Read CRUD operation
	 *
	 * @param	array		$data	Data to be made available to Third Party Integrations separated into "type" which is the type of data being sent (ITEMS, ITEM_KITS, CUSTOMERS, SUPPLIERS, GIFTCARDS, RECEIVINGS, and SALES) and "data"
	 * @return	string|NULL			NULL is returned on a successful completion of integration tasks or the integration and failure message
	 */
	public function integrations_read(array $data)
	{
		//Currently no triggers are established for Read operations.  It's unclear whether we will use these in integrations
		$results = NULL;
		
		//calls to read functions for the different Integrations
		switch($data['type'])
		{
			default:
				$results = 'Improper integrations read type';
				break;
		}
		
		return $results;
	}
	
	/**
	 * Event trigger for integrations on Update CRUD operation
	 *
	 * @param	array		$data	Data to be made available to Third Party Integrations separated into "type" which is the type of data being sent (ITEMS, ITEM_KITS, CUSTOMERS, SUPPLIERS, GIFTCARDS, RECEIVINGS, and SALES) and "data"
	 * @return	string|NULL			NULL is returned on a successful completion of integration tasks or the integration and failure message
	 */
	public function integrations_update(array $data)
	{
		$results = NULL;
		
		//calls to update functions for the different Integrations
		switch($data['type'])
		{
			case 'ITEMS':
				break;
				
			case 'ITEM_KITS':
				break;
				
			case 'CUSTOMERS':
				break;
				
			case 'SUPPLIERS':
				break;
				
			case 'RECEIVINGS':
				break;
				
			case 'SALES':
				break;
				
			case 'GIFTCARDS':
				break;
				
			default:
				$results = 'Improper integrations update type.';
				break;
		}
		
		return $results;
	}
	
	/**
	 * Event trigger for integrations on Delete CRUD operation
	 *
	 * @param	array		$data	Data to be made available to Third Party Integrations separated into "type" which is the type of data being sent (ITEMS, ITEM_KITS, CUSTOMERS, SUPPLIERS and GIFTCARDS) and "data"
	 * @return	string|NULL			NULL is returned on a successful completion of integration tasks or the integration and failure message
	 */
	public function integrations_delete(array $data)
	{
		$results = NULL;
		
		//calls to delete functions for the different Integrations
		switch($data['type'])
		{
			case 'ITEMS':
				break;
				
			case 'ITEM_KITS':
				break;
				
			case 'CUSTOMERS':
				break;
				
			case 'SUPPLIERS':
				break;
				
			case 'GIFTCARDS':
				break;
				
			default:
				$results = 'Improper integrations delete type.';
				break;
		}
		
		return $results;
	}
}