
<?php

require_once 'Secure_Controller.php';
/**
 *  Cash Receiving Controller Inherited from Secure_Controller
 */
class Cash_receivings extends Secure_Controller
{

    public function __construct()
    {
        parent::__construct('cash_receiving');
    }

    public function index()
    {
        $data['table_headers'] = $this->xss_clean(get_cash_receivings_manage_table_headers());

        // filters that will be loaded in the multiselect dropdown
        $data['filters'] = array('is_deleted' => $this->lang->line('cash_receiving_is_deleted'));

        $this->load->view('cash_receivings/manage', $data);
    }

    public function search()
    {
        $cash_up = 0;
        $search = $this->input->get('search');
        $limit = $this->input->get('limit');
        $offset = $this->input->get('offset');
        $sort = $this->input->get('sort');
        $order = $this->input->get('order');
        $filters = array(
            'start_date' => $this->input->get('start_date'),
            'end_date' => $this->input->get('end_date'),
            'is_deleted' => false);

        // check if any filter is set in the multiselect dropdown
        $filledup = array_fill_keys($this->input->get('filters'), true);
        $filters = array_merge($filters, $filledup);
        $cash_ups = $this->Cash_receiving->search($search, $filters, $limit, $offset, $sort, $order);
        $total_rows = $this->Cash_receiving->get_found_rows($search, $filters);
        $data_rows = array();
        foreach ($cash_ups->result() as $cash_up) {
            $data_rows[] = $this->xss_clean(get_cash_up_data_row($cash_up));
        }

        echo json_encode(array('total' => $total_rows, 'rows' => $data_rows));
    }

    public function view($cashup_id = -1)
    {
        $data = array();

        $data['employees'] = array();
        foreach ($this->Employee->get_all()->result() as $employee) {
            foreach (get_object_vars($employee) as $property => $value) {
                $employee->$property = $this->xss_clean($value);
            }

            $data['employees'][$employee->person_id] = $employee->first_name . ' ' . $employee->last_name;
        }

        $cash_receiving_info = $this->Cash_receiving->get_info($cashup_id);

        foreach (get_object_vars($cash_receiving_info) as $property => $value) {
            $cash_receiving_info->$property = $this->xss_clean($value);
        }

        // open cashup
        if (empty($cash_receiving_info->cashup_id)) {
            $cash_receiving_info->open_date = date('Y-m-d H:i:s');
            $cash_receiving_info->close_date = $cash_receiving_info->open_date;
            $cash_receiving_info->open_employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
            $cash_receiving_info->close_employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
        }
        // if all the amounts are null or 0 that means it's a close cashup
        elseif (floatval($cash_receiving_info->closed_amount_cash) == 0 &&
            floatval($cash_receiving_info->closed_amount_due) == 0 &&
            floatval($cash_receiving_info->closed_amount_card) == 0 &&
            floatval($cash_receiving_info->closed_amount_check) == 0) {
            // set the close date and time to the actual as this is a close session
            $cash_receiving_info->close_date = date('Y-m-d H:i:s');

            // the closed amount starts with the open amount -/+ any trasferred amount
            $cash_receiving_info->closed_amount_cash = $cash_receiving_info->open_amount_cash + $cash_receiving_info->transfer_amount_cash;

            // if it's date mode only and not date & time truncate the open and end date to date only
            if (empty($this->config->item('date_or_time_format'))) {
                // search for all the payments given the time range
                $inputs = array('start_date' => substr($cash_receiving_info->open_date, 0, 10), 'end_date' => substr($cash_receiving_info->close_date, 0, 10), 'sale_type' => 'complete', 'location_id' => 'all');
            } else {
                // search for all the payments given the time range
                $inputs = array('start_date' => $cash_receiving_info->open_date, 'end_date' => $cash_receiving_info->close_date, 'sale_type' => 'complete', 'location_id' => 'all');
            }

            // get all the transactions payment summaries
            $this->load->model('reports/Summary_payments');
            $reports_data = $this->Summary_payments->getData($inputs);

            foreach ($reports_data as $row) {
                if ($row['trans_group'] == $this->lang->line('reports_trans_payments')) {
                    if ($row['trans_type'] == $this->lang->line('sales_cash')) {
                        $cash_receiving_info->closed_amount_cash += $this->xss_clean($row['trans_amount']);
                    } elseif ($row['trans_type'] == $this->lang->line('sales_due')) {
                        $cash_receiving_info->closed_amount_due += $this->xss_clean($row['trans_amount']);
                    } elseif ($row['trans_type'] == $this->lang->line('sales_debit') ||
                        $row['trans_type'] == $this->lang->line('sales_credit')) {
                        $cash_receiving_info->closed_amount_card += $this->xss_clean($row['trans_amount']);
                    } elseif ($row['trans_type'] == $this->lang->line('sales_check')) {
                        $cash_receiving_info->closed_amount_check += $this->xss_clean($row['trans_amount']);
                    }
                }
            }

            // lookup expenses paid in cash
            $filters = array(
                'only_cash' => true,
                'only_due' => false,
                'only_check' => false,
                'only_credit' => false,
                'only_debit' => false,
                'is_deleted' => false);
            $payments = $this->Expense->get_payments_summary('', array_merge($inputs, $filters));

            foreach ($payments as $row) {
                $cash_receiving_info->closed_amount_cash -= $this->xss_clean($row['amount']);
            }

            $get_cash_receivings = $this->Cash_receiving->get_sale_receivings(array());
            foreach ($get_cash_receivings as $row) {
                $cash_receiving_info->closed_amount_cash -= $this->xss_clean($row['amount_tendered']);
            }

            // $cash_receiving_info->closed_amount_total = $this->_calculate_total($cash_receiving_info->open_amount_cash, $cash_receiving_info->transfer_amount_cash, $cash_receiving_info->closed_amount_cash, $cash_receiving_info->closed_amount_due, $cash_receiving_info->closed_amount_card, $cash_receiving_info->closed_amount_check);
        }

        $data['amounts_data'] = self::get_amounts_infos();
        $data['cash_ups_info'] = $cash_receiving_info;
        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";
        // exit;

        $this->load->view("cash_receivings/form", $data);
    }

    private function get_amounts_infos()
    {
        // GET Total tendered
        $get_total_amount = $this->Cash_receiving->get_sale_receivings(array(), true);
        $tendered_amount = 0;
        foreach ($get_total_amount as $key => $row) {
            $tendered_amount += $row->amount_tendered;
        }

        $get_total_amount = $this->Cash_receiving->get_sale_receivings(array());
        $tendered_amount_new = 0;
        foreach ($get_total_amount as $key => $row) {
            $tendered_amount_new += $row->amount_tendered;
        }

        // GET Cash
        $get_total_amount = $this->Cash_receiving->get_sale_receivings(array('only_cash' => true));
        $tendered_amount_cash = 0;
        foreach ($get_total_amount as $key => $row) {
            $tendered_amount_cash += $row->amount_tendered;
        }

        // GET Dues
        $get_total_amount = $this->Cash_receiving->get_sale_receivings(array('only_due' => true));
        $tendered_amount_dues = 0;
        foreach ($get_total_amount as $key => $row) {
            $tendered_amount_dues += $row->amount_tendered;
        }

        // GET Cards
        $get_total_amount = $this->Cash_receiving->get_sale_receivings(array('only_cards' => true));
        $tendered_amount_cards = 0;
        foreach ($get_total_amount as $key => $row) {
            $tendered_amount_cards += $row->amount_tendered;
        }

        // GET Checks
        $get_total_amount = $this->Cash_receiving->get_sale_receivings(array('only_checks' => true));
        $tendered_amount_checks = 0;
        foreach ($get_total_amount as $key => $row) {
            $tendered_amount_checks += $row->amount_tendered;
        }

        // GET Gift Cards
        // $get_total_amount = $this->Cash_receiving->get_sale_receivings(array('only_gitcard' => true));
        // $get_total_amount = $this->Cash_receiving->get_sale_receivings(array('only_gitcard' => true));
        $tendered_amount_giftcards = $this->Cash_receiving->get_gifts_cards();
        $old_amount_giftcards = $this->Cash_receiving->get_gifts_cards(true);
        // foreach ($get_total_amount as $key => $row) {
        //     $tendered_amount_giftcards += $row->amount_tendered;
        // }

        $total_expances = $this->Cash_receiving->get_expence_payments(array())->amount;
        $total_cash_received = $this->Cash_receiving->get_cash_receiving_sum_rows()->cash_receiving;
        $last_cash_received = $this->Cash_receiving->get_cash_receiving_sum_rows(true)->cash_receiving;
        $get_remaining_cash = $this->Cash_receiving->get_cash_last_remaining_rows();
        $remaining_cash = 0;
        foreach ($get_remaining_cash as $key => $row) {
            $remaining_cash += $row->last_remaining;
        }

        return array(
            'total_tendered' => $tendered_amount,
            'total_tendered_new' => $tendered_amount_new,
            'total_cash' => ($tendered_amount > 0) ? $tendered_amount_cash : 0,
            'total_dues' => ($tendered_amount > 0) ? $tendered_amount_dues : 0,
            'total_checks' => ($tendered_amount > 0) ? $tendered_amount_checks : 0,
            'total_cards' => ($tendered_amount > 0) ? $tendered_amount_cards : 0,
            'total_giftcards' => ($tendered_amount > 0) ? $tendered_amount_giftcards : 0,
            'old_amount_giftcards' => ($tendered_amount > 0) ? $old_amount_giftcards : 0,
            'expences_check' => ($tendered_amount > 0 && $total_expances > 0) ? $total_expances : 0,
            'cash_receivings' => (isset($tendered_amount_cash) && $tendered_amount > 0) ? $tendered_amount_new : 0,
            // 'cash_receivings' => 0,
            'last_cash_received' => $last_cash_received,
            'total_cash_received' => $total_cash_received,
            'remaining_cash' => $remaining_cash,
            'in_out_cash' => ($tendered_amount > 0) ? ($last_cash_received + $tendered_amount) : 0,
            'remaining_balance' => $this->_calculate_cash_recevings($tendered_amount, ($tendered_amount > 0) ? $total_cash_received : 0, $tendered_amount_new, $tendered_amount_giftcards, $total_expances, $old_amount_giftcards),
        );
    }

