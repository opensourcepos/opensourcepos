<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Login');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.

$routes->get('/', 'Login::index');
$routes->get('login', 'Login::index');
$routes->post('login', 'Login::index');

$routes->add('no_access/([^/]+)', 'No_access::getIndex/$1');
$routes->add('no_access/([^/]+)/([^/]+)', 'No_access::getIndex/$1/$2');

$routes->add('sales/index/([^/]+)', 'Sales::manage/$1');
$routes->add('sales/index/([^/]+)/([^/]+)', 'Sales::manage/$1/$2');
$routes->add('sales/index/([^/]+)/([^/]+)/([^/]+)', 'Sales::manage/$1/$2/$3');

$routes->add('reports/(summary_:any)/([^/]+)/([^/]+)', 'Reports::summary_(.+)/$1/$2/$3/$4'); //TODO - double check all TODOs
$routes->add('reports/summary_expenses_categories', 'Reports::date_input_only');
$routes->add('reports/summary_payments', 'Reports::date_input_only');
$routes->add('reports/summary_discounts', 'Reports::summary_discounts_input');
$routes->add('reports/summary_:any', 'Reports::date_input');

$routes->add('reports/(graphical_:any)/([^/]+)/([^/]+)', 'Reports::/$1/$2/$3/$4'); //TODO
$routes->add('reports/graphical_summary_expenses_categories', 'Reports::date_input_only');
$routes->add('reports/graphical_summary_discounts', 'Reports::summary_discounts_input');
$routes->add('reports/graphical_:any', 'Reports::date_input');

$routes->add('reports/(inventory_:any)/([^/]+)', 'Reports::/$1/$2'); //TODO
$routes->add('reports/inventory_summary', 'Reports::inventory_summary_input');
$routes->add('reports/(inventory_summary)/([^/]+)/([^/]+)/([^/]+)', 'Reports::/$1/$2'); //TODO

$routes->add('reports/(detailed_:any)/([^/]+)/([^/]+)/([^/]+)', 'Reports::/$1/$2/$3/$4'); //TODO
$routes->add('reports/detailed_sales', 'Reports::date_input_sales');
$routes->add('reports/detailed_receivings', 'Reports::date_input_recv');

$routes->add('reports/(specific_:any)/([^/]+)/([^/]+)/([^/]+)', 'Reports::/$1/$2/$3/$4'); //TODO
$routes->add('reports/specific_customer', 'Reports::specific_customer_input');
$routes->add('reports/specific_employee', 'Reports::specific_employee_input');
$routes->add('reports/specific_discount', 'Reports::specific_discount_input');
$routes->add('reports/specific_supplier', 'Reports::specific_supplier_input');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
