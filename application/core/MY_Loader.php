<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class MY_Loader extends CI_Loader {

    protected $_ci_events_paths = array();

    public function __construct() {
        parent::__construct();

        $this->_ci_events_paths = array(APPPATH);
        log_message('debug', "MY_Loader Class Initialized");
    }

    /** Load a events module * */
    public function event($event = '', $params = NULL, $object_name = NULL)
    {
        if (is_array($event))
        {
            foreach ($event as $class)
            {
                $this->library($class, $params);
            }

            return;
        }

        if ($event == '' OR isset($this->_base_classes[$event]))
        {
            return FALSE;
        }

        if ( ! is_null($params) && ! is_array($params))
        {
            $params = NULL;
        }

        $this->_ci_events_class($event, $params, $object_name);
    }

    /** Load an array of events * */
    public function events($event = '', $params = NULL, $object_name = NULL)
    {
        if (is_array($event))
        {
            foreach ($event as $class)
            {
                $this->library($class, $params);
            }
        }
        return;
    }

    /**
     * Load Events
     *
     * This function loads the requested class.
     *
     * @param	string	the item that is being loaded
     * @param	mixed	any additional parameters
     * @param	string	an optional object name
     * @return	void
     */
    protected function _ci_events_class($class, $params = NULL, $object_name = NULL) {
        // Get the class name, and while we're at it trim any slashes.
        // The directory path can be included as part of the class name,
        // but we don't want a leading slash
        $class = str_replace('.php', '', trim($class, '/'));

        // Was the path included with the class name?
        // We look for a slash to determine this
        $subdir = '';
        if (($last_slash = strrpos($class, '/')) !== FALSE) {
            // Extract the path
            $subdir = substr($class, 0, $last_slash + 1);

            // Get the filename from the path
            $class = substr($class, $last_slash + 1);
        }

        // We'll test for both lowercase and capitalized versions of the file name
        foreach (array(ucfirst($class), strtolower($class)) as $class) {
            $subclass = APPPATH . 'events/' . $subdir . config_item('subclass_prefix') . $class . '.php';

            // Is this a class extension request?
            if (file_exists($subclass)) {
                $baseclass = BASEPATH . 'events/' . ucfirst($class) . '.php';

                if (!file_exists($baseclass)) {
                    log_message('error', "Unable to load the requested class: " . $class);
                    show_error("Unable to load the requested class: " . $class);
                }

                // Safety:  Was the class already loaded by a previous call?
                if (in_array($subclass, $this->_ci_library_paths)) {
                    // Before we deem this to be a duplicate request, let's see
                    // if a custom object name is being supplied.  If so, we'll
                    // return a new instance of the object
                    if (!is_null($object_name)) {
                        $CI = & get_instance();
                        if (!isset($CI->$object_name)) {
                            return $this->_ci_init_library($class, config_item('subclass_prefix'), $params, $object_name);
                        }
                    }

                    $is_duplicate = TRUE;
                    log_message('debug', $class . " class already loaded. Second attempt ignored.");
                    return;
                }

                include_once($baseclass);
                include_once($subclass);
                $this->_ci_library_paths[] = $subclass;

                return $this->_ci_init_library($class, config_item('subclass_prefix'), $params, $object_name);
            }

            // Lets search for the requested library file and load it.
            $is_duplicate = FALSE;
            foreach ($this->_ci_events_paths as $path) {
                $filepath = $path . 'events/' . $subdir . $class . '.php';

                // Does the file exist?  No?  Bummer...
                if (!file_exists($filepath)) {
                    continue;
                }

                // Safety:  Was the class already loaded by a previous call?
                if (in_array($filepath, $this->_ci_library_paths)) {
                    // Before we deem this to be a duplicate request, let's see
                    // if a custom object name is being supplied.  If so, we'll
                    // return a new instance of the object
                    if (!is_null($object_name)) {
                        $CI = & get_instance();
                        if (!isset($CI->$object_name)) {
                            return $this->_ci_init_library($class, '', $params, $object_name);
                        }
                    }

                    $is_duplicate = TRUE;
                    log_message('debug', $class . " class already loaded. Second attempt ignored.");
                    return;
                }

                include_once($filepath);
                $this->_ci_library_paths[] = $filepath;
                return $this->_ci_init_library($class, '', $params, $object_name);
            }
        } // END FOREACH
        // One last attempt.  Maybe the library is in a subdirectory, but it wasn't specified?
        if ($subdir == '') {
            $path = strtolower($class) . '/' . $class;
            return $this->_ci_load_class($path, $params);
        }

        // If we got this far we were unable to find the requested class.
        // We do not issue errors if the load call failed due to a duplicate request
        if ($is_duplicate == FALSE) {
            log_message('error', "Unable to load the requested class: " . $class);
            show_error("Unable to load the requested class: " . $class);
        }
    }

}