    public function get_row($row_id)
    {
        $cash_receiving_info = $this->Cash_receiving->get_info($row_id);
        $data_row = $this->xss_clean(get_cash_up_data_row($cash_receiving_info));

        echo json_encode($data_row);
    }

    public function save($cashup_id = -1)
    {
        $open_date = $this->input->post('open_date');
        $open_date_formatter = date_create_from_format($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), $open_date);

        $close_date = $this->input->post('close_date');
        $close_date_formatter = date_create_from_format($this->config->item('dateformat') . ' ' . $this->config->item('timeformat'), $close_date);

        $getamounts = $this->get_amounts_infos();
        $tendered_amount_cards = $getamounts['total_cards'];
        $tendered_amount_dues = $getamounts['total_dues'];
        $total_expances = $getamounts['expences_check'];
        $tendered_amount_checks = $getamounts['total_checks'];
        $total_cash_received = $getamounts['total_cash_received'];
        $total_cash = $getamounts['total_cash'];
        // Calculate Amount in cash Receiving
        // $cash_receivings_new = ($total_cash_received + parse_decimals($total_cash) - parse_decimals($this->input->post('closed_amount_total')));
        // $cash_receivings_new = (
        //     ( parse_decimals($total_cash) - parse_decimals($this->input->post('closed_amount_total')) ) - parse_decimals($total_expances)
        // );

        $cash_receivings_new =  parse_decimals($this->input->post('cash_receivings'));

        // $gitcard = $getamounts['total_giftcards'];
        // $total_expances = $getamounts['total_checks'];

        $cash_up_data = array(
            'open_date' => $open_date_formatter->format('Y-m-d H:i:s'),
            'close_date' => $close_date_formatter->format('Y-m-d H:i:s'),
            'open_amount_cash' => parse_decimals($this->input->post('open_amount_cash')),
            'transfer_amount_cash' => parse_decimals($this->input->post('transfer_amount_cash')),
            'closed_amount_cash' => parse_decimals($this->input->post('closed_amount_cash')),
            'closed_amount_due' => parse_decimals($tendered_amount_dues),
            'closed_amount_card' => parse_decimals($tendered_amount_cards),
            'closed_amount_check' => parse_decimals($tendered_amount_checks),
            'closed_amount_total' => parse_decimals($this->input->post('closed_amount_total')),
            'note' => $this->input->post('note') != null,
            'description' => $this->input->post('description'),
            'open_employee_id' => $this->input->post('open_employee_id'),
            'close_employee_id' => $this->input->post('close_employee_id'),
            'deleted' => $this->input->post('deleted') != null,
        );

        if ($this->Cash_receiving->save($cash_up_data, $cashup_id, $cash_receivings_new)) {
            $cash_up_data = $this->xss_clean($cash_up_data);

            //New cashup_id
            if ($cashup_id == -1) {
                echo json_encode(array('success' => true, 'message' => $this->lang->line('cash_receiving_successful_adding'), 'id' => $cash_up_data['cashup_id']));
            } else // Existing Cashup
            {
                echo json_encode(array('success' => true, 'message' => $this->lang->line('cash_receiving_successful_updating'), 'id' => $cashup_id));
            }
        } else //failure
        {
            echo json_encode(array('success' => false, 'message' => $this->lang->line('cash_receiving_error_adding_updating'), 'id' => -1));
        }
    }

    public function delete()
    {
        $cash_ups_to_delete = $this->input->post('ids');

        if ($this->Cash_receiving->delete_list($cash_ups_to_delete)) {
            echo json_encode(array('success' => true, 'message' => $this->lang->line('cash_receiving_successful_deleted') . ' ' . count($cash_ups_to_delete) . ' ' . $this->lang->line('cash_receiving_one_or_multiple'), 'ids' => $cash_ups_to_delete));
        } else {
            echo json_encode(array('success' => false, 'message' => $this->lang->line('cash_receiving_cannot_be_deleted'), 'ids' => $cash_ups_to_delete));
        }
    }

    /*
    AJAX call from cashup input form to calculate the total
     */
    public function ajax_cashup_total()
    {
        $total_tendered = parse_decimals($this->input->post('open_amount_cash'));
        $transfer_amount_cash = parse_decimals($this->input->post('transfer_amount_cash'));
        $closed_amount_cash = parse_decimals($this->input->post('closed_amount_cash'));
        $closed_amount_due = parse_decimals($this->input->post('closed_amount_due'));
        $closed_amount_card = parse_decimals($this->input->post('closed_amount_card'));
        $closed_amount_check = parse_decimals($this->input->post('closed_amount_check'));
        $cash_receivings = parse_decimals($this->input->post('cash_receivings'));
        $expences_check = parse_decimals($this->input->post('expences_check'));

        $getamounts = $this->get_amounts_infos();
        $new_giftcard = $getamounts['total_giftcards'];
        $closed_amount_card = $getamounts['total_cards'];
        $closed_amount_due = $getamounts['total_dues'];
        $tendered_amount_checks = $getamounts['total_checks'];
        $expences_check = $getamounts['expences_check'];
        $remaining_cash = $getamounts['remaining_cash'];

        $total_cash_received = $getamounts['total_cash_received'];
        $total_tendered_new = $getamounts['total_tendered_new'];
        $total_tendered = $getamounts['total_tendered'];
        $old_amount_giftcards = $getamounts['old_amount_giftcards'];

        $total = $this->_calculate_cash_recevings($total_tendered,  $total_cash_received, $cash_receivings, $new_giftcard, $expences_check, $old_amount_giftcards);

        echo json_encode(array('total' => to_currency_no_money($total)));
    }

    /*
    Calculate total
     */
    // private function _calculate_total($open_amount_cash, $transfer_amount_cash, $closed_amount_due, $closed_amount_cash, $closed_amount_card, $closed_amount_check)
    // {
    //     return ($closed_amount_cash - $open_amount_cash - $transfer_amount_cash + $closed_amount_due + $closed_amount_card + $closed_amount_check);
    // }

    private function _calculate_cash_recevings($tendered_amount, $total_cash_received, $receivedd, $new_giftcard, $total_expances, $old_gifts, $tendered_amount_cards = 0, $tendered_amount_dues = 0, $tendered_amount_checks = 0)
    {
        // return ($tendered_amount - $total_cash_received - $receivedd  - $giftcard);
        return ((((($tendered_amount + $old_gifts) - $total_cash_received) - $receivedd) - ($total_expances - $tendered_amount_cards - $tendered_amount_dues - $tendered_amount_checks)) - $new_giftcard );
    }
}
