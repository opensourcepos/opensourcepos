<?php

namespace App\Models\Reports;

/**
 * Detailed Transfers Report
 *
 * Shows each stock transfer with the items it contains.
 */
class Detailed_transfers extends Report
{
    /**
     * @return array
     */
    public function getDataColumns(): array
    {
        return [
            'summary' => [
                ['id'                => lang('Transfers.id')],
                ['transfer_time'     => lang('Reports.date'), 'sortable' => false],
                ['quantity'          => lang('Reports.quantity')],
                ['employee_name'     => lang('Reports.employee')],
                ['location_from_name'=> lang('Transfers.stock_source')],
                ['location_to_name'  => lang('Transfers.stock_destination')],
                ['comment'           => lang('Reports.comments')]
            ],
            'details' => [
                lang('Reports.item_number'),
                lang('Reports.name'),
                lang('Reports.category'),
                lang('Reports.quantity')
            ]
        ];
    }

    /**
     * @param array $inputs
     * @return array
     */
    public function getData(array $inputs): array
    {
        $builder = $this->db->table('transfers AS transfers');
        $builder->select('
            transfers.transfer_id,
            MAX(transfers.transfer_time) AS transfer_time,
            SUM(transfers_items.quantity) AS quantity,
            MAX(CONCAT(employee.first_name, " ", employee.last_name)) AS employee_name,
            MAX(location_from.location_name) AS location_from_name,
            MAX(location_to.location_name) AS location_to_name,
            MAX(transfers.comment) AS comment');
        $builder->join('transfers_items AS transfers_items', 'transfers_items.transfer_id = transfers.transfer_id');
        $builder->join('people AS employee', 'employee.person_id = transfers.employee_id');
        $builder->join('stock_locations AS location_from', 'location_from.location_id = transfers.location_from');
        $builder->join('stock_locations AS location_to', 'location_to.location_id = transfers.location_to');

        $builder->where('transfers.transfer_time >=', $inputs['start_date'] . ' 00:00:00');
        $builder->where('transfers.transfer_time <=', $inputs['end_date'] . ' 23:59:59');

        if ($inputs['location_id'] != 'all') {
            $builder->where('transfers.location_from', $inputs['location_id']);
        }

        $builder->groupBy('transfers.transfer_id');
        $builder->orderBy('MAX(transfers.transfer_id)');

        $data['summary'] = $builder->get()->getResultArray();
        $data['details'] = [];

        foreach ($data['summary'] as $key => $row) {
            $detail_builder = $this->db->table('transfers_items AS transfers_items');
            $detail_builder->select('
                MAX(items.item_number) AS item_number,
                MAX(items.name) AS name,
                MAX(items.category) AS category,
                MAX(transfers_items.quantity) AS quantity');
            $detail_builder->join('items AS items', 'items.item_id = transfers_items.item_id');
            $detail_builder->where('transfers_items.transfer_id', $row['transfer_id']);
            $detail_builder->groupBy('transfers_items.item_id');

            $data['details'][$key] = $detail_builder->get()->getResultArray();
        }

        return $data;
    }

    /**
     * @param array $inputs
     * @return array
     */
    public function getSummaryData(array $inputs): array
    {
        $builder = $this->db->table('transfers AS transfers');
        $builder->join('transfers_items AS transfers_items', 'transfers_items.transfer_id = transfers.transfer_id');
        $builder->select('COALESCE(SUM(transfers_items.quantity), 0) AS total_quantity');

        $builder->where('transfers.transfer_time >=', $inputs['start_date'] . ' 00:00:00');
        $builder->where('transfers.transfer_time <=', $inputs['end_date'] . ' 23:59:59');

        if ($inputs['location_id'] != 'all') {
            $builder->where('transfers.location_from', $inputs['location_id']);
        }

        return $builder->get()->getRowArray();
    }
}
