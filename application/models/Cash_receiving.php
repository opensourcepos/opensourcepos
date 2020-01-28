<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Item_kit_items class
 */

class Cash_receiving extends CI_Model
{
    /*
    Determines if a given Cashup_id is an Cashup
     */
    public function exists($cashup_id)
    {
        $this->db->from('cash_up');
        $this->db->where('cashup_id', $cashup_id);

        return ($this->db->get()->num_rows() == 1);
    }

    public function exists_rec($cashup_id)
    {
        $this->db->from('cash_receivings');
        $this->db->where('cashup_id', $cashup_id);

        return ($this->db->get()->num_rows() == 1);
    }

    /*
    Gets employee info
     */
    public function get_employee($cashup_id)
    {
        $this->db->from('cash_up');
        $this->db->where('cashup_id', $cashup_id);

        return $this->Employee->get_info($this->db->get()->row()->employee_id);
    }

    public function get_multiple_info($cashup_ids)
    {
        $this->db->from('cash_up');
        $this->db->where_in('cashup_id', $cashup_ids);
        $this->db->order_by('cashup_id', 'asc');

        return $this->db->get();
    }

    /*
    Gets rows
     */
    public function get_found_rows($search, $filters)
    {
        return $this->search($search, $filters, 0, 0, 'cashup_id', 'asc', true);
    }

    /*
    Searches cashups
     */
    public function search($search, $filters, $rows = 0, $limit_from = 0, $sort = 'cashup_id', $order = 'asc', $count_only = false)
    {
        // get_found_rows case
        if ($count_only == true) {
            $this->db->select('COUNT(cash_up.cashup_id) as count');
        }

        $this->db->select('

            cash_rec.id AS cash_receiving_id,
            cash_rec.cash_balance  AS total_cash_balance,
            cash_rec.cash_receiving AS cash_receiving,
            cash_up.cashup_id,
            cash_rec.cashup_id AS rec_cashup_id,
            MAX(cash_up.open_date) AS open_date,
            MAX(cash_up.close_date) AS close_date,
            MAX(cash_up.open_amount_cash) AS open_amount_cash,
            MAX(cash_up.transfer_amount_cash) AS transfer_amount_cash,
            MAX(cash_up.closed_amount_cash) AS closed_amount_cash,
            MAX(cash_up.closed_amount_due) AS closed_amount_due,
            MAX(cash_up.closed_amount_card) AS closed_amount_card,
            MAX(cash_up.closed_amount_check) AS closed_amount_check,
            MAX(cash_up.closed_amount_total) AS closed_amount_total,
            MAX(cash_up.description) AS description,
            MAX(cash_up.note) AS note,
            MAX(cash_up.open_employee_id) AS open_employee_id,
            MAX(cash_up.close_employee_id) AS close_employee_id,
            MAX(open_employees.first_name) AS open_first_name,
            MAX(open_employees.last_name) AS open_last_name,
            MAX(close_employees.first_name) AS close_first_name,
            MAX(close_employees.last_name) AS close_last_name
        ');
        $this->db->from('cash_up AS cash_up');
        $this->db->join('cash_receivings AS cash_rec', 'cash_rec.cashup_id = cash_up.cashup_id', 'LEFT');
        $this->db->join('people AS open_employees', 'open_employees.person_id = cash_up.open_employee_id', 'LEFT');
        $this->db->join('people AS close_employees', 'close_employees.person_id = cash_up.close_employee_id', 'LEFT');

        $this->db->group_start();
        $this->db->like('cash_up.open_date', $search);
        $this->db->or_like('open_employees.first_name', $search);
        $this->db->or_like('open_employees.last_name', $search);
        $this->db->or_like('close_employees.first_name', $search);
        $this->db->or_like('close_employees.last_name', $search);
        $this->db->or_like('cash_up.closed_amount_total', $search);
        $this->db->or_like('CONCAT(open_employees.first_name, " ", open_employees.last_name)', $search);
        $this->db->or_like('CONCAT(close_employees.first_name, " ", close_employees.last_name)', $search);
        $this->db->group_end();

        $this->db->where('cash_up.deleted', $filters['is_deleted']);

        if (empty($this->config->item('date_or_time_format'))) {
            $this->db->where('DATE_FORMAT(cash_up.open_date, "%Y-%m-%d") BETWEEN ' . $this->db->escape($filters['start_date']) . ' AND ' . $this->db->escape($filters['end_date']));
        } else {
            $this->db->where('cash_up.open_date BETWEEN ' . $this->db->escape(rawurldecode($filters['start_date'])) . ' AND ' . $this->db->escape(rawurldecode($filters['end_date'])));
        }

        $this->db->group_by('cash_up.cashup_id');

        // get_found_rows case
        if ($count_only == true) {
            return $this->db->get()->row_array()['count'];
        }

        $this->db->order_by($sort, $order);

        if ($rows > 0) {
            $this->db->limit($rows, $limit_from);
        }

        return $this->db->get();
    }

    public function get_cash_receiving_sum_rows($last_receings = false)
    {
        $this->db->select_sum('cash_receiving');
        $this->db->from('cash_receivings');
        if ($last_receings != false) {
            $this->db->order_by('id', 'desc');
            $this->db->limit(1);
        }
        return $this->db->get()->row();
    }
    
    public function get_cash_last_remaining_rows()
    {
        $this->db->select('(cash_balance - cash_receiving) as last_remaining');
        $this->db->from('cash_receivings');
        $this->db->order_by('id', 'desc');
        return $this->db->get()->result();
    }

    public function get_gifts_cards($old = false)
    {
        $this->db->select('SUM(value) as total_gifts_amount');
        $this->db->from('giftcards');
        if($old == false){
            $this->db->where('cashed_status', 0);
        }else{
            $this->db->where('cashed_status', 1);
        }
        $this->db->order_by('giftcard_id', 'desc');
        $get = $this->db->get()->row();
        return ($get->total_gifts_amount > 0) ? $get->total_gifts_amount : 0;
    }

    /*
    Gets information about a particular cashup
     */
    public function get_info($cashup_id)
    {
        $this->db->select('

            cash_rec.id AS cash_receiving_id,
            cash_rec.cash_balance  AS total_cash_balance,
            cash_rec.cash_receiving AS cash_receiving,
            cash_up.cashup_id AS cashup_id,
            cash_rec.cashup_id AS rec_cashup_id,
            cash_up.open_date AS open_date,
            cash_up.close_date AS close_date,
            cash_up.open_amount_cash AS open_amount_cash,
            cash_up.transfer_amount_cash AS transfer_amount_cash,
            cash_up.closed_amount_cash AS closed_amount_cash,
            cash_up.closed_amount_due AS closed_amount_due,
            cash_up.closed_amount_card AS closed_amount_card,
            cash_up.closed_amount_check AS closed_amount_check,
            cash_up.closed_amount_total AS closed_amount_total,
            cash_up.description AS description,
            cash_up.note AS note,
            cash_up.open_employee_id AS open_employee_id,
            cash_up.close_employee_id AS close_employee_id,
            cash_up.deleted AS deleted,
            open_employees.first_name AS open_first_name,
            open_employees.last_name AS open_last_name,
            close_employees.first_name AS close_first_name,
            close_employees.last_name AS close_last_name
        ');
        $this->db->from('cash_up AS cash_up');
        $this->db->join('cash_receivings AS cash_rec', 'cash_rec.cashup_id = cash_up.cashup_id', 'LEFT');
        $this->db->join('people AS open_employees', 'open_employees.person_id = cash_up.open_employee_id', 'LEFT');
        $this->db->join('people AS close_employees', 'close_employees.person_id = cash_up.close_employee_id', 'LEFT');
        $this->db->where('cash_up.cashup_id', $cashup_id);

        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            return $query->row();
        } else {
            //Get empty base parent object
            $cash_up_obj = new stdClass();

            //Get all the fields from cashup table
            foreach ($this->db->list_fields('cash_up') as $field) {
                $cash_up_obj->$field = '';
            }

            return $cash_up_obj;
        }
    }

    /*
    Inserts or updates an cashup
     */
    public function save(&$cash_up_data, $cashup_id = false, $cash_receivings_new = null)
    {

        if (!$cashup_id == -1 || !$this->exists($cashup_id)) {
            if ($this->db->insert('cash_up', $cash_up_data)) {
                $cash_up_data['cashup_id'] = $this->db->insert_id();

                $receiving_data = array(
                    'cashup_id' => $cash_up_data['cashup_id'],
                    'cash_balance' => $cash_up_data['open_amount_cash'],
                    'cash_receiving' => $cash_receivings_new,
                );
                self::save_receiving_cash($receiving_data, $cash_up_data['cashup_id']);

                return true;
            }

            return false;
        }

        $receiving_data = array(
            'cashup_id' => $cashup_id,
            'cash_balance' => $cash_up_data['open_amount_cash'],
            'cash_receiving' => $cash_receivings_new,
        );
        self::save_receiving_cash($receiving_data, $cashup_id);

        $this->db->where('cashup_id', $cashup_id);

        return $this->db->update('cash_up', $cash_up_data);
    }

    /*
    Inserts or updates an cashup
     */
    public function save_receiving_cash(&$data, $cashup_id = false)
    {
        // Dont let again sales payment with status 1 and set to 1 now
        $this->db->where('cashed_status', 0);
        $this->db->update('sales_payments', array('cashed_status'=> 1));

        // Dont let again expanses with status 1 and set to 1 now
        $this->db->where('cash_exp_status', 0);
        $this->db->update('expenses', array('cash_exp_status'=> 1));
        
        // Dont let again giftcard with status 1 and set to 1 now
        $this->db->where('cashed_status', 0);
        $this->db->update('giftcards', array('cashed_status'=> 1));

        if (!$cashup_id == -1 || !$this->exists_rec($cashup_id)) {
            if ($this->db->insert('cash_receivings', $data)) {
                $cash_up_data['id'] = $this->db->insert_id();

                return true;
            }

            return false;
        }
        
        return $this->db->query("UPDATE ".$this->db->dbprefix('cash_receivings')." SET cash_balance = cash_balance + '" . $data['cash_balance'] . "', cash_receiving = cash_receiving + '" . $data['cash_receiving'] . "' WHERE cashup_id = $cashup_id ");

    }

    /*
    Deletes a list of cashups
     */
    public function delete_list($cashup_ids)
    {
        $success = false;

        //Run these queries as a transaction, we want to make sure we do all or nothing
        $this->db->trans_start();
        $this->db->where_in('cashup_id', $cashup_ids);
        $success = $this->db->update('cash_up', array('deleted' => 1));
        $this->db->trans_complete();

        return $success;
    }

    /*
    Gets the payment summary for the expenses (expenses/manage) view
     */
    public function get_expence_payments($filters)
    {
        // get payment summary
        $this->db->select_sum('amount');
        $this->db->from('expenses');
        $this->db->where('deleted', 0);
        $this->db->where('cash_exp_status', 0);

        if (isset($filters['only_debit']) && $filters['only_cash'] != false) {
            $this->db->like('payment_type', $this->lang->line('expenses_cash'));
        }

        if (isset($filters['only_debit']) && $filters['only_due'] != false) {
            $this->db->like('payment_type', $this->lang->line('expenses_due'));
        }

        if (isset($filters['only_debit']) && $filters['only_check'] != false) {
            $this->db->like('payment_type', $this->lang->line('expenses_check'));
        }

        if (isset($filters['only_debit']) && $filters['only_credit'] != false) {
            $this->db->like('payment_type', $this->lang->line('expenses_credit'));
        }

        if (isset($filters['only_debit']) && $filters['only_debit'] != false) {
            $this->db->like('payment_type', $this->lang->line('expenses_debit'));
        }

        // $this->db->group_by('payment_type');

        $payments = $this->db->get()->row();

        return $payments;
    }

    public function get_sale_receivings($filters, $all = false)
    {
        $this->db->select("SUM(payment_amount) AS amount_tendered");
        $this->db->from('sales_payments AS payments');

        if($all ==  false){
            $this->db->where('payments.cashed_status', 0);
        }

        if (isset($filters['only_gitcard']) && $filters['only_gitcard'] != false) {
            $this->db->like('payments.payment_type', $this->lang->line('sales_giftcard'), 'before');
        }
        if (isset($filters['only_cash']) && $filters['only_cash'] != false) {
            $this->db->where('payments.payment_type', $this->lang->line('sales_cash'));
        }
        if (isset($filters['only_due']) && $filters['only_due'] != false) {
            $this->db->where('payments.payment_type', $this->lang->line('sales_due'));
        }
        if (isset($filters['only_checks']) && $filters['only_checks'] != false) {
            $this->db->where('payments.payment_type', $this->lang->line('sales_check'));
        }
        if (isset($filters['only_cards']) && $filters['only_cards'] != false) {
            $this->db->group_start();
            $this->db->where('payments.payment_type', $this->lang->line('sales_debit'));
            $this->db->or_where('payments.payment_type', $this->lang->line('sales_credit'));
            $this->db->group_end();
        }

        // $this->db->group_by('sales.sale_id');
        $this->db->group_by('payments.payment_type');

        // order by sale time by default
        $this->db->order_by('sale_id', 'desc');

        return $this->db->get()->result();
    }
}
