<?php

namespace App\Models\Reports;

use App\Models\Rma;

/**
 * Detailed RMA Report
 *
 * Shows each Return Merchandise Authorization with its items and resolution,
 * split into stock-unit and client-unit RMAs.
 */
class Detailed_rmas extends Report
{
    public function getDataColumns(): array
    {
        return [
            'summary' => [
                ['id' => lang('Rmas.rma_id')],
                ['rma_time'      => lang('Reports.date'), 'sortable' => false],
                ['rma_type'      => lang('Rmas.rma_type'), 'sortable' => false],
                ['location_name' => lang('Rmas.location')],
                ['entity_name'   => lang('Rmas.supplier_customer'), 'sortable' => false],
                ['sale_id'       => lang('Rmas.sale'), 'sortable' => false],
                ['quantity'      => lang('Reports.quantity')],
                ['resolution'    => lang('Rmas.resolution'), 'sortable' => false],
                ['employee_name' => lang('Reports.employee')],
                ['comment'       => lang('Reports.comments')],
            ],
            'details' => [
                lang('Reports.item_number'),
                lang('Reports.name'),
                lang('Reports.category'),
                lang('Reports.quantity'),
                lang('Rmas.serial_number'),
                lang('Rmas.item_issue'),
            ],
        ];
    }

    public function getData(array $inputs): array
    {
        $builder = $this->db->table('rmas AS rmas');
        $builder->select('
            rmas.rma_id,
            MAX(rmas.rma_time) AS rma_time,
            MAX(rmas.rma_type) AS rma_type,
            MAX(rmas.resolution) AS resolution,
            MAX(rmas.comment) AS comment,
            MAX(rmas.sale_id) AS sale_id,
            SUM(rma_items.quantity) AS quantity,
            MAX(location.location_name) AS location_name,
            MAX(CONCAT(employee.first_name, " ", employee.last_name)) AS employee_name,
            MAX(CONCAT(supplier_people.first_name, " ", supplier_people.last_name)) AS supplier_name,
            MAX(CONCAT(customer_people.first_name, " ", customer_people.last_name)) AS customer_name');
        $builder->join('rma_items AS rma_items', 'rma_items.rma_id = rmas.rma_id');
        $builder->join('stock_locations AS location', 'location.location_id = rmas.location_id', 'LEFT');
        $builder->join('people AS employee', 'employee.person_id = rmas.employee_id', 'LEFT');
        $builder->join('suppliers AS supplier', 'supplier.person_id = rmas.supplier_id', 'LEFT');
        $builder->join('people AS supplier_people', 'supplier_people.person_id = supplier.person_id', 'LEFT');
        $builder->join('customers AS customer', 'customer.person_id = rmas.customer_id', 'LEFT');
        $builder->join('people AS customer_people', 'customer_people.person_id = customer.person_id', 'LEFT');

        $builder->where('rmas.rma_time >=', $inputs['start_date'] . ' 00:00:00');
        $builder->where('rmas.rma_time <=', $inputs['end_date'] . ' 23:59:59');

        if (! empty($inputs['rma_type']) && in_array($inputs['rma_type'], ['stock', 'client'], true)) {
            $type = $inputs['rma_type'] === 'stock' ? Rma::TYPE_STOCK : Rma::TYPE_CLIENT;
            $builder->where('rmas.rma_type', $type);
        }

        if ($inputs['location_id'] !== 'all') {
            $builder->where('rmas.location_id', $inputs['location_id']);
        }

        $builder->groupBy('rmas.rma_id');
        $builder->orderBy('MAX(rmas.rma_id)');

        $data['summary'] = $builder->get()->getResultArray();
        $data['details'] = [];

        foreach ($data['summary'] as $key => $row) {
            $detail_builder = $this->db->table('rma_items AS rma_items');
            $detail_builder->select('
                MAX(items.item_number) AS item_number,
                MAX(items.name) AS name,
                MAX(items.category) AS category,
                MAX(rma_items.quantity) AS quantity,
                MAX(rma_items.serial_number) AS serial_number,
                MAX(rma_items.issue) AS issue');
            $detail_builder->join('items AS items', 'items.item_id = rma_items.item_id');
            $detail_builder->where('rma_items.rma_id', $row['rma_id']);
            $detail_builder->groupBy('rma_items.item_id');

            $data['details'][$key] = $detail_builder->get()->getResultArray();
        }

        return $data;
    }

    public function getSummaryData(array $inputs): array
    {
        $builder = $this->db->table('rmas AS rmas');
        $builder->join('rma_items AS rma_items', 'rma_items.rma_id = rmas.rma_id');
        $builder->select('COALESCE(SUM(rma_items.quantity), 0) AS total_quantity');

        $builder->where('rmas.rma_time >=', $inputs['start_date'] . ' 00:00:00');
        $builder->where('rmas.rma_time <=', $inputs['end_date'] . ' 23:59:59');

        if ($inputs['location_id'] !== 'all') {
            $builder->where('rmas.location_id', $inputs['location_id']);
        }

        return $builder->get()->getRowArray();
    }
}
