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
defined('MONTH')  || define('MONTH', 2_592_000);
defined('YEAR')   || define('YEAR', 31_536_000);
defined('DECADE') || define('DECADE', 315_360_000);
defined('DEFAULT_DATE') || define('DEFAULT_DATE', mktime(0, 0, 0, 1, 1, 2010));
defined('DEFAULT_DATETIME') || define('DEFAULT_DATETIME', mktime(0, 0, 0, 1, 1, 2010));
defined('NOW') || define('NOW', time());


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
defined('EXIT_SUCCESS')        || define('EXIT_SUCCESS', 0);        // no errors
defined('EXIT_ERROR')          || define('EXIT_ERROR', 1);          // generic error
defined('EXIT_CONFIG')         || define('EXIT_CONFIG', 3);         // configuration error
defined('EXIT_UNKNOWN_FILE')   || define('EXIT_UNKNOWN_FILE', 4);   // file not found
defined('EXIT_UNKNOWN_CLASS')  || define('EXIT_UNKNOWN_CLASS', 5);  // unknown class
defined('EXIT_UNKNOWN_METHOD') || define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     || define('EXIT_USER_INPUT', 7);     // invalid user input
defined('EXIT_DATABASE')       || define('EXIT_DATABASE', 8);       // database error
defined('EXIT__AUTO_MIN')      || define('EXIT__AUTO_MIN', 9);      // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      || define('EXIT__AUTO_MAX', 125);    // highest automatically-assigned error code

/**
 * @deprecated Use \CodeIgniter\Events\Events::PRIORITY_LOW instead.
 */
define('EVENT_PRIORITY_LOW', 200);

/**
 * @deprecated Use \CodeIgniter\Events\Events::PRIORITY_NORMAL instead.
 */
define('EVENT_PRIORITY_NORMAL', 100);

/**
 * @deprecated Use \CodeIgniter\Events\Events::PRIORITY_HIGH instead.
 */
define('EVENT_PRIORITY_HIGH', 10);

/**
 * Global Constants.
 */
const NEW_ENTRY = -1;
const ACTIVE = 0;
const DELETED = 1;

/**
 * Attribute Related Constants.
 */
const GROUP = 'GROUP';
const DROPDOWN = 'DROPDOWN';
const DECIMAL = 'DECIMAL';
const DATE = 'DATE';
const TEXT = 'TEXT';
const CHECKBOX = 'CHECKBOX';
const NO_DEFINITION_ID = 0;
const CATEGORY_DEFINITION_ID = -1;
const DEFINITION_TYPES = [GROUP, DROPDOWN, DECIMAL, TEXT, DATE, CHECKBOX];

/**
 * Item Related Constants.
 */
const HAS_STOCK = 0;
const HAS_NO_STOCK = 1;

const ITEM = 0;
const ITEM_KIT = 1;
const ITEM_AMOUNT_ENTRY = 2;
const ITEM_TEMP = 3;
const NEW_ITEM = -1;

const PRINT_ALL = 0;
const PRINT_PRICED = 1;
const PRINT_KIT = 2;

const PRINT_YES = 0;
const PRINT_NO = 1;

const PRICE_ALL = 0;
const PRICE_KIT = 1;
const PRICE_KIT_ITEMS = 2;

const PRICE_OPTION_ALL = 0;
const PRICE_OPTION_KIT = 1;
const PRICE_OPTION_KIT_STOCK = 2;

const NAME_SEPARATOR = ' | ';

/**
 * Sale Related Constants.
 */
const COMPLETED = 0;
const SUSPENDED = 1;
const CANCELED = 2;

const SALE_TYPE_POS = 0;
const SALE_TYPE_INVOICE = 1;
const SALE_TYPE_WORK_ORDER = 2;
const SALE_TYPE_QUOTE = 3;
const SALE_TYPE_RETURN = 4;

const PERCENT = 0;
const FIXED = 1;

const PRICE_MODE_STANDARD = 0;
const PRICE_MODE_KIT = 1;

const PAYMENT_TYPE_UNASSIGNED = '--';

const CASH_ADJUSTMENT_TRUE = 1;
const CASH_ADJUSTMENT_FALSE = 0;
const CASH_MODE_TRUE = 1;
const CASH_MODE_FALSE = 0;

/**
 * Supplier Related Constants
 */
const GOODS_SUPPLIER = 0;
const COST_SUPPLIER = 1;

/**
 * Locale Related Constants
 */
const MAX_PRECISION = 1e14;
const DEFAULT_PRECISION = 2;
const DEFAULT_LANGUAGE = 'english';
const DEFAULT_LANGUAGE_CODE = 'en';
