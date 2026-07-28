<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Office UI. The module menu entry registered in WhatsappPlugin::install() links
// to /{module_id}, so these live under 'whatsapp' rather than 'plugins/whatsapp'.
// Access is enforced by the 'whatsapp' permission in WhatsappController.
$routes->get('whatsapp', '\App\Plugins\WhatsappPlugin\Controllers\WhatsappController::getIndex');
$routes->get('whatsapp/view/(:num)', '\App\Plugins\WhatsappPlugin\Controllers\WhatsappController::getView/$1');
$routes->get('whatsapp/conversation/(:segment)', '\App\Plugins\WhatsappPlugin\Controllers\WhatsappController::getConversation/$1');
$routes->get('whatsapp/sendDocument/(:num)/(:segment)', '\App\Plugins\WhatsappPlugin\Controllers\WhatsappController::getSendDocument/$1/$2');
$routes->post('whatsapp/send', '\App\Plugins\WhatsappPlugin\Controllers\WhatsappController::postSend');
$routes->post('whatsapp/sendForm/(:num)', '\App\Plugins\WhatsappPlugin\Controllers\WhatsappController::postSendForm/$1');

// Public Meta callback: no authentication, and CSRF-exempt via the
// 'plugins/*/webhook' pattern in app/Config/Filters.php — which is why this one
// route keeps the 'plugins/' prefix. The controller verifies the
// X-Hub-Signature-256 HMAC against the configured app secret before storing anything.
$routes->get('plugins/whatsapp/webhook', '\App\Plugins\WhatsappPlugin\Controllers\WebhookController::index');
$routes->post('plugins/whatsapp/webhook', '\App\Plugins\WhatsappPlugin\Controllers\WebhookController::index');
