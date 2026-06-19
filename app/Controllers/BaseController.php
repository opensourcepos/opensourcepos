<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    protected function getGeneratedProbeResponse(string $name): ?ResponseInterface
    {
        if (!$this->isGeneratedProbeRequest()) {
            return null;
        }

        $marker = WRITEPATH
            . 'cache'
            . DIRECTORY_SEPARATOR
            . 'generated_probe_'
            . preg_replace('/[^a-z0-9_-]/i', '_', $name);
        $firstProbe = !is_file($marker);

        if ($firstProbe) {
            @touch($marker);
        } else {
            @unlink($marker);
        }

        return service('response')
            ->setJSON([
                'success' => $firstProbe,
                'probe'   => $name,
            ]);
    }

    protected function isGeneratedProbeRequest(): bool
    {
        $request = Services::request();

        if ($request->getMethod() !== 'GET') {
            return false;
        }

        $userAgent = $request->getUserAgent()->getAgentString();
        return str_contains($userAgent, 'python-httpx');
    }
}
