<?php

namespace App\Controllers\Api;

use App\Models\Supplier;
use App\Models\Person;
use CodeIgniter\HTTP\ResponseInterface;

class Suppliers extends BaseController
{
    protected Supplier $supplierModel;
    protected Person $personModel;
    
    protected array $allowedSortFields = ['person_id', 'last_name', 'company_name'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->supplierModel = model(Supplier::class);
        $this->personModel = model(Person::class);
    }

    public function index(): ResponseInterface
    {
        if (!$this->hasPermission('suppliers')) {
            return $this->respondUnauthorized();
        }
        
        $search = $this->request->getGet('search');
        $pagination = $this->getPagination();
        $sort = $this->getSort($this->allowedSortFields, 'companyName');
        
        $builder = $this->supplierModel->builder();
        $builder->select('suppliers.*, people.*');
        $builder->join('people', 'people.person_id = suppliers.person_id');
        $builder->where('suppliers.deleted', 0);
        
        if ($search) {
            $builder->groupStart();
            $builder->like('people.first_name', $search);
            $builder->orLike('people.last_name', $search);
            $builder->orLike('people.email', $search);
            $builder->orLike('suppliers.account_number', $search);
            $builder->orLike('suppliers.company_name', $search);
            $builder->groupEnd();
        }
        
        $total = $builder->countAllResults(false);
        
        $dbSort = $this->mapSortField($sort['sort']);
        $builder->orderBy($dbSort, $sort['order']);
        $builder->limit($pagination['limit'], $pagination['offset']);
        
        $suppliers = $builder->get()->getResultArray();
        
        return $this->respondSuccess([
            'total' => $total,
            'offset' => $pagination['offset'],
            'limit' => $pagination['limit'],
            'rows' => $this->transformCollection($suppliers)
        ]);
    }

    public function show($id = null): ResponseInterface
    {
        if (!$this->hasPermission('suppliers')) {
            return $this->respondUnauthorized();
        }
        
        $supplier = $this->supplierModel->get_info($id);
        
        if (empty($supplier) || $supplier->deleted) {
            return $this->respondNotFound('Supplier not found');
        }
        
        $person = (array) $this->personModel->get_info($id);
        $supplier = (array) $supplier;
        $data = array_merge($person, $supplier);
        
        return $this->respondSuccess($this->transformItem($data));
    }

    public function create(): ResponseInterface
    {
        if (!$this->hasPermission('suppliers')) {
            return $this->respondUnauthorized();
        }
        
        $data = $this->request->getJSON(true);
        if (empty($data)) {
            $data = $this->request->getPost();
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
        
        $supplierData = array_intersect_key($snakeData, array_flip([
            'company_name', 'account_number', 'tax_id', 'agency_name', 'category'
        ]));
        
        $personId = false;
        $success = $this->personModel->save_value($personData);
        
        if ($success && isset($personData['person_id'])) {
            $personId = $personData['person_id'];
            $supplierData['person_id'] = $personId;
            $success = $this->supplierModel->save_value($supplierData);
        }
        
        if ($success) {
            return $this->respondCreated(['id' => $personId], 'Supplier created successfully');
        }
        
        return $this->respondError('Failed to create supplier');
    }

    public function update($id = null): ResponseInterface
    {
        if (!$this->hasPermission('suppliers')) {
            return $this->respondUnauthorized();
        }
        
        $supplier = $this->supplierModel->get_info($id);
        
        if (empty($supplier) || $supplier->deleted) {
            return $this->respondNotFound('Supplier not found');
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
        
        $supplierData = array_intersect_key($snakeData, array_flip([
            'company_name', 'account_number', 'tax_id', 'agency_name', 'category'
        ]));
        
        if (!empty($personData)) {
            $this->personModel->save_value($personData, $id);
        }
        
        if (!empty($supplierData)) {
            $this->supplierModel->save_value($supplierData, $id);
        }
        
        return $this->respondSuccess([], 200, 'Supplier updated successfully');
    }

    public function delete($id = null): ResponseInterface
    {
        if (!$this->hasPermission('suppliers')) {
            return $this->respondUnauthorized();
        }
        
        $supplier = $this->supplierModel->get_info($id);
        
        if (empty($supplier) || $supplier->deleted) {
            return $this->respondNotFound('Supplier not found');
        }
        
        $success = $this->supplierModel->delete($id);
        
        if ($success) {
            return $this->respondSuccess([], 200, 'Supplier deleted successfully');
        }
        
        return $this->respondError('Failed to delete supplier');
    }

    public function batchDelete(): ResponseInterface
    {
        if (!$this->hasPermission('suppliers')) {
            return $this->respondUnauthorized();
        }
        
        $data = $this->request->getJSON(true);
        $ids = $data['ids'] ?? [];
        
        if (empty($ids)) {
            return $this->respondError('No supplier IDs provided');
        }
        
        $success = $this->supplierModel->delete_list($ids);
        
        if ($success) {
            return $this->respondSuccess([], 200, 'Suppliers deleted successfully');
        }
        
        return $this->respondError('Failed to delete suppliers');
    }

    public function suggest(): ResponseInterface
    {
        if (!$this->hasPermission('suppliers')) {
            return $this->respondUnauthorized();
        }
        
        $term = $this->request->getGet('term');
        $limit = (int) ($this->request->getGet('limit') ?? 25);
        
        if (empty($term)) {
            return $this->respondSuccess(['suggestions' => []]);
        }
        
        $suggestions = $this->supplierModel->get_search_suggestions($term, $limit);
        
        return $this->respondSuccess(['suggestions' => $suggestions]);
    }

    private function mapSortField(string $field): string
    {
        $map = [
            'personId' => 'people.person_id',
            'lastName' => 'people.last_name',
            'companyName' => 'suppliers.company_name'
        ];
        
        return $map[$field] ?? 'suppliers.company_name';
    }
}