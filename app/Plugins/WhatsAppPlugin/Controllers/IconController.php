<?php

namespace App\Plugins\WhatsAppPlugin\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Serves the module menu icon.
 *
 * Public by design: the browser fetches this as an image, so it must not sit
 * behind Secure_Controller's auth redirect.
 */
class IconController extends BaseController
{
    public function getIcon(): ResponseInterface
    {
        $path = APPPATH . 'Plugins/WhatsAppPlugin/whatsapp.svg';

        if (! is_file($path)) {
            return $this->response->setStatusCode(404)->setBody('');
        }

        return $this->response
            ->setHeader('Content-Type', 'image/svg+xml')
            ->setBody((string) file_get_contents($path));
    }
}
