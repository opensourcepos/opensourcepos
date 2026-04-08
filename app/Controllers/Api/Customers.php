<?php

namespace App\Controllers\Api;

use App\Models\Customer;
use App\Models\Person;
use CodeIgniter\HTTP\ResponseInterface;

class Customers extends BaseController
{
    protected Customer $customerModel;
    protected Person $personModel;
    
    protected array $allowedSortFields = ['person_id', 'last_name', 'first_name', 'email', 'company_name'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->customerModel = model(Customer::class);
        $this->personModel = model(Person::class);
    }

    public function index(): ResponseInterface
    {
        if (!$this->hasPermission('customers')) {
            return $this->respondUnauthorized();
        }
        
        $search = $this->request->getGet('search');
        $pagination = $this->getPagination();
        $sort = $this->getSort($this->allowedSortFields, 'last_name');
        
        $builder = $this->customerModel->builder();
        $builder->select('customers.*, people.*');
        $builder->join('people', 'people.person_id = customers.person_id');
        $builder->where('customers.deleted', 0);
        
        if ($search) {
            $builder->groupStart();
            $builder->like('people.first_name', $search);
            $builder->orLike('people.last_name', $search);
            $builder->orLike('people.email', $search);
            $builder->orLike('customers.account_number', $search);
            $builder->orLike('customers.company_name', $search);
            $builder->groupEnd();
        }
        
        $total = $builder->countAllResults(false);
        
        $dbSort = $this->mapSortField($sort['sort']);
        $builder->orderBy($dbSort, $sort['order']);
        $builder->limit($pagination['limit'], $pagination['offset']);
        
        $customers = $builder->get()->getResultArray();
        
        return $this->respondSuccess([
            'total' => $total,
            'offset' => $pagination['offset'],
            'limit' => $pagination['limit'],
            'rows' => $this->transformCollection($customers)
        ]);
    }

    public function show($id = null): ResponseInterface
    {
        if (!$this->hasPermission('customers')) {
            return $this->respondUnauthorized();
        }
        
        $customer = $this->customerModel->get_info($id);
        
        if (empty($customer) || $customer->deleted) {
            return $this->respondNotFound('Customer not found');
        }
        
        $person = (array) $this->personModel->get_info($id);
        $customer = (array) $customer;
        $data = array_merge($person, $customer);
        
        return $this->respondSuccess($this->transformItem($data));
    }

    public function create(): ResponseInterface
    {
        if (!$this->hasPermission('customers')) {
            return $this->respondUnauthorized();
        }
        
        $data = $this->request->getJSON(true);
        if (empty($data)) {
            $data = $this->request->getPost();
        }
        
        $data = $this->toSnakeCase($data);
        
        $rules = [
            'first_name' => 'required|max_length[255]',
            'last_name' => 'required|max_length[255]',
        ];
        
        $snakeData = [];
        foreach ($data as $key => $value) {
            $snakeKey = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $key));
            $snakeData[$snakeKey] = $value;
        }
        
        $personData = array_intersect_key($snakeData, array_flip([
            'first_name', 'last_name', 'gender', 'phone_number', 'email',
            'address_1', 'address_2', 'city', 'state', 'zip', 'country', 'comments'
        ]));
        
        $customerData = array_intersect_key($snakeData, array_flip([
            'account_number', 'taxable', 'tax_id', 'sales_tax_code_id',
            'discount', 'discount_type', 'company_name', 'package_id', 'consent'
        ]));
        $customerData['employee_id'] = $this->employeeId;
        
        $personId = false;
        $success = $this->personModel->save_value($personData);
        
        if ($success && isset($personData['person_id'])) {
            $personId = $personData['person_id'];
            $customerData['person_id'] = $personId;
            $success = $this->customerModel->save_value($customerData);
        }
        
        if ($success) {
            return $this->respondCreated(['id' => $personId], 'Customer created successfully');
        }
        
        return $this->respondError('Failed to create customer');
    }

    public function update($id = null): ResponseInterface
    {
        if (!$this->hasPermission('customers')) {
            return $this->respondUnauthorized();
        }
        
        $customer = $this->customerModel->get_info($id);
        
        if (empty($customer) || $customer->deleted) {
            return $this->respondNotFound('Customer not found');
        }
        
        $data = $this->request->getJSON(true);
        if (empty($data)) {
            $data = $this->request->getRawInput();
        }
        
        $snakeData = [];
        foreach ($data as $key => $value) {
            $snakeKey = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $key));
            $snakeData[$snakeKey] = $value;
        }
        
        $personData = array_intersect_key($snakeData, array_flip([
            'first_name', 'last_name', 'gender', 'phone_number', 'email',
            'address_1', 'address_2', 'city', 'state', 'zip', 'country', 'comments'
        ]));
        
        $customerData = array_intersect_key($snakeData, array_flip([
            'account_number', 'taxable', 'tax_id', 'sales_tax_code_id',
            'discount', 'discount_type', 'company_name', 'package_id', 'consent'
        ]));
        
        if (!empty($personData)) {
            $this->personModel->save_value($personData, $id);
        }
        
        if (!empty($customerData)) {
            $this->customerModel->save_value($customerData, $id);
        }
        
        return $this->respondSuccess([], 200, 'Customer updated successfully');
    }

    public function delete($id = null): ResponseInterface
    {
        if (!$this->hasPermission('customers')) {
            return $this->respondUnauthorized();
        }
        
        $customer = $this->customerModel->get_info($id);
        
        if (empty($customer) || $customer->deleted) {
            return $this->respondNotFound('Customer not found');
        }
        
        $success = $this->customerModel->delete($id);
        
        if ($success) {
            return $this->respondSuccess([], 200, 'Customer deleted successfully');
        }
        
        return $this->respondError('Failed to delete customer');
    }

    public function batchDelete(): ResponseInterface
    {
        if (!$this->hasPermission('customers')) {
            return $this->respondUnauthorized();
        }
        
        $data = $this->request->getJSON(true);
        $ids = $data['ids'] ?? [];
        
        if (empty($ids)) {
            return $this->respondError('No customer IDs provided');
        }
        
        $success = $this->customerModel->delete_list($ids);
        
        if ($success) {
            return $this->respondSuccess([], 200, 'Customers deleted successfully');
        }
        
        return $this->respondError('Failed to delete customers');
    }

    public function suggest(): ResponseInterface
    {
        if (!$this->hasPermission('customers')) {
            return $this->respondUnauthorized();
        }
        
        $term = $this->request->getGet('term');
        $limit = (int) ($this->request->getGet('limit') ?? 25);
        
        if (empty($term)) {
            return $this->respondSuccess(['suggestions' => []]);
        }
        
        $suggestions = $this->customerModel->get_search_suggestions($term, $limit);
        
        return $this->respondSuccess(['suggestions' => $suggestions]);
    }

    private function mapSortField(string $field): string
    {
        $map = [
            'personId' => 'people.person_id',
            'lastName' => 'people.last_name',
            'firstName' => 'people.first_name',
            'email' => 'people.email',
            'companyName' => 'customers.company_name'
        ];
        
        return $map[$field] ?? 'people.last_name';
    }
}