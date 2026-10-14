<?php

namespace App\Controllers\Api;

use App\Models\Item;
use App\Models\Item_quantity;
use CodeIgniter\HTTP\ResponseInterface;

class Items extends BaseController
{
    protected Item $itemModel;
    protected Item_quantity $itemQuantityModel;
    
    protected array $allowedSortFields = ['item_id', 'name', 'category', 'cost_price', 'unit_price'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->itemModel = model(Item::class);
        $this->itemQuantityModel = model(Item_quantity::class);
    }

    public function index(): ResponseInterface
    {
        if (!$this->hasPermission('items')) {
            return $this->respondUnauthorized();
        }
        
        $search = $this->request->getGet('search');
        $pagination = $this->getPagination();
        $sort = $this->getSort($this->allowedSortFields, 'name');
        $stockLocation = $this->request->getGet('stockLocation');
        
        $builder = $this->itemModel->builder();
        $builder->where('deleted', 0);
        
        if ($search) {
            $builder->groupStart();
            $builder->like('name', $search);
            $builder->orLike('item_number', $search);
            $builder->orLike('category', $search);
            $builder->orLike('description', $search);
            $builder->groupEnd();
        }
        
        $total = $builder->countAllResults(false);
        
        $dbSort = $this->mapSortField($sort['sort']);
        $builder->orderBy($dbSort, $sort['order']);
        $builder->limit($pagination['limit'], $pagination['offset']);
        
        $items = $builder->get()->getResultArray();
        
        return $this->respondSuccess([
            'total' => $total,
            'offset' => $pagination['offset'],
            'limit' => $pagination['limit'],
            'rows' => $this->transformCollection($items)
        ]);
    }

    public function show($id = null): ResponseInterface
    {
        if (!$this->hasPermission('items')) {
            return $this->respondUnauthorized();
        }
        
        $item = $this->itemModel->find($id);
        
        if (!$item || $item->deleted) {
            return $this->respondNotFound('Item not found');
        }
        
        return $this->respondSuccess($this->transformItem($item));
    }

    public function create(): ResponseInterface
    {
        if (!$this->hasPermission('items')) {
            return $this->respondUnauthorized();
        }
        
        $data = $this->request->getJSON(true);
        if (empty($data)) {
            $data = $this->request->getPost();
        }
        
        $snakeData = $this->toSnakeCase($data);
        
        if (!empty($snakeData['item_number'])) {
            if ($this->itemModel->item_number_exists($snakeData['item_number'])) {
                return $this->respondError('Item number already exists', 409);
            }
        }
        
        $itemId = $this->itemModel->save_value($snakeData);
        
        if ($itemId) {
            return $this->respondCreated(['id' => $itemId], 'Item created successfully');
        }
        
        return $this->respondError('Failed to create item');
    }

    public function update($id = null): ResponseInterface
    {
        if (!$this->hasPermission('items')) {
            return $this->respondUnauthorized();
        }
        
        $item = $this->itemModel->find($id);
        
        if (!$item || $item->deleted) {
            return $this->respondNotFound('Item not found');
        }
        
        $data = $this->request->getJSON(true);
        if (empty($data)) {
            $data = $this->request->getRawInput();
        }
        
        $snakeData = $this->toSnakeCase($data);
        $snakeData['item_id'] = $id;
        
        $success = $this->itemModel->save_value($snakeData);
        
        if ($success) {
            return $this->respondSuccess([], 200, 'Item updated successfully');
        }
        
        return $this->respondError('Failed to update item');
    }

    public function delete($id = null): ResponseInterface
    {
        if (!$this->hasPermission('items')) {
            return $this->respondUnauthorized();
        }
        
        $item = $this->itemModel->find($id);
        
        if (!$item || $item->deleted) {
            return $this->respondNotFound('Item not found');
        }
        
        $success = $this->itemModel->delete($id);
        
        if ($success) {
            return $this->respondSuccess([], 200, 'Item deleted successfully');
        }
        
        return $this->respondError('Failed to delete item');
    }

    public function batchDelete(): ResponseInterface
    {
        if (!$this->hasPermission('items')) {
            return $this->respondUnauthorized();
        }
        
        $data = $this->request->getJSON(true);
        $ids = $data['ids'] ?? [];
        
        if (empty($ids)) {
            return $this->respondError('No item IDs provided');
        }
        
        $success = $this->itemModel->delete_list($ids);
        
        if ($success) {
            return $this->respondSuccess([], 200, 'Items deleted successfully');
        }
        
        return $this->respondError('Failed to delete items');
    }

    public function quantities($id = null): ResponseInterface
    {
        if (!$this->hasPermission('items')) {
            return $this->respondUnauthorized();
        }
        
        $item = $this->itemModel->find($id);
        
        if (!$item || $item->deleted) {
            return $this->respondNotFound('Item not found');
        }
        
        $locations = model('App\Models\Stock_location')->get_all();
        $quantities = [];
        
        foreach ($locations as $location) {
            $qty = $this->itemQuantityModel->get_item_quantity($id, $location->location_id);
            $quantities[] = [
                'locationId' => (int) $location->location_id,
                'locationName' => $location->location_name,
                'quantity' => $qty ? (float) $qty->quantity : 0
            ];
        }
        
        return $this->respondSuccess([
            'itemId' => (int) $id,
            'quantities' => $quantities
        ]);
    }

    public function suggest(): ResponseInterface
    {
        if (!$this->hasPermission('items')) {
            return $this->respondUnauthorized();
        }
        
        $term = $this->request->getGet('term');
        $limit = (int) ($this->request->getGet('limit') ?? 25);
        
        if (empty($term)) {
            return $this->respondSuccess(['suggestions' => []]);
        }
        
        $suggestions = $this->itemModel->get_search_suggestions($term, $limit);
        
        return $this->respondSuccess(['suggestions' => $suggestions]);
    }

    private function mapSortField(string $field): string
    {
        $map = [
            'itemId' => 'item_id',
            'name' => 'name',
            'category' => 'category',
            'costPrice' => 'cost_price',
            'unitPrice' => 'unit_price'
        ];
        
        return $map[$field] ?? 'name';
    }
}