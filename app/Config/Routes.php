<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->setDefaultController('Login');

$routes->get('/', 'Login::index');
$routes->get('login', 'Login::index');
$routes->post('login', 'Login::index');

$routes->add('no_access/index/(:segment)', 'No_access::index/$1');
$routes->add('no_access/index/(:segment)/(:segment)', 'No_access::index/$1/$2');

$routes->add('reports/summary_(:any)/(:any)/(:any)', 'Reports::Summary_$1/$2/$3/$4');
$routes->add('reports/summary_expenses_categories', 'Reports::date_input_only');
$routes->add('reports/summary_payments', 'Reports::date_input_only');
$routes->add('reports/summary_discounts', 'Reports::summary_discounts_input');
$routes->add('reports/summary_(:any)', 'Reports::date_input');

$routes->add('reports/graphical_(:any)/(:any)/(:any)', 'Reports::Graphical_$1/$2/$3/$4');
$routes->add('reports/graphical_summary_expenses_categories', 'Reports::date_input_only');
$routes->add('reports/graphical_summary_discounts', 'Reports::summary_discounts_input');
$routes->add('reports/graphical_(:any)', 'Reports::date_input');

$routes->add('reports/inventory_(:any)/(:any)', 'Reports::Inventory_$1/$2');
$routes->add('reports/inventory_low', 'Reports::inventory_low');
$routes->add('reports/inventory_summary', 'Reports::inventory_summary_input');
$routes->add('reports/inventory_summary/(:any)/(:any)/(:any)', 'Reports::inventory_summary/$1/$2/$3');

$routes->add('reports/detailed_(:any)/(:any)/(:any)/(:any)', 'Reports::Detailed_$1/$2/$3/$4');
$routes->add('reports/detailed_sales', 'Reports::date_input_sales');
$routes->add('reports/detailed_receivings', 'Reports::date_input_recv');

$routes->add('reports/specific_(:any)/(:any)/(:any)/(:any)', 'Reports::Specific_$1/$2/$3/$4');
$routes->add('reports/specific_customers', 'Reports::specific_customer_input');
$routes->add('reports/specific_employees', 'Reports::specific_employee_input');
$routes->add('reports/specific_discounts', 'Reports::specific_discount_input');
$routes->add('reports/specific_suppliers', 'Reports::specific_supplier_input');

$routes->group('office/api-keys', ['filter' => 'session'], static function(RouteCollection $routes): void {
    $routes->get('/', 'ApiKeys::index');
    $routes->post('generate', 'ApiKeys::generate');
    $routes->post('revoke/(:num)', 'ApiKeys::revoke/$1');
    $routes->post('regenerate/(:num)', 'ApiKeys::regenerate/$1');
});

$routes->group('api/v1', ['filter' => 'apiauth'], static function(RouteCollection $routes): void {
    $routes->get('customers', 'Api\Customers::index');
    $routes->get('customers/(:num)', 'Api\Customers::show/$1');
    $routes->post('customers', 'Api\Customers::create');
    $routes->put('customers/(:num)', 'Api\Customers::update/$1');
    $routes->delete('customers/(:num)', 'Api\Customers::delete/$1');
    $routes->post('customers/batch-delete', 'Api\Customers::batchDelete');
    $routes->get('customers/suggest', 'Api\Customers::suggest');
    
    $routes->get('suppliers', 'Api\Suppliers::index');
    $routes->get('suppliers/(:num)', 'Api\Suppliers::show/$1');
    $routes->post('suppliers', 'Api\Suppliers::create');
    $routes->put('suppliers/(:num)', 'Api\Suppliers::update/$1');
    $routes->delete('suppliers/(:num)', 'Api\Suppliers::delete/$1');
    $routes->post('suppliers/batch-delete', 'Api\Suppliers::batchDelete');
    $routes->get('suppliers/suggest', 'Api\Suppliers::suggest');
    
    $routes->get('items', 'Api\Items::index');
    $routes->get('items/(:num)', 'Api\Items::show/$1');
    $routes->post('items', 'Api\Items::create');
    $routes->put('items/(:num)', 'Api\Items::update/$1');
    $routes->delete('items/(:num)', 'Api\Items::delete/$1');
    $routes->post('items/batch-delete', 'Api\Items::batchDelete');
    $routes->get('items/suggest', 'Api\Items::suggest');
    $routes->get('items/(:num)/quantities', 'Api\Items::quantities/$1');
    
    $routes->get('inventory', 'Api\Inventory::index');
    $routes->post('inventory', 'Api\Inventory::create');
    $routes->post('inventory/bulk', 'Api\Inventory::create');
    
    $routes->get('sales', 'Api\Sales::index');
    $routes->get('sales/(:num)', 'Api\Sales::show/$1');
    $routes->get('sales/(:num)/items', 'Api\Sales::items/$1');
    $routes->get('sales/(:num)/payments', 'Api\Sales::payments/$1');
    
    $routes->get('receivings', 'Api\Receivings::index');
    $routes->get('receivings/(:num)', 'Api\Receivings::show/$1');
    $routes->get('receivings/(:num)/items', 'Api\Receivings::items/$1');
});
