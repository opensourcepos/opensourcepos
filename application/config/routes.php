<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

$route['default_controller'] = 'login';
$route['no_access/([^/]+)'] = 'no_access/index/$1';
$route['no_access/([^/]+)/([^/]+)'] = 'no_access/index/$1/$2';

$route['sales/index/([^/]+)'] = 'sales/manage/$1';
$route['sales/index/([^/]+)/([^/]+)'] = 'sales/manage/$1/$2';
$route['sales/index/([^/]+)/([^/]+)/([^/]+)'] = 'sales/manage/$1/$2/$3';

$route['reports/(summary_:any)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3/$4';
$route['reports/summary_expenses_categories'] = 'reports/date_input_only';
$route['reports/summary_:any'] = 'reports/date_input';

$route['reports/(graphical_:any)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3/$4';
$route['reports/graphical_summary_expenses_categories'] = 'reports/date_input_only';
$route['reports/graphical_:any'] = 'reports/date_input';

$route['reports/(inventory_:any)/([^/]+)'] = 'reports/$1/$2';
$route['reports/inventory_summary'] = 'reports/inventory_summary_input';
$route['reports/(inventory_summary)/([^/]+)/([^/]+)/([^/]+)'] = 'reports/$1/$2';

$route['reports/(detailed_sales)/([^/]+)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3$/$4';
$route['reports/detailed_sales'] = 'reports/date_input_sales';
$route['reports/(detailed_receivings)/([^/]+)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3/$4';
$route['reports/detailed_receivings'] = 'reports/date_input_recv';

$route['reports/(specific_:any)/([^/]+)/([^/]+)/([^/]+)'] = 'reports/$1/$2/$3/$4';
$route['reports/specific_customer'] = 'reports/specific_customer_input';
$route['reports/specific_employee'] = 'reports/specific_employee_input';
$route['reports/specific_discount'] = 'reports/specific_discount_input';

$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
