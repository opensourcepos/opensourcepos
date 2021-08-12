<?php

/*
 | --------------------------------------------------------------------
 | App Namespace
 | --------------------------------------------------------------------
 |
 | This defines the default Namespace that is used throughout
 | CodeIgniter to refer to the Application directory. Change
 | this constant to change the namespace that all application
 | classes should use.
 |
 | NOTE: changing this will require manually modifying the
 | existing namespaces of App\* namespaced-classes.
 */
defined('APP_NAMESPACE') || define('APP_NAMESPACE', 'App');

/*
 | --------------------------------------------------------------------------
 | Composer Path
 | --------------------------------------------------------------------------
 |
 | The path that Composer's autoload file is expected to live. By default,
 | the vendor folder is in the Root directory, but you can customize that here.
 */
defined('COMPOSER_PATH') || define('COMPOSER_PATH', ROOTPATH . 'vendor/autoload.php');

/*
 |--------------------------------------------------------------------------
 | Timing Constants
 |--------------------------------------------------------------------------
 |
 | Provide simple ways to work with the myriad of PHP functions that
 | require information to be in seconds.
 */
defined('SECOND') || define('SECOND', 1);
defined('MINUTE') || define('MINUTE', 60);
defined('HOUR')   || define('HOUR', 3600);
defined('DAY')    || define('DAY', 86400);
defined('WEEK')   || define('WEEK', 604800);
defined('MONTH')  || define('MONTH', 2592000);
defined('YEAR')   || define('YEAR', 31536000);
defined('DECADE') || define('DECADE', 315360000);

/*
 | --------------------------------------------------------------------------
 | Exit Status Codes
 | --------------------------------------------------------------------------
 |
 | Used to indicate the conditions under which the script is exit()ing.
 | While there is no universal standard for error codes, there are some
 | broad conventions.  Three such conventions are mentioned below, for
 | those who wish to make use of them.  The CodeIgniter defaults were
 | chosen for the least overlap with these conventions, while still
 | leaving room for others to be defined in future versions and user
 | applications.
 |
 | The three main conventions used for determining exit status codes
 | are as follows:
 |
 |    Standard C/C++ Library (stdlibc):
 |       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
 |       (This link also contains other GNU-specific conventions)
 |    BSD sysexits.h:
 |       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
 |    Bash scripting:
 |       http://tldp.org/LDP/abs/html/exitcodes.html
 |
 */
defined('EXIT_SUCCESS')        || define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          || define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         || define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   || define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  || define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') || define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     || define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       || define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      || define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      || define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 | Attribute Related Constants.
 */
define('GROUP', 'GROUP');
define('DROPDOWN', 'DROPDOWN');
define('DECIMAL', 'DECIMAL');
define('DATE', 'DATE');
define('TEXT', 'TEXT');
define('CHECKBOX', 'CHECKBOX');
define('NO_DEFINITION_ID', 0);
define('CATEGORY_DEFINITION_ID', -1);

const DEFINITION_TYPES = [GROUP, DROPDOWN, DECIMAL, TEXT, DATE, CHECKBOX];

/*
 | Item Related Constants.
 */
define('HAS_STOCK', 0);
define('HAS_NO_STOCK', 1);

define('ITEM', 0);
define('ITEM_KIT', 1);
define('ITEM_AMOUNT_ENTRY', 2);
define('ITEM_TEMP', 3);
define('NEW_ITEM', -1);

define('PRINT_ALL', 0);
define('PRINT_PRICED', 1);
define('PRINT_KIT', 2);

define('PRINT_YES', 0);
define('PRINT_NO', 1);

define('PRICE_ALL', 0);
define('PRICE_KIT', 1);
define('PRICE_KIT_ITEMS', 2);

define('PRICE_OPTION_ALL', 0);
define('PRICE_OPTION_KIT', 1);
define('PRICE_OPTION_KIT_STOCK', 2);

define('NAME_SEPARATOR', ' | ');

/*
 | Sale Related Constants.
 */
define('COMPLETED', 0);
define('SUSPENDED', 1);
define('CANCELED', 2);

define('SALE_TYPE_POS', 0);
define('SALE_TYPE_INVOICE', 1);
define('SALE_TYPE_WORK_ORDER', 2);
define('SALE_TYPE_QUOTE', 3);
define('SALE_TYPE_RETURN', 4);

define('PERCENT', 0);
define('FIXED', 1);

define('PRICE_MODE_STANDARD', 0);
define('PRICE_MODE_KIT', 1);

define('PAYMENT_TYPE_UNASSIGNED', '--');

define('CASH_ADJUSTMENT_TRUE', 1);
define('CASH_ADJUSTMENT_FALSE', 0);
define('CASH_MODE_TRUE', 1);
define('CASH_MODE_FALSE', 0);
