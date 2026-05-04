<?php

namespace App\Controllers;

use App\Models\Account;
use App\Models\Journal;
use App\Models\Accounting_entry;
use CodeIgniter\HTTP\ResponseInterface;

class Accounting extends Secure_Controller
{
    private Account $account;
    private Journal $journal;
    private Accounting_entry $accounting_entry;

    public function __construct()
    {
        parent::__construct('accounting');
        
        $this->account = model(Account::class);
        $this->journal = model(Journal::class);
        $this->accounting_entry = model(Accounting_entry::class);
    }

    public function getIndex(): string
    {
        $data['balance_sheet'] = $this->accounting_entry->get_balance_sheet();
        $data['profit_loss'] = $this->accounting_entry->get_profit_loss();
        return view('accounting/dashboard', $data);
    }

    public function getAccounts(): string
    {
        $data['accounts'] = $this->account->get_all()->getResult();
        return view('accounting/manage_accounts', $data);
    }

    public function getEntries(): string
    {
        $db = \Config\Database::connect();
        $builder = $db->table('accounting_entries e');
        $builder->select('e.*, j.name as journal_name, CONCAT(p.first_name, " ", p.last_name) as employee_name');
        $builder->join('journals j', 'j.journal_id = e.journal_id');
        $builder->join('people p', 'p.person_id = e.employee_id');
        $builder->where('e.deleted', 0);
        $builder->orderBy('e.date', 'DESC');
        $builder->limit(100);
        
        $data['entries'] = $builder->get()->getResult();
        return view('accounting/manage_entries', $data);
    }

    public function getReports(): string
    {
        $data['balance_sheet'] = $this->accounting_entry->get_balance_sheet();
        $data['profit_loss'] = $this->accounting_entry->get_profit_loss();
        return view('accounting/reports', $data);
    }

    public function postSaveAccount(): ResponseInterface
    {
        $account_id = $this->request->getPost('account_id') ? $this->request->getPost('account_id') : false;
        
        $account_data = [
            'code' => $this->request->getPost('code', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'name' => $this->request->getPost('name', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'type' => $this->request->getPost('type', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
        ];

        if ($this->account->save_value($account_data, $account_id)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Account saved successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to save account']);
        }
    }
}
