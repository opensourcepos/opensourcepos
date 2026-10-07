<?php

namespace App\Filters;

use App\Models\Employee;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\Exceptions\RedirectException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class IsLoggedIn implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $employee = model(Employee::class);

        if (!$employee->is_logged_in()) {
            if ($request->isAJAX()) {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Session expired. Please log in again.',
                    ]);
            }

            throw new RedirectException('login');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
