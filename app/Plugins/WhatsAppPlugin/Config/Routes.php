<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// The module menu entry links to /{module_id}, so the office UI lives under
// 'whatsapp' rather than 'plugins/whatsapp'.
$routes->get('whatsapp', '\App\Plugins\WhatsAppPlugin\Controllers\WhatsAppController::getIndex');
$routes->get('whatsapp/view/(:num)', '\App\Plugins\WhatsAppPlugin\Controllers\WhatsAppController::getView/$1');
$routes->get('whatsapp/conversation/(:segment)', '\App\Plugins\WhatsAppPlugin\Controllers\WhatsAppController::getConversation/$1');
$routes->get('whatsapp/sendDocument/(:num)/(:segment)', '\App\Plugins\WhatsAppPlugin\Controllers\WhatsAppController::getSendDocument/$1/$2');
$routes->post('whatsapp/send', '\App\Plugins\WhatsAppPlugin\Controllers\WhatsAppController::postSend');
$routes->post('whatsapp/sendForm/(:num)', '\App\Plugins\WhatsAppPlugin\Controllers\WhatsAppController::postSendForm/$1');

$routes->get('plugins/whatsapp/icon', '\App\Plugins\WhatsAppPlugin\Controllers\IconController::getIcon');

// Public Meta callback, CSRF-exempt via the 'plugins/*/webhook' pattern in
// app/Config/Filters.php — which is why this route keeps the 'plugins/' prefix.
$routes->get('plugins/whatsapp/webhook', '\App\Plugins\WhatsAppPlugin\Controllers\WebhookController::index');
$routes->post('plugins/whatsapp/webhook', '\App\Plugins\WhatsAppPlugin\Controllers\WebhookController::index');
