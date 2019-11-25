<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Events
 *
 * A simple events system for CodeIgniter.
 *
 * @package		CodeIgniter
 * @subpackage	Events
 * @version		1.0
 * @author		Eric Barnes <http://ericlbarnes.com>
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @license		Apache License v2.0
 * @copyright	2010 Dan Horrigan
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Events Library
 */
class Events 
{
	/**
	 * @var	array	An array of listeners
	 */
	protected static $_listeners = array();
	
	/**
	 * Register
	 *
	 * Registers a Callback for a given event
	 *
	 * @access	public
	 * @param	string	The name of the event
	 * @param	array	The callback for the Event
	 * @return	void
	 */
	public static function register($event, array $callback)
	{
		$key = get_class($callback[0]).'::'.$callback[1];
		self::$_listeners[$event][$key] = $callback;
		log_message('debug', 'Events::register() - Registered "'.$key.' with event "'.$event.'"');
	}
	
	/**
	 * Trigger
	 *
	 * Triggers an event and returns the results.  The results can be returned
	 * in the following formats:
	 *
	 * 'array'
	 * 'json'
	 * 'serialized'
	 * 'string'
	 *
	 * @access	public
	 * @param	string	The name of the event
	 * @param	mixed	Any data that is to be passed to the listener
	 * @param	string	The return type
	 * @return	mixed	The return of the listeners, in the return type
	 */
	public static function trigger($event, $data = '', $return_type = 'string')
	{
		log_message('debug', 'Events::trigger() - Triggering event "'.$event.'"');
		
		$calls = array();
		
		if (self::has_listeners($event))
		{
			foreach (self::$_listeners[$event] as $listener)
			{
				if (is_callable($listener))
				{
					$calls[] = call_user_func($listener, $data);
				}
			}
		}
		
		return self::_format_return($calls, $return_type);
	}
	
	/**
	 * Format Return
	 *
	 * Formats the return in the given type
	 *
	 * @access	protected
	 * @param	array	The array of returns
	 * @param	string	The return type
	 * @return	mixed	The formatted return
	 */
	protected static function _format_return(array $calls, $return_type)
	{
		log_message('debug', 'Events::_format_return() - Formating calls in type "'.$return_type.'"');
		
		switch ($return_type)
		{
			case 'json':
				return json_encode($calls);
				break;
			case 'serialized':
				return serialize($calls);
				break;
			case 'string':
				$str = '';
				foreach ($calls as $call)
				{
					$str .= $call;
				}
				return $str;
				break;
			default:
				return $calls;
				break;
		}
		
		return FALSE;
	}
	
	/**
	 * Has Listeners
	 *
	 * Checks if the event has listeners
	 *
	 * @access	public
	 * @param	string	The name of the event
	 * @return	bool	Whether the event has listeners
	 */
	public static function has_listeners($event)
	{
		log_message('debug', 'Events::has_listeners() - Checking if event "'.$event.'" has listeners.');
		
		if (isset(self::$_listeners[$event]) AND count(self::$_listeners[$event]) > 0)
		{
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * Event trigger for integrations on Create CRUD operation
	 *
	 * @param	array		$data	Data to be made available to Third Party Integrations
	 * @return	string|NULL			NULL is returned on a successful completion of integration tasks or the integration and failure message
	 */
	public function integrations_create($data)
	{
		$negative_results = NULL;
		log_message("ERROR","event created for integrations create: ". var_dump($data));
		//calls to create functions for the different Integrations
		
		return $negative_results;
	}
	
	/**
	 * Event trigger for integrations on Read CRUD operation
	 *
	 * @param 	array		$data	Data to be made available to Third Party Integrations
	 * @return	string|NULL			NULL is returned on a successful completion of integration tasks or the integration and failure message
	 */
	public function integrations_read($data)
	{
		$negative_results = NULL;
		log_message("ERROR","event created for integrations read: ". var_dump($data));
		//calls to read functions for the different Integrations
		
		return $negative_results;
	}
	
	/**
	 * Event trigger for integrations on Create CRUD operation
	 *
	 * @param 	array		$data	Data to be made available to Third Party Integrations
	 * @return	string|NULL			NULL is returned on a successful completion of integration tasks or the integration and failure message
	 */
	public function integrations_update($data)
	{
		$negative_results = NULL;
		log_message("ERROR","event created for integrations update: ". var_dump($data));
		//calls to update functions for the different Integrations
		$negative_results = "CLCdesq API failed to update item.  Error Code 1232";
		return $negative_results;
	}
	
	/**
	 * Event trigger for integrations on Create CRUD operation
	 *
	 * @param 	array		$data	Data to be made available to Third Party Integrations
	 * @return	string|NULL			NULL is returned on a successful completion of integration tasks or the integration and failure message
	 */
	public function integrations_delete($data)
	{
		$negative_results = NULL;
		log_message("ERROR","event created for integrations delete: ". var_dump($data));
		//calls to delete functions for the different Integrations
		
		return $negative_results;
	}
}