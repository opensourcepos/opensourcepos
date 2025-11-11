<?php

namespace App\Controllers;

use App\Models\Consignment;
use App\Models\Sale;
use App\Models\Supplier;

class Consignments extends Secure_Controller
{
    private Consignment $consignment;
    private Sale $sale;
    private Supplier $supplier;

    public function __construct()
    {
        parent::__construct('consignments', null, 'office');

        $this->consignment = model(Consignment::class);
        $this->sale = model(Sale::class);
        $this->supplier = model(Supplier::class);
    }

    public function getIndex(): void
    {
        $data['table_headers'] = get_consignments_manage_table_headers();
        $data['filters'] = [
            'status_pending'  => lang('Consignments.pending'),
            'status_paid'     => lang('Consignments.paid'),
            'status_canceled' => lang('Consignments.canceled')
        ];
        $data['selected_filters'] = ['status_pending'];

        echo view('consignments/manage', $data);
    }

    public function getSearch(): void
    {
        $search = $this->request->getGet('search');
        $limit = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
        $offset = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
        $sort = $this->sanitizeSortColumn(consignment_headers(), $this->request->getGet('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS), 'consignment_transactions.sold_at');
        $order = $this->request->getGet('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $filters = [
            'start_date'       => $this->request->getGet('start_date'),
            'end_date'         => $this->request->getGet('end_date'),
            'status_pending'   => false,
            'status_paid'      => false,
            'status_canceled'  => false
        ];

        $request_filters = array_fill_keys($this->request->getGet('filters', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? [], true);
        $filters = array_merge($filters, $request_filters);

        $consignments = $this->consignment->search($search, $filters, $limit, $offset, $sort, $order);
        $total_rows = $this->consignment->get_found_rows($search, $filters);

        $rows = [];
        foreach ($consignments->getResult() as $row) {
            $rows[] = get_consignment_data_row($row);
        }

        echo json_encode(['total' => $total_rows, 'rows' => $rows]);
    }

    public function getRow(int $row_id): void
    {
        $data_row = get_consignment_data_row($this->consignment->get_info($row_id));
        echo json_encode($data_row);
    }

    public function getView(int $consignment_id = NEW_ENTRY): void
    {
        $info = $this->consignment->get_info($consignment_id);
        $data['consignment_info'] = $info;
        $data['status_options'] = [
            Consignment::STATUS_PENDING  => lang('Consignments.pending'),
            Consignment::STATUS_PAID     => lang('Consignments.paid'),
            Consignment::STATUS_CANCELED => lang('Consignments.canceled')
        ];

        echo view('consignments/form', $data);
    }

    public function postSave(int $consignment_id = NEW_ENTRY): void
    {
        $status = $this->request->getPost('status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $payout_date_input = $this->request->getPost('payout_date');
        $notes = $this->request->getPost('notes', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $valid_statuses = [Consignment::STATUS_PENDING, Consignment::STATUS_PAID, Consignment::STATUS_CANCELED];
        if (!in_array($status, $valid_statuses, true)) {
            $status = Consignment::STATUS_PENDING;
        }

        $payout_date = null;
        if (!empty($payout_date_input) && $status === Consignment::STATUS_PAID) {
            $timestamp = strtotime($payout_date_input);
            $payout_date = $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
        }

        if ($status !== Consignment::STATUS_PAID) {
            $payout_date = null;
        }

        $update_data = [
            'status'      => $status,
            'payout_date' => $payout_date,
            'notes'       => $notes
        ];

        if ($this->consignment->update($consignment_id, $update_data)) {
            echo json_encode([
                'success' => true,
                'message' => lang('Consignments.successful_updating'),
                'id'      => $consignment_id
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => lang('Consignments.error_updating'),
                'id'      => $consignment_id
            ]);
        }
    }

    public function postMarkPaid(): void
    {
        $ids = $this->request->getPost('ids');
        if (!is_array($ids)) {
            $ids = $this->request->getPost('ids', FILTER_SANITIZE_NUMBER_INT);
        }

        $ids = array_filter(array_map('intval', (array)$ids));

        if (!empty($ids) && $this->consignment->mark_paid($ids)) {
            echo json_encode([
                'success' => true,
                'message' => lang('Consignments.successful_mark_paid')
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => lang('Consignments.error_mark_paid')
            ]);
        }
    }
}
