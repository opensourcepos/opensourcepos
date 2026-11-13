<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setDefaultController('Login');

$routes->get('/', 'Login::index');
$routes->get('login', 'Login::index');
$routes->post('login', 'Login::index');
$routes->post('migrate', 'Login::migrate');

$routes->add('no_access/index/(:segment)', 'No_access::index/$1');
$routes->add('no_access/index/(:segment)/(:segment)', 'No_access::index/$1/$2');

$routes->add('reports/summary_sales/exportAll/(:any)/(:any)/(:any)/(:any)/(:any)', 'Reports::summary_salesExportAll/$1/$2/$3/$4/$5');
$routes->add('reports/summary_categories/exportAll/(:any)/(:any)/(:any)/(:any)/(:any)', 'Reports::summary_categoriesExportAll/$1/$2/$3/$4/$5');
$routes->add('reports/summary_expenses_categories/exportAll/(:any)/(:any)/(:any)/(:any)', 'Reports::summary_expenses_categoriesExportAll/$1/$2/$3/$4');
$routes->add('reports/summary_customers/exportAll/(:any)/(:any)/(:any)/(:any)/(:any)', 'Reports::summary_customersExportAll/$1/$2/$3/$4/$5');
$routes->add('reports/summary_suppliers/exportAll/(:any)/(:any)/(:any)/(:any)/(:any)', 'Reports::summary_suppliersExportAll/$1/$2/$3/$4/$5');
$routes->add('reports/summary_items/exportAll/(:any)/(:any)/(:any)/(:any)/(:any)', 'Reports::summary_itemsExportAll/$1/$2/$3/$4/$5');
$routes->add('reports/summary_employees/exportAll/(:any)/(:any)/(:any)/(:any)/(:any)', 'Reports::summary_employeesExportAll/$1/$2/$3/$4/$5');
$routes->add('reports/summary_taxes/exportAll/(:any)/(:any)/(:any)/(:any)/(:any)', 'Reports::summary_taxesExportAll/$1/$2/$3/$4/$5');
$routes->add('reports/summary_sales_taxes/exportAll/(:any)/(:any)/(:any)/(:any)/(:any)', 'Reports::summary_sales_taxesExportAll/$1/$2/$3/$4/$5');
$routes->add('reports/summary_discounts/exportAll/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)', 'Reports::summary_discountsExportAll/$1/$2/$3/$4/$5/$6');
$routes->add('reports/summary_payments/exportAll/(:any)/(:any)/(:any)', 'Reports::summary_paymentsExportAll/$1/$2/$3');

$routes->add('reports/summary_(:any)/(:any)/(:any)', 'Reports::Summary_$1/$2/$3/$4');
$routes->add('reports/summary_expenses_categories', 'Reports::date_input_only');
$routes->add('reports/summary_payments', 'Reports::date_input_only');
$routes->add('reports/summary_discounts', 'Reports::summary_discounts_input');
$routes->add('reports/summary_(:any)', 'Reports::date_input');

$routes->add('reports/graphical_(:any)/(:any)/(:any)', 'Reports::Graphical_$1/$2/$3/$4');
$routes->add('reports/graphical_summary_expenses_categories', 'Reports::date_input_only');
$routes->add('reports/graphical_summary_discounts', 'Reports::summary_discounts_input');
$routes->add('reports/graphical_(:any)', 'Reports::date_input');

$routes->add('reports/inventory_low/exportAll/(:any)', 'Reports::inventory_lowExportAll/$1');
$routes->add('reports/inventory_summary/exportAll/(:any)/(:any)/(:any)', 'Reports::inventory_summaryExportAll/$1/$2/$3');

$routes->add('reports/inventory_(:any)/(:any)', 'Reports::Inventory_$1/$2');
$routes->add('reports/inventory_low', 'Reports::inventory_low');
$routes->add('reports/inventory_summary', 'Reports::inventory_summary_input');
$routes->add('reports/inventory_summary/(:any)/(:any)/(:any)', 'Reports::inventory_summary/$1/$2/$3');

$routes->add('reports/detailed_item_sales/exportAll/(:segment)/(:segment)/(:segment)/(:segment)/(:segment)/(:segment)', 'Reports::detailedItemSalesExportAll/$1/$2/$3/$4/$5/$6');
$routes->add('reports/detailed_sales/exportAll/(:any)/(:any)/(:any)/(:any)/(:any)', 'Reports::detailed_salesExportAll/$1/$2/$3/$4/$5');
$routes->add('reports/detailed_receivings/exportAll/(:any)/(:any)/(:any)/(:any)/(:any)', 'Reports::detailed_receivingsExportAll/$1/$2/$3/$4/$5');

$routes->add('reports/detailed_item_sales', 'Reports::dateInputItemSales');
$routes->add('reports/detailed_item_sales/(:segment)/(:segment)/(:segment)/(:segment)/(:segment)', 'Reports::detailedItemSales/$1/$2/$3/$4/$5');
$routes->add('reports/detailed_(:any)/(:any)/(:any)/(:any)', 'Reports::Detailed_$1/$2/$3/$4');
$routes->add('reports/detailed_sales', 'Reports::date_input_sales');
$routes->add('reports/detailed_receivings', 'Reports::date_input_recv');

$routes->add('reports/specific_customers/exportAll/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)', 'Reports::specific_customersExportAll/$1/$2/$3/$4/$5/$6');
$routes->add('reports/specific_employees/exportAll/(:any)/(:any)/(:any)/(:any)/(:any)', 'Reports::specific_employeesExportAll/$1/$2/$3/$4/$5');
$routes->add('reports/specific_discounts/exportAll/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)', 'Reports::specific_discountsExportAll/$1/$2/$3/$4/$5/$6');
$routes->add('reports/specific_suppliers/exportAll/(:any)/(:any)/(:any)/(:any)/(:any)', 'Reports::specific_suppliersExportAll/$1/$2/$3/$4/$5');

$routes->add('reports/specific_(:any)/(:any)/(:any)/(:any)', 'Reports::Specific_$1/$2/$3/$4');
$routes->add('reports/specific_customers', 'Reports::specific_customer_input');
$routes->add('reports/specific_employees', 'Reports::specific_employee_input');
$routes->add('reports/specific_discounts', 'Reports::specific_discount_input');
$routes->add('reports/specific_suppliers', 'Reports::specific_supplier_input');
