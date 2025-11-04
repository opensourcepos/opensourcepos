<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\OSPOS;

class Consignment extends Model
{
    protected $table = 'consignment_transactions';
    protected $primaryKey = 'consignment_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $allowedFields = [
        'sale_id',
        'sale_line',
        'item_id',
        'supplier_id',
        'location_id',
        'quantity',
        'sale_amount',
        'payout_rate',
        'payout_amount',
        'status',
        'payout_date',
        'notes',
        'sold_at',
        'created_at',
        'updated_at'
    ];
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELED = 'canceled';

    public function get_info(int $consignment_id): object
    {
        $builder = $this->getBuilder();
        $builder->select('
            consignment_transactions.*, 
            sales.sale_time,
            sales.sale_type,
            items.name AS item_name,
            items.item_number,
            suppliers.company_name,
            stock_locations.location_name,
            CONCAT(people.first_name, " ", people.last_name) AS contact_name
        ');
        $builder->join('sales', 'sales.sale_id = consignment_transactions.sale_id', 'left');
        $builder->join('items', 'items.item_id = consignment_transactions.item_id', 'left');
        $builder->join('suppliers', 'suppliers.person_id = consignment_transactions.supplier_id', 'left');
        $builder->join('people', 'people.person_id = suppliers.person_id', 'left');
        $builder->join('stock_locations', 'stock_locations.location_id = consignment_transactions.location_id', 'left');
        $builder->where('consignment_transactions.consignment_id', $consignment_id);

        $query = $builder->get();

        if ($query->getNumRows() === 1) {
            return $query->getRow();
        }

        return $this->getEmptyObject();
    }

    public function search(
        ?string $search,
        array $filters,
        ?int $rows = 0,
        ?int $limit_from = 0,
        ?string $sort = 'consignment_transactions.sold_at',
        ?string $order = 'desc',
        ?bool $count_only = false
    ) {
        $rows ??= 0;
        $limit_from ??= 0;
        $sort ??= 'consignment_transactions.sold_at';
        $order ??= 'desc';
        $count_only ??= false;

        $builder = $this->db->table($this->table . ' AS consignment_transactions');

        if ($count_only) {
            $builder->select('COUNT(DISTINCT consignment_transactions.consignment_id) AS count');
        } else {
            $builder->select('
                consignment_transactions.*,
                sales.sale_time,
                sales.sale_type,
                items.name AS item_name,
                items.item_number,
                suppliers.company_name,
                stock_locations.location_name,
                CONCAT(people.first_name, " ", people.last_name) AS contact_name
            ');
        }

        $builder->join('sales', 'sales.sale_id = consignment_transactions.sale_id', 'left');
        $builder->join('items', 'items.item_id = consignment_transactions.item_id', 'left');
        $builder->join('suppliers', 'suppliers.person_id = consignment_transactions.supplier_id', 'left');
        $builder->join('people', 'people.person_id = suppliers.person_id', 'left');
        $builder->join('stock_locations', 'stock_locations.location_id = consignment_transactions.location_id', 'left');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('items.name', $search)
                ->orLike('items.item_number', $search)
                ->orLike('suppliers.company_name', $search)
                ->orLike('consignment_transactions.notes', $search)
                ->orLike('consignment_transactions.sale_id', $search)
                ->groupEnd();
        }

        $config = config(OSPOS::class)->settings;
        $start_date = $filters['start_date'] ?? null;
        $end_date = $filters['end_date'] ?? null;

        if (!empty($start_date) && !empty($end_date)) {
            if (empty($config['date_or_time_format'])) {
                $builder->where('DATE(consignment_transactions.sold_at) >=', $start_date);
                $builder->where('DATE(consignment_transactions.sold_at) <=', $end_date);
            } else {
                $builder->where('consignment_transactions.sold_at >=', rawurldecode($start_date));
                $builder->where('consignment_transactions.sold_at <=', rawurldecode($end_date));
            }
        }

        $status_filters = [];
        if (!empty($filters['status_pending'])) {
            $status_filters[] = self::STATUS_PENDING;
        }
        if (!empty($filters['status_paid'])) {
            $status_filters[] = self::STATUS_PAID;
        }
        if (!empty($filters['status_canceled'])) {
            $status_filters[] = self::STATUS_CANCELED;
        }

        if (!empty($status_filters)) {
            $builder->whereIn('consignment_transactions.status', $status_filters);
        }

        if ($count_only) {
            $result = $builder->get()->getRow();
            return $result ? $result->count : 0;
        }

        if ($rows > 0) {
            $builder->limit($rows, $limit_from);
        }

        $builder->orderBy($sort, $order);

        return $builder->get();
    }

    public function get_found_rows(?string $search, array $filters): int
    {
        return $this->search($search, $filters, 0, 0, null, null, true);
    }

    public function mark_paid(array $consignment_ids, ?string $note = null, ?string $payout_date = null): bool
    {
        if (empty($consignment_ids)) {
            return false;
        }

        $data = [
            'status'      => self::STATUS_PAID,
            'payout_date' => $payout_date ?? date('Y-m-d H:i:s')
        ];

        if ($note !== null && $note !== '') {
            $data['notes'] = $note;
        }

        $builder = $this->db->table($this->table);
        $builder->whereIn('consignment_id', $consignment_ids);

        return $builder->update($data);
    }

    public function delete_by_sale(int $sale_id): void
    {
        $this->where('sale_id', $sale_id)->delete();
    }

    public function cancel_by_sale(int $sale_id): void
    {
        $builder = $this->db->table($this->table);
        $builder->where('sale_id', $sale_id);
        $builder->update([
            'status'      => self::STATUS_CANCELED,
            'payout_date' => null
        ]);
    }

    public function restore_by_sale(int $sale_id): void
    {
        $builder = $this->db->table($this->table);
        $builder->where('sale_id', $sale_id);
        $builder->where('status', self::STATUS_CANCELED);
        $builder->update([
            'status'      => self::STATUS_PENDING,
            'payout_date' => null
        ]);
    }

    private function getEmptyObject(): object
    {
        $empty = new \stdClass();
        foreach ($this->allowedFields as $field) {
            $empty->$field = null;
        }
        $empty->{$this->primaryKey} = NEW_ENTRY;

        return $empty;
    }
}
