<?php

namespace App\Controllers\Api;

use App\Models\Employee;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class BaseController extends ResourceController
{
    protected Employee $employee;
    protected int $employeeId = 0;
    protected $format = 'json';

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        
        $this->employee = model(Employee::class);
        $this->employeeId = $request->employeeId ?? 0;
    }

    protected function hasPermission(string $moduleId): bool
    {
        return $this->employee->has_grant($moduleId, $this->employeeId);
    }

    protected function respondSuccess(array $data = [], int $code = 200, string $message = 'Success'): ResponseInterface
    {
        $response = ['success' => true];
        
        if ($message) {
            $response['message'] = $message;
        }
        
        $response = array_merge($response, $data);
        
        return $this->respond($response, $code);
    }

    protected function respondCreated(array $data = [], string $message = 'Resource created'): ResponseInterface
    {
        return $this->respondSuccess($data, 201, $message);
    }

    protected function respondError(string $message, int $code = 400): ResponseInterface
    {
        return $this->respond([
            'success' => false,
            'message' => $message
        ], $code);
    }

    protected function respondNotFound(string $message = 'Resource not found'): ResponseInterface
    {
        return $this->respondError($message, 404);
    }

    protected function respondUnauthorized(string $message = 'Unauthorized'): ResponseInterface
    {
        return $this->respondError($message, 403);
    }

    protected function respondValidationError(array $errors): ResponseInterface
    {
        return $this->respond([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ], 422);
    }

    protected function getPagination(): array
    {
        $offset = (int) ($this->request->getGet('offset') ?? 0);
        $limit = (int) ($this->request->getGet('limit') ?? 25);
        $limit = min(max($limit, 1), 100);
        $offset = max($offset, 0);
        
        return ['offset' => $offset, 'limit' => $limit];
    }

    protected function getSort(array $allowedFields, string $default = 'id', string $defaultOrder = 'asc'): array
    {
        $sort = $this->request->getGet('sort') ?? $default;
        $order = strtolower($this->request->getGet('order') ?? $defaultOrder);
        
        if (!in_array($sort, $allowedFields)) {
            $sort = $default;
        }
        
        if (!in_array($order, ['asc', 'desc'])) {
            $order = $defaultOrder;
        }
        
        return ['sort' => $sort, 'order' => $order];
    }

    protected function toCamelCase(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $camelKey = lcfirst(str_replace('_', '', ucwords($key, '_')));
            $result[$camelKey] = $value;
        }
        return $result;
    }

    protected function toSnakeCase(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $snakeKey = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $key));
            $result[$snakeKey] = $value;
        }
        return $result;
    }

    protected function transformItem(object|array $item, array $additional = []): array
    {
        $item = is_object($item) ? (array) $item : $item;
        return $this->toCamelCase(array_merge($item, $additional));
    }

    protected function transformCollection(array $items): array
    {
        return array_map([$this, 'transformItem'], $items);
    }
}