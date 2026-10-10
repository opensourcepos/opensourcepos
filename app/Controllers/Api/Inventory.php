<?php

namespace App\Controllers\Api;

use App\Models\Inventory as InventoryModel;
use App\Models\Item;
use App\Models\Item_quantity;
use CodeIgniter\HTTP\ResponseInterface;

class Inventory extends BaseController
{
    protected InventoryModel $inventory;
    protected Item $item;
    protected Item_quantity $itemQuantity;
    
    protected array $allowedSortFields = ['trans_id', 'trans_date', 'trans_items'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->inventory = model(InventoryModel::class);
        $this->item = model(Item::class);
        $this->itemQuantity = model(Item_quantity::class);
    }

    public function index(): ResponseInterface
    {
        if (!$this->hasPermission('items')) {
            return $this->respondUnauthorized();
        }
        
        $itemId = $this->request->getGet('itemId');
        $locationId = $this->request->getGet('locationId');
        $pagination = $this->getPagination();
        $sort = $this->getSort($this->allowedSortFields, 'trans_date');
        
        $builder = $this->inventory->builder();
        
        if ($itemId) {
            $builder->where('trans_items', $itemId);
        }
        
        if ($locationId) {
            $builder->where('trans_location', $locationId);
        }
        
        $total = $builder->countAllResults(false);
        
        $builder->orderBy($sort['sort'], $sort['order']);
        $builder->limit($pagination['limit'], $pagination['offset']);
        
        $transactions = $builder->get()->getResultArray();
        
        return $this->respondSuccess([
            'total' => $total,
            'offset' => $pagination['offset'],
            'limit' => $pagination['limit'],
            'rows' => $this->transformCollection($transactions)
        ]);
    }

    public function create(): ResponseInterface
    {
        if (!$this->hasPermission('items')) {
            return $this->respondUnauthorized();
        }
        
        $data = $this->request->getJSON(true);
        
        if (isset($data['adjustments']) && is_array($data['adjustments'])) {
            return $this->bulkAdjust($data['adjustments']);
        }
        
        return $this->singleAdjust($data);
    }

    private function singleAdjust(array $data): ResponseInterface
    {
        if (empty($data['itemId'])) {
            return $this->respondError('itemId is required');
        }
        
        if (!isset($data['quantity'])) {
            return $this->respondError('quantity is required');
        }
        
        $mode = $data['mode'] ?? 'adjust';
        
        if (!in_array($mode, ['adjust', 'set'])) {
            return $this->respondError('mode must be "adjust" or "set"');
        }
        
        $item = $this->item->find($data['itemId']);
        if (!$item || $item->deleted) {
            return $this->respondNotFound('Item not found');
        }
        
        $locationId = $data['locationId'] ?? 1;
        $comment = $data['comment'] ?? 'API inventory adjustment';
        $quantity = (float) $data['quantity'];
        
        if ($mode === 'set') {
            $currentQty = $this->itemQuantity->get_item_quantity($data['itemId'], $locationId);
            $currentQty = $currentQty ? (float) $currentQty->quantity : 0;
            $adjustment = $quantity - $currentQty;
            
            if ($adjustment == 0) {
                return $this->respondSuccess([
                    'itemId' => (int) $data['itemId'],
                    'locationId' => (int) $locationId,
                    'newQuantity' => $quantity,
                    'mode' => $mode
                ], 200, 'Quantity already at requested level');
            }
        } else {
            $adjustment = $quantity;
        }
        
        $invData = [
            'trans_date' => date('Y-m-d H:i:s'),
            'trans_items' => $data['itemId'],
            'trans_user' => $this->employeeId,
            'trans_location' => $locationId,
            'trans_comment' => $comment,
            'trans_inventory' => $adjustment
        ];
        
        $this->inventory->insert($invData);
        $this->itemQuantity->change_quantity($data['itemId'], $locationId, $adjustment);
        
        $newQty = $this->itemQuantity->get_item_quantity($data['itemId'], $locationId);
        
        return $this->respondSuccess([
            'itemId' => (int) $data['itemId'],
            'locationId' => (int) $locationId,
            'adjustment' => $adjustment,
            'newQuantity' => $newQty ? (float) $newQty->quantity : 0,
            'mode' => $mode
        ], 200, 'Inventory adjusted successfully');
    }

    private function bulkAdjust(array $adjustments): ResponseInterface
    {
        $results = [];
        $processed = 0;
        $errors = [];
        
        $this->inventory->db->transStart();
        
        foreach ($adjustments as $adjustment) {
            $itemId = $adjustment['itemId'] ?? $adjustment['item_id'] ?? null;
            
            if (!$itemId) {
                $errors[] = ['itemId' => null, 'success' => false, 'error' => 'itemId is required'];
                continue;
            }
            
            $item = $this->item->find($itemId);
            if (!$item || $item->deleted) {
                $errors[] = ['itemId' => $itemId, 'success' => false, 'error' => 'Item not found'];
                continue;
            }
            
            $mode = $adjustment['mode'] ?? 'adjust';
            $locationId = $adjustment['locationId'] ?? $adjustment['location_id'] ?? 1;
            $quantity = (float) ($adjustment['quantity'] ?? 0);
            $comment = $adjustment['comment'] ?? 'Bulk API inventory adjustment';
            
            if ($mode === 'set') {
                $currentQty = $this->itemQuantity->get_item_quantity($itemId, $locationId);
                $currentQty = $currentQty ? (float) $currentQty->quantity : 0;
                $adjustmentQty = $quantity - $currentQty;
            } else {
                $adjustmentQty = $quantity;
            }
            
            $invData = [
                'trans_date' => date('Y-m-d H:i:s'),
                'trans_items' => $itemId,
                'trans_user' => $this->employeeId,
                'trans_location' => $locationId,
                'trans_comment' => $comment,
                'trans_inventory' => $adjustmentQty
            ];
            
            $this->inventory->insert($invData);
            $this->itemQuantity->change_quantity($itemId, $locationId, $adjustmentQty);
            
            $results[] = ['itemId' => $itemId, 'success' => true];
            $processed++;
        }
        
        $this->inventory->db->transComplete();
        
        $response = [
            'processed' => $processed,
            'total' => count($adjustments),
            'results' => $results
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
            $response['success'] = false;
            $response['message'] = 'Some adjustments failed';
        } else {
            $response['success'] = true;
            $response['message'] = 'All adjustments processed successfully';
        }
        
        return $this->respondSuccess($response);
    }
}