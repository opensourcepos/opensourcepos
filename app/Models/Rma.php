<?php

namespace App\Models;

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;

/**
 * Rma class
 *
 * Records Return Merchandise Authorizations in two flavours:
 *   - STOCK  units: defective stock found upon opening. Quantity is deducted
 *     from the location (returned to the supplier) at RMA creation, and added
 *     back later when the item is resolved as replacement or repair.
 *   - CLIENT units: items already sold to a customer. No quantity change.
 *
 * A single resolution applies to the whole RMA (per-RMA).
 */
class Rma extends Model
{
    public const TYPE_STOCK               = 0;
    public const TYPE_CLIENT              = 1;
    public const RESOLUTION_REPLACEMENT   = 'replacement';
    public const RESOLUTION_CREDIT_MEMO   = 'credit_memo';
    public const RESOLUTION_REPAIR        = 'repair';
    public const RESOLUTION_VOID_WARRANTY = 'void_warranty';

    // Stock-unit resolutions that add the quantity back to stock
    public const STOCK_ADD_BACK = [self::RESOLUTION_REPLACEMENT, self::RESOLUTION_REPAIR];

    protected $table            = 'rmas';
    protected $primaryKey       = 'rma_id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'rma_time',
        'employee_id',
        'rma_type',
        'location_id',
        'supplier_id',
        'customer_id',
        'sale_id',
        'resolution',
        'resolved_by',
        'resolved_time',
        'comment',
    ];

    public function get_info(int $rma_id): ResultInterface
    {
        $builder = $this->db->table('rmas AS rmas');
        $builder->join('stock_locations AS location', 'location.location_id = rmas.location_id', 'LEFT');
        $builder->join('people AS employee', 'employee.person_id = rmas.employee_id', 'LEFT');
        $builder->join('people AS resolved', 'resolved.person_id = rmas.resolved_by', 'LEFT');
        $builder->join('suppliers AS supplier', 'supplier.person_id = rmas.supplier_id', 'LEFT');
        $builder->join('people AS supplier_people', 'supplier_people.person_id = supplier.person_id', 'LEFT');
        $builder->join('customers AS customer', 'customer.person_id = rmas.customer_id', 'LEFT');
        $builder->join('people AS customer_people', 'customer_people.person_id = customer.person_id', 'LEFT');
        $builder->select(
            'rmas.rma_id,
            rmas.rma_time,
            rmas.employee_id,
            rmas.rma_type,
            rmas.location_id,
            rmas.supplier_id,
            rmas.customer_id,
            rmas.resolution,
            rmas.resolved_by,
            rmas.resolved_time,
            rmas.comment,
            rmas.sale_id,
            CONCAT(employee.first_name, " ", employee.last_name) AS employee_name,
            CONCAT(resolved.first_name, " ", resolved.last_name) AS resolved_name,
            location.location_name,
            CONCAT(supplier_people.first_name, " ", supplier_people.last_name) AS supplier_name,
            CONCAT(customer_people.first_name, " ", customer_people.last_name) AS customer_name',
        );
        $builder->where('rmas.rma_id', $rma_id);

        return $builder->get();
    }

    public function get_rma_items(int $rma_id): ResultInterface
    {
        $builder = $this->db->table('rma_items AS rma_items');
        $builder->join('items AS items', 'items.item_id = rma_items.item_id');
        $builder->select(
            'rma_items.item_id,
            rma_items.line,
            rma_items.quantity,
            rma_items.item_location,
            rma_items.description,
            rma_items.issue,
            rma_items.serial_number,
            items.item_number,
            items.name',
        );
        $builder->where('rma_items.rma_id', $rma_id);
        $builder->orderBy('rma_items.line', 'asc');

        return $builder->get();
    }

    /**
     * Lists RMAs, optionally filtered by the locations a user may see.
     *
     * @param list<int> $locations locations the user is allowed to act on
     */
    public function get_all(array $locations): ResultInterface
    {
        $builder = $this->db->table('rmas AS rmas');
        $builder->join('stock_locations AS location', 'location.location_id = rmas.location_id', 'LEFT');
        $builder->join('people AS employee', 'employee.person_id = rmas.employee_id', 'LEFT');
        $builder->join('suppliers AS supplier', 'supplier.person_id = rmas.supplier_id', 'LEFT');
        $builder->join('people AS supplier_people', 'supplier_people.person_id = supplier.person_id', 'LEFT');
        $builder->join('customers AS customer', 'customer.person_id = rmas.customer_id', 'LEFT');
        $builder->join('people AS customer_people', 'customer_people.person_id = customer.person_id', 'LEFT');
        $builder->select(
            'rmas.rma_id,
            rmas.rma_time,
            rmas.rma_type,
            rmas.location_id,
            rmas.supplier_id,
            rmas.customer_id,
            rmas.sale_id,
            rmas.resolution,
            rmas.comment,
            CONCAT(employee.first_name, " ", employee.last_name) AS employee_name,
            location.location_name,
            CONCAT(supplier_people.first_name, " ", supplier_people.last_name) AS supplier_name,
            CONCAT(customer_people.first_name, " ", customer_people.last_name) AS customer_name',
        );

        if (! empty($locations)) {
            $builder->whereIn('rmas.location_id', $locations);
        }

        $builder->orderBy('rmas.rma_id', 'desc');

        return $builder->get();
    }

    /**
     * Saves a new RMA. For STOCK type the item quantity is deducted from the
     * location immediately (returned to the supplier for evaluation).
     *
     * @param array $items Cart items keyed by line, each with item_id/line/quantity/description
     *
     * @return int The rma_id on success or -1 on failure
     */
    public function save_value(array $items, int $employee_id, int $rma_type, int $location_id, ?int $supplier_id, ?int $customer_id, ?int $sale_id, string $comment): int
    {
        if (count($items) === 0) {
            return -1;
        }

        $this->db->transStart();

        $this->db->table('rmas')->insert([
            'rma_time'      => date('Y-m-d H:i:s'),
            'employee_id'   => $employee_id,
            'rma_type'      => $rma_type,
            'location_id'   => $location_id,
            'supplier_id'   => $supplier_id,
            'customer_id'   => $customer_id,
            'sale_id'       => $sale_id,
            'resolution'    => null,
            'resolved_by'   => null,
            'resolved_time' => null,
            'comment'       => $comment,
        ]);
        $rma_id = $this->db->insertID();

        $item_quantity = model(Item_quantity::class);
        $inventory     = model('Inventory');
        $builder       = $this->db->table('rma_items');

        foreach ($items as $line => $item_data) {
            $quantity = (float) $item_data['quantity'];

            $builder->insert([
                'rma_id'        => $rma_id,
                'item_id'       => $item_data['item_id'],
                'line'          => $item_data['line'],
                'quantity'      => $quantity,
                'item_location' => $item_data['item_location'] ?? $location_id,
                'description'   => $item_data['description'] ?? '',
                'issue'         => $item_data['issue'] ?? '',
                'serial_number' => $item_data['serial_number'] ?? '',
            ]);

            // STOCK units: returned to the supplier, so deduct the quantity.
            if ($rma_type === self::TYPE_STOCK) {
                $item_quantity->change_quantity($item_data['item_id'], $location_id, -$quantity);

                $inventory->insert([
                    'trans_date'      => date('Y-m-d H:i:s'),
                    'trans_items'     => $item_data['item_id'],
                    'trans_user'      => $employee_id,
                    'trans_comment'   => 'RMA SEND OUT ' . $rma_id,
                    'trans_inventory' => -$quantity,
                    'trans_location'  => $location_id,
                ], false);
            }
        }

        $this->db->transComplete();

        return $this->db->transStatus() ? $rma_id : -1;
    }

    /**
     * Resolves an RMA. Only applies if it is currently unresolved.
     *
     * For STOCK type, replacement and repair add the quantity back to stock
     * while credit memo and void warranty do not. CLIENT type never changes
     * stock quantities.
     */
    public function resolve(int $rma_id, string $resolution, int $resolved_by): bool
    {
        $rma = $this->get_info($rma_id)->getRowArray();

        if ($rma === null || $rma['resolution'] !== null) {
            return false;
        }

        $this->db->transStart();

        // STOCK units: replacement or repair restores the quantity.
        if ((int) $rma['rma_type'] === self::TYPE_STOCK && in_array($resolution, self::STOCK_ADD_BACK, true)) {
            $item_quantity = model(Item_quantity::class);
            $inventory     = model('Inventory');
            $items         = $this->get_rma_items($rma_id)->getResultArray();

            foreach ($items as $item) {
                $quantity = (float) $item['quantity'];

                $item_quantity->change_quantity($item['item_id'], (int) $rma['location_id'], $quantity);

                $inventory->insert([
                    'trans_date'      => date('Y-m-d H:i:s'),
                    'trans_items'     => $item['item_id'],
                    'trans_user'      => $resolved_by,
                    'trans_comment'   => 'RMA RETURN ' . $rma_id,
                    'trans_inventory' => $quantity,
                    'trans_location'  => $rma['location_id'],
                ], false);
            }
        }

        $this->db->table('rmas')
            ->where('rma_id', $rma_id)
            ->where('resolution', null)
            ->update([
                'resolution'    => $resolution,
                'resolved_by'   => $resolved_by,
                'resolved_time' => date('Y-m-d H:i:s'),
            ]);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Deletes an unresolved RMA, reversing the stock deduction done for
     * stock units.
     */
    public function delete_value(int $rma_id): bool
    {
        $rma = $this->get_info($rma_id)->getRowArray();

        if ($rma === null || $rma['resolution'] !== null) {
            return false;
        }

        $this->db->transStart();

        $builder = $this->db->table('rma_items')->where('rma_id', $rma_id);
        $items   = $builder->get()->getResultArray();

        if ((int) $rma['rma_type'] === self::TYPE_STOCK && count($items) > 0) {
            $item_quantity = model(Item_quantity::class);
            $inventory     = model('Inventory');
            $location_id   = (int) $rma['location_id'];

            foreach ($items as $item) {
                $quantity = (float) $item['quantity'];

                $item_quantity->change_quantity($item['item_id'], $location_id, $quantity);

                $inventory->insert([
                    'trans_date'      => date('Y-m-d H:i:s'),
                    'trans_items'     => $item['item_id'],
                    'trans_user'      => (int) $rma['employee_id'],
                    'trans_comment'   => 'RMA DELETE ' . $rma_id,
                    'trans_inventory' => $quantity,
                    'trans_location'  => $location_id,
                ], false);
            }
        }

        $this->db->table('rma_items')->where('rma_id', $rma_id)->delete();
        $this->db->table('rmas')->where('rma_id', $rma_id)->delete();

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Returns the resolution options allowed for an RMA type.
     *
     * @return array<string,string> option value => language key
     */
    public function resolution_options(int $rma_type): array
    {
        $options = [
            self::RESOLUTION_REPLACEMENT => lang('Rmas.resolution_replacement'),
            self::RESOLUTION_REPAIR      => lang('Rmas.resolution_repair'),
        ];

        if ($rma_type === self::TYPE_STOCK) {
            $options[self::RESOLUTION_CREDIT_MEMO] = lang('Rmas.resolution_credit_memo');
        }

        $options[self::RESOLUTION_VOID_WARRANTY] = lang('Rmas.resolution_void_warranty');

        return $options;
    }
}
