<?php

namespace App\Controllers;

use App\TransferObjects\Cashups\CashupAmounts;
use App\Models\Cashup;
use App\Models\Expense;
use App\Models\Reports\Summary_payments;
use CodeIgniter\HTTP\ResponseInterface;
use Config\OSPOS;
use Config\Services;

class Cashups extends Secure_Controller
{
    private Cashup $cashup;
    private Expense $expense;
    private Summary_payments $summary_payments;
    private array $config;

    public function __construct()
    {
        parent::__construct('cashups');

        $this->cashup = model(Cashup::class);
        $this->expense = model(Expense::class);
        $this->summary_payments = model(Summary_payments::class);
        $this->config = config(OSPOS::class)->settings;
    }

    /**
     * @return string
     */
    public function getIndex(): string
    {
        $data['table_headers'] = get_cashups_manage_table_headers();

        // filters that will be loaded in the multiselect dropdown
        $data['filters'] = ['is_deleted' => lang('Cashups.is_deleted')];

        // Restore filters from URL
        $data = array_merge($data, restoreTableFilters($this->request));

        return view('cashups/manage', $data);
    }

    /**
     * @return void
     */
    public function getSearch(): ResponseInterface
    {
        $search = $this->request->getGet('search');
        $limit = $this->request->getGet('limit', FILTER_SANITIZE_NUMBER_INT);
        $offset = $this->request->getGet('offset', FILTER_SANITIZE_NUMBER_INT);
        $sort = $this->sanitizeSortColumn(cashup_headers(), $this->request->getGet('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS), 'cashup_id');
        $order = $this->request->getGet('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $filters = [
            'start_date' => $this->request->getGet('start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'end_date'   => $this->request->getGet('end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'is_deleted' => false
        ];

        // Check if any filter is set in the multiselect dropdown
        $requestFilters = array_fill_keys($this->request->getGet('filters', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? [], true);
        $filters = array_merge($filters, $requestFilters);
        $cashUps = $this->cashup->search($search, $filters, $limit, $offset, $sort, $order);
        $totalRows = $this->cashup->get_found_rows($search, $filters);
        $dataRows = [];
        foreach ($cashUps->getResult() as $cashUp) {
            $dataRows[] = get_cash_up_data_row($cashUp);
        }

        return $this->response->setJSON(['total' => $totalRows, 'rows' => $dataRows]);
    }

    /**
     * @param int $cashupId
     * @return string
     */
    public function getView(int $cashupId = NEW_ENTRY): string
    {
        $data = [];
        $data['employees'] = $this->buildEmployeeLookup();

        $cashUpsInfo = $this->normalizeCashupInfo($cashupId);

        // Open cashup
        if ($cashUpsInfo->cashup_id == NEW_ENTRY) {
            $this->initializeNewCashup($cashUpsInfo);
        }
        // If all the amounts are null or 0 that means it's a close cashup
        elseif ($this->isPendingClose($cashUpsInfo)) {
            $this->closeCashup($cashUpsInfo);
        }

        $data['cash_ups_info'] = $cashUpsInfo;

        return view("cashups/form", $data);
    }

    /**
     * @return array
     */
    private function buildEmployeeLookup(): array
    {
        $employees = [];
        foreach ($this->employee->get_all()->getResult() as $employee) {
            foreach (get_object_vars($employee) as $property => $value) {
                $employee->$property = $value;
            }

            $employees[$employee->person_id] = $employee->first_name . ' ' . $employee->last_name;
        }

        return $employees;
    }

    /**
     * @param int $cashupId
     * @return object
     */
    private function normalizeCashupInfo(int $cashupId): object
    {
        $cashUpsInfo = $this->cashup->get_info($cashupId);

        foreach (get_object_vars($cashUpsInfo) as $property => $value) {
            $cashUpsInfo->$property = $value;
        }

        return $cashUpsInfo;
    }

    /**
     * @param object $cashUpsInfo
     * @return void
     */
    private function initializeNewCashup(object $cashUpsInfo): void
    {
        $cashUpsInfo->open_date = date('Y-m-d H:i:s');
        $cashUpsInfo->close_date = $cashUpsInfo->open_date;
        $cashUpsInfo->open_employee_id = $this->employee->get_logged_in_employee_info()->person_id;
        $cashUpsInfo->close_employee_id = $this->employee->get_logged_in_employee_info()->person_id;
    }

    /**
     * @param object $cashUpsInfo
     * @return bool
     */
    private function isPendingClose(object $cashUpsInfo): bool
    {
        return floatval($cashUpsInfo->closed_amount_cash) == 0
            && floatval($cashUpsInfo->closed_amount_due) == 0
            && floatval($cashUpsInfo->closed_amount_card) == 0
            && floatval($cashUpsInfo->closed_amount_check) == 0;
    }

    /**
     * @param object $cashUpsInfo
     * @return void
     */
    private function closeCashup(object $cashUpsInfo): void
    {
        // Set the close date and time to the actual as this is a close session
        $cashUpsInfo->close_date = date('Y-m-d H:i:s');

        // The closed amount starts with the open amount -/+ any transferred amount
        $cashUpsInfo->closed_amount_cash = $cashUpsInfo->open_amount_cash + $cashUpsInfo->transfer_amount_cash;

        $inputs = $this->buildReportDateRangeInputs($cashUpsInfo);

        // Get all the transactions payment summaries
        $reportsData = $this->summary_payments->getData($inputs);
        $this->applyPaymentTotals($cashUpsInfo, $reportsData);

        $this->subtractCashExpenses($cashUpsInfo, $inputs);

        $cashUpsInfo->closed_amount_total = $this->calculateTotal(new CashupAmounts(
            $cashUpsInfo->open_amount_cash,
            $cashUpsInfo->transfer_amount_cash,
            $cashUpsInfo->closed_amount_due,
            $cashUpsInfo->closed_amount_cash,
            $cashUpsInfo->closed_amount_card,
            $cashUpsInfo->closed_amount_check
        ));
    }

    /**
     * @param object $cashUpsInfo
     * @return array
     */
    private function buildReportDateRangeInputs(object $cashUpsInfo): array
    {
        // If it's date mode only and not date & time, truncate the open and end date to date only
        if (empty($this->config['date_or_time_format'])) {
            if ($cashUpsInfo->open_date != null) {
                $startDate = substr($cashUpsInfo->open_date, 0, 10);
            } else {
                $startDate = null;
            }
            if ($cashUpsInfo->close_date != null) {
                $endDate = substr($cashUpsInfo->close_date, 0, 10);
            } else {
                $endDate = null;
            }
        } else {
            $startDate = $cashUpsInfo->open_date;
            $endDate = $cashUpsInfo->close_date;
        }

        // Search for all the payments given the time range
        return [
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'sale_type'   => 'complete',
            'location_id' => 'all'
        ];
    }

    /**
     * @param object $cashUpsInfo
     * @param array $reportsData
     * @return void
     */
    private function applyPaymentTotals(object $cashUpsInfo, array $reportsData): void
    {
        foreach ($reportsData as $row) {
            if ($row['trans_group'] == lang('Reports.trans_payments')) {
                if ($row['trans_type'] == lang('Sales.cash')) {
                    $cashUpsInfo->closed_amount_cash += $row['trans_amount'];
                } elseif ($row['trans_type'] == lang('Sales.due')) {
                    $cashUpsInfo->closed_amount_due += $row['trans_amount'];
                } elseif (
                    $row['trans_type'] == lang('Sales.debit') ||
                    $row['trans_type'] == lang('Sales.credit')
                ) {
                    $cashUpsInfo->closed_amount_card += $row['trans_amount'];
                } elseif ($row['trans_type'] == lang('Sales.check')) {
                    $cashUpsInfo->closed_amount_check += $row['trans_amount'];
                }
            }
        }
    }

    /**
     * @param object $cashUpsInfo
     * @param array $inputs
     * @return void
     */
    private function subtractCashExpenses(object $cashUpsInfo, array $inputs): void
    {
        // Lookup expenses paid in cash
        $filters = [
            'only_cash'   => true,
            'only_due'    => false,
            'only_check'  => false,
            'only_credit' => false,
            'only_debit'  => false,
            'is_deleted'  => false
        ];

        $payments = $this->expense->get_payments_summary('', array_merge($inputs, $filters));

        foreach ($payments as $row) {
            $cashUpsInfo->closed_amount_cash -= $row['amount'];
        }
    }

    /**
     * @param int $rowId
     * @return ResponseInterface
     */
    public function getRow(int $rowId): ResponseInterface
    {
        $cashUpsInfo = $this->cashup->get_info($rowId);
        $dataRow = get_cash_up_data_row($cashUpsInfo);

        return $this->response->setJSON($dataRow);
    }

    /**
     * @param int $cashupId
     * @return ResponseInterface
     */
    public function postSave(int $cashupId = NEW_ENTRY): ResponseInterface
    {
        $openDate = $this->parseCashupDate($this->request->getPost('open_date'));
        $closeDate = $this->parseCashupDate($this->request->getPost('close_date'));
        $amounts = $this->getCashupAmountsFromPost();

        $cashUpData = $this->buildCashupSaveData($amounts, $openDate, $closeDate);
        $wasSaved = $this->cashup->save_value($cashUpData, $cashupId);

        return $this->buildSaveResponse($wasSaved, $cashupId, $cashUpData);
    }

    /**
     * @param string $rawDate
     * @return \DateTime|false
     */
    private function parseCashupDate(string $rawDate): \DateTime|false
    {
        return date_create_from_format($this->config['dateformat'] . ' ' . $this->config['timeformat'], $rawDate);
    }

    /**
     * @param CashupAmounts $amounts
     * @param \DateTime $openDate
     * @param \DateTime $closeDate
     * @return array
     */
    private function buildCashupSaveData(CashupAmounts $amounts, \DateTime $openDate, \DateTime $closeDate): array
    {
        $loggedInEmployee = $this->employee->get_logged_in_employee_info();

        return [
            'open_date'            => $openDate->format('Y-m-d H:i:s'),
            'close_date'           => $closeDate->format('Y-m-d H:i:s'),
            'open_amount_cash'     => $amounts->openAmountCash,
            'transfer_amount_cash' => $amounts->transferAmountCash,
            'closed_amount_cash'   => $amounts->closedAmountCash,
            'closed_amount_due'    => $amounts->closedAmountDue,
            'closed_amount_card'   => $amounts->closedAmountCard,
            'closed_amount_check'  => $amounts->closedAmountCheck,
            'closed_amount_total'  => $this->calculateTotal($amounts),
            'note'                 => $this->request->getPost('note') != null,
            'description'          => $this->request->getPost('description', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'open_employee_id'     => $loggedInEmployee->person_id,
            'close_employee_id'    => $loggedInEmployee->person_id,
            'deleted'              => $this->request->getPost('deleted') != null
        ];
    }

    /**
     * @param bool $wasSaved
     * @param int $cashupId
     * @param array $cashUpData
     * @return ResponseInterface
     */
    private function buildSaveResponse(bool $wasSaved, int $cashupId, array $cashUpData): ResponseInterface
    {
        if (!$wasSaved) { // Failure
            return $this->response->setJSON(['success' => false, 'message' => lang('Cashups.error_adding_updating'), 'id' => NEW_ENTRY]);
        }

        // New cashup_id
        if ($cashupId == NEW_ENTRY) {
            return $this->response->setJSON(['success' => true, 'message' => lang('Cashups.successful_adding'), 'id' => $cashUpData['cashup_id']]);
        }

        // Existing Cashup
        return $this->response->setJSON(['success' => true, 'message' => lang('Cashups.successful_updating'), 'id' => $cashupId]);
    }

    /**
     * @return ResponseInterface
     */
    public function postDelete(): ResponseInterface
    {
        $cashUpsToDelete = $this->request->getPost('ids', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($this->cashup->delete_list($cashUpsToDelete)) {
            return $this->response->setJSON(['success' => true, 'message' => lang('Cashups.successful_deleted') . ' ' . count($cashUpsToDelete) . ' ' . lang('Cashups.one_or_multiple'), 'ids' => $cashUpsToDelete]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => lang('Cashups.cannot_be_deleted'), 'ids' => $cashUpsToDelete]);
        }
    }

    /**
     * Calculate the total for cashups. Used in app\Views\cashups\form.php
     *
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function postAjax_cashup_total(): ResponseInterface
    {
        $total = $this->calculateTotal($this->getCashupAmountsFromPost());

        return $this->response->setJSON(['total' => to_currency_no_money($total)]);
    }

    /**
     * Calculate total
     */
    private function calculateTotal(CashupAmounts $amounts): float
    {
        return ($amounts->closedAmountCash - $amounts->openAmountCash - $amounts->transferAmountCash + $amounts->closedAmountDue + $amounts->closedAmountCard + $amounts->closedAmountCheck);
    }

    /**
     * @return CashupAmounts
     */
    private function getCashupAmountsFromPost(): CashupAmounts
    {
        return new CashupAmounts(
            parse_decimals($this->request->getPost('open_amount_cash')),
            parse_decimals($this->request->getPost('transfer_amount_cash')),
            parse_decimals($this->request->getPost('closed_amount_due')),
            parse_decimals($this->request->getPost('closed_amount_cash')),
            parse_decimals($this->request->getPost('closed_amount_card')),
            parse_decimals($this->request->getPost('closed_amount_check'))
        );
    }
}
