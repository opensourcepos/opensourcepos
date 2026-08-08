<?php

namespace App\Exceptions;

use CodeIgniter\HTTP\ResponsableInterface;
use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;

class AccessDeniedRedirectException extends RuntimeException implements ResponsableInterface
{
    public function __construct(private readonly string $redirectUrl)
    {
        parent::__construct('Access denied, redirecting to: ' . $redirectUrl);
    }

    public function getResponse(): ResponseInterface
    {
        return service('response')->redirect($this->redirectUrl);
    }
}
