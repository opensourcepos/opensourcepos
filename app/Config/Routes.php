<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
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
