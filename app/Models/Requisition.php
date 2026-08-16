<?php

namespace App\Models;

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;

/**
 * Requisition class
 *
 * A stock request from the requesting location (location_to) to a source
 * location (location_from). The workflow is two-step: the source location
 * approves first (PENDING -> SOURCE_APPROVED), then an administrator approves
 * and the stock physically moves (SOURCE_APPROVED -> APPROVED). Stock only
 * moves on final approval, reusing the transfer logic so lots and supplier
 * attribution are preserved.
 */
class Requisition extends Model
{
    public const STATUS_PENDING         = 0;
    public const STATUS_SOURCE_APPROVED = 1;
    public const STATUS_APPROVED        = 2;
    public const STATUS_REJECTED        = 3;

    protected $table            = 'requisitions';
    protected $primaryKey       = 'requisition_id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'requisition_time',
        'requested_by',
        'location_from',
        'location_to',
        'status',
        'comment',
        'approved_by',
        'approved_time',
    ];

    public function get_info(int $requisition_id): ResultInterface
    {
        $builder = $this->db->table('requisitions AS requisitions');
        $builder->join('stock_locations AS location_from', 'location_from.location_id = requisitions.location_from', 'LEFT');
        $builder->join('stock_locations AS location_to', 'location_to.location_id = requisitions.location_to', 'LEFT');
        $builder->join('people AS requested', 'requested.person_id = requisitions.requested_by', 'LEFT');
        $builder->join('people AS approved', 'approved.person_id = requisitions.approved_by', 'LEFT');
        $builder->select(
            'requisitions.requisition_id,
            requisitions.requisition_time,
            requisitions.requested_by,
            requisitions.location_from,
            requisitions.location_to,
            requisitions.status,
            requisitions.comment,
            requisitions.approved_by,
            requisitions.approved_time,
            CONCAT(requested.first_name, " ", requested.last_name) AS requested_name,
            CONCAT(approved.first_name, " ", approved.last_name) AS approved_name,
            location_from.location_name AS location_from_name,
            location_to.location_name AS location_to_name',
        );
        $builder->where('requisitions.requisition_id', $requisition_id);

        return $builder->get();
    }

    public function get_requisition_items(int $requisition_id): ResultInterface
    {
        $builder = $this->db->table('requisition_items AS requisition_items');
        $builder->join('items AS items', 'items.item_id = requisition_items.item_id');
        $builder->select(
            'requisition_items.item_id,
            requisition_items.line,
            requisition_items.quantity,
            requisition_items.item_location,
            requisition_items.description,
            items.item_number,
            items.name',
        );
        $builder->where('requisition_items.requisition_id', $requisition_id);
        $builder->orderBy('requisition_items.line', 'asc');

        return $builder->get();
    }

    /**
     * Lists requisitions, optionally filtered by the locations a user may see.
     *
     * @param list<int> $locations locations the user is allowed to act on
     */
    public function get_all(array $locations): ResultInterface
    {
        $builder = $this->db->table('requisitions AS requisitions');
        $builder->join('stock_locations AS location_from', 'location_from.location_id = requisitions.location_from', 'LEFT');
        $builder->join('stock_locations AS location_to', 'location_to.location_id = requisitions.location_to', 'LEFT');
        $builder->join('people AS requested', 'requested.person_id = requisitions.requested_by', 'LEFT');
        $builder->select(
            'requisitions.requisition_id,
            requisitions.requisition_time,
            requisitions.location_from,
            requisitions.location_to,
            requisitions.status,
            requisitions.comment,
            CONCAT(requested.first_name, " ", requested.last_name) AS requested_name,
            location_from.location_name AS location_from_name,
            location_to.location_name AS location_to_name',
        );

        if (! empty($locations)) {
            $builder->groupStart();
            $builder->whereIn('requisitions.location_from', $locations);
            $builder->orWhereIn('requisitions.location_to', $locations);
            $builder->groupEnd();
        }

        $builder->orderBy('requisitions.requisition_id', 'desc');

        return $builder->get();
    }

    /**
     * Saves a new requisition as PENDING.
     *
     * @param array $items Cart items keyed by line, each with item_id/line/quantity/description
     *
     * @return int The requisition_id on success or -1 on failure
     */
    public function save_value(array $items, int $requested_by, int $location_from, int $location_to, string $comment): int
    {
        if (count($items) === 0) {
            return -1;
        }

        if ($location_from === $location_to) {
            return -1;
        }

        $this->db->transStart();

        $this->db->table('requisitions')->insert([
            'requisition_time' => date('Y-m-d H:i:s'),
            'requested_by'     => $requested_by,
            'location_from'    => $location_from,
            'location_to'      => $location_to,
            'status'           => self::STATUS_PENDING,
            'comment'          => $comment,
            'approved_by'      => null,
            'approved_time'    => null,
        ]);
        $requisition_id = $this->db->insertID();

        $builder = $this->db->table('requisition_items');

        foreach ($items as $line => $item_data) {
            $builder->insert([
                'requisition_id' => $requisition_id,
                'item_id'        => $item_data['item_id'],
                'line'           => $item_data['line'],
                'quantity'       => (float) $item_data['quantity'],
                'item_location'  => $location_to,
                'description'    => $item_data['description'] ?? '',
            ]);
        }

        $this->db->transComplete();

        return $this->db->transStatus() ? $requisition_id : -1;
    }

    /**
     * Step 1: the source location approves the request.
     */
    public function approve_source(int $requisition_id): bool
    {
        $builder = $this->db->table('requisitions');
        $builder->where('requisition_id', $requisition_id);
        $builder->where('status', self::STATUS_PENDING);

        return $builder->update(['status' => self::STATUS_SOURCE_APPROVED]);
    }

    /**
     * Step 2: the administrator approves the request and stock moves.
     *
     * Moves stock using the transfer logic so lots and supplier attribution are
     * preserved, then marks the requisition APPROVED.
     *
     * @return bool true only when the stock transfer succeeded and the status updated
     */
    public function approve(int $requisition_id, int $approved_by): bool
    {
        $requisition = $this->get_info($requisition_id)->getRowArray();

        if ($requisition === null || (int) $requisition['status'] !== self::STATUS_SOURCE_APPROVED) {
            return false;
        }

        $items = $this->get_requisition_items($requisition_id)->getResultArray();

        $cart = [];

        foreach ($items as $item) {
            $cart[$item['line']] = [
                'item_id'     => (int) $item['item_id'],
                'line'        => (int) $item['line'],
                'quantity'    => (float) $item['quantity'],
                'description' => $item['description'],
            ];
        }

        $transfer    = model(Transfer::class);
        $transfer_id = $transfer->save_value(
            $cart,
            $approved_by,
            (int) $requisition['location_from'],
            (int) $requisition['location_to'],
            'Requisition ' . $requisition_id,
        );

        if ($transfer_id === -1) {
            return false;
        }

        $this->db->table('requisitions')
            ->where('requisition_id', $requisition_id)
            ->where('status', self::STATUS_SOURCE_APPROVED)
            ->update([
                'status'        => self::STATUS_APPROVED,
                'approved_by'   => $approved_by,
                'approved_time' => date('Y-m-d H:i:s'),
            ]);

        return true;
    }

    /**
     * Rejects a request that is still pending approval (either step).
     */
    public function reject(int $requisition_id, int $approved_by): bool
    {
        $builder = $this->db->table('requisitions');
        $builder->where('requisition_id', $requisition_id);
        $builder->where('status <', self::STATUS_APPROVED);

        return $builder->update([
            'status'        => self::STATUS_REJECTED,
            'approved_by'   => $approved_by,
            'approved_time' => date('Y-m-d H:i:s'),
        ]);
    }
}
