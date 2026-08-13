<?php

namespace App\Filters;

use App\Exceptions\AccessDeniedRedirectException;
use App\Models\Employee;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class IsLoggedIn implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $employee = model(Employee::class);

        if (!$employee->is_logged_in()) {
            throw new AccessDeniedRedirectException(base_url('login'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
