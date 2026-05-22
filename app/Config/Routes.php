<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->setDefaultController('Login');

$routes->get('/', 'Login::index');
$routes->get('login', 'Login::index');
$routes->post('login', 'Login::index');
$routes->post('migrate', 'Login::migrate');

$routes->get('sales', 'Sales::getIndex');
$routes->get('sales/customerDisplay', 'Sales::getCustomerDisplay');
$routes->get('sales/itemSearch', 'Sales::getItemSearch');
$routes->post('sales/selectCustomer', 'Sales::postSelectCustomer');
$routes->post('sales/changeMode', 'Sales::postChangeMode');
$routes->post('sales/setComment', 'Sales::postSetComment');
$routes->post('sales/setInvoiceNumber', 'Sales::postSetInvoiceNumber');
$routes->post('sales/setPaymentType', 'Sales::postSetPaymentType');
$routes->post('sales/setPrintAfterSale', 'Sales::postSetPrintAfterSale');
$routes->post('sales/setPriceWorkOrders', 'Sales::postSetPriceWorkOrders');
$routes->post('sales/setEmailReceipt', 'Sales::postSetEmailReceipt');
$routes->post('sales/addPayment', 'Sales::postAddPayment');
$routes->post('sales/add', 'Sales::postAdd');
$routes->post('sales/editItem/(:segment)', 'Sales::postEditItem/$1');
$routes->post('sales/deleteItem/(:segment)', 'Sales::getDeleteItem/$1');
$routes->post('sales/deletePayment/(:segment)', 'Sales::getDeletePayment/$1');
$routes->post('sales/removeCustomer', 'Sales::getRemoveCustomer');
$routes->post('sales/complete', 'Sales::postComplete');
$routes->post('sales/cancel', 'Sales::postCancel');
$routes->post('sales/suspend', 'Sales::postSuspend');
$routes->post('sales/unsuspend', 'Sales::postUnsuspend');
$routes->post('sales/checkInvoiceNumber', 'Sales::postCheckInvoiceNumber');
$routes->post('sales/changeItemNumber', 'Sales::postChangeItemNumber');
$routes->post('sales/changeItemName', 'Sales::postChangeItemName');
$routes->post('sales/changeItemDescription', 'Sales::postChangeItemDescription');
$routes->get('sales/suspended', 'Sales::getSuspended');
$routes->get('sales/discardSuspendedSale', 'Sales::getDiscardSuspendedSale');
$routes->get('sales/sales_keyboard_help', 'Sales::getSalesKeyboardHelp');
$routes->get('sales/receipt/(:num)', 'Sales::getReceipt/$1');
$routes->get('sales/invoice/(:num)', 'Sales::getInvoice/$1');
$routes->get('sales/edit/(:num)', 'Sales::getEdit/$1');
$routes->post('sales/delete/(:num)', 'Sales::postDelete/$1');
$routes->post('sales/save/(:num)', 'Sales::postSave/$1');

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
