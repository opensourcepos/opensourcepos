<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->post('plugins/mailchimp/checkMailchimpApiKey', '\App\Plugins\MailchimpPlugin\Controllers\MailchimpController::postCheckMailchimpApiKey');
