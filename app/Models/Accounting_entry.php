<?php

namespace App\Models;

use CodeIgniter\Model;

class Accounting_entry extends Model
{
    protected $table = 'accounting_entries';
    protected $primaryKey = 'entry_id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'date',
        'journal_id',
        'ref',
        'description',
        'employee_id',
        'deleted'
    ];

    public function get_info($entry_id)
    {
        $builder = $this->db->table($this->table);
        $builder->where('entry_id', $entry_id);
        $query = $builder->get();

        if ($query->getNumRows() == 1) {
            return $query->getRow();
        } else {
            $obj = new \stdClass();
            $obj->entry_id = null;
            $obj->date = date('Y-m-d H:i:s');
            $obj->journal_id = null;
            $obj->ref = '';
            $obj->description = '';
            $obj->employee_id = null;
            $obj->deleted = 0;
            return $obj;
        }
    }

    public function get_items($entry_id)
    {
        $builder = $this->db->table('accounting_items');
        $builder->where('entry_id', $entry_id);
        return $builder->get()->getResult();
    }

    public function exists($entry_id)
    {
        $builder = $this->db->table($this->table);
        $builder->where('entry_id', $entry_id);
        return ($builder->get()->getNumRows() == 1);
    }

    public function save_entry(&$entry_data, &$items_data, $entry_id = false)
    {
        $this->db->transStart();

        // Save Header
        if (!$entry_id || !$this->exists($entry_id)) {
            if ($this->db->table($this->table)->insert($entry_data)) {
                $entry_data['entry_id'] = $this->db->insertID();
                $entry_id = $entry_data['entry_id'];
            }
        } else {
            $builder = $this->db->table($this->table);
            $builder->where('entry_id', $entry_id);
            $builder->update($entry_data);
        }

        // Delete existing items for update
        if ($entry_id) {
            $builder = $this->db->table('accounting_items');
            $builder->where('entry_id', $entry_id);
            $builder->delete();
        }

        // Validate items balance
        $total_debit = 0.0;
        $total_credit = 0.0;
        
        foreach ($items_data as $item) {
            $total_debit += (float) $item['debit'];
            $total_credit += (float) $item['credit'];
        }
        
        if (abs($total_debit - $total_credit) > 0.01) {
            // Unbalanced entry, rollback
            $this->db->transRollback();
            return false;
        }

        // Save Items
        foreach ($items_data as $item) {
            $item['entry_id'] = $entry_id;
            $this->db->table('accounting_items')->insert($item);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }
    
    public function get_balance_sheet()
    {
        $builder = $this->db->table('accounting_items ai');
        $builder->select('a.type, a.code, a.name, SUM(ai.debit) as total_debit, SUM(ai.credit) as total_credit, (SUM(ai.debit) - SUM(ai.credit)) as balance');
        $builder->join('accounts a', 'a.account_id = ai.account_id');
        $builder->join('accounting_entries e', 'e.entry_id = ai.entry_id');
        $builder->where('e.deleted', 0);
        $builder->whereIn('a.type', ['Asset', 'Liability', 'Equity']);
        $builder->groupBy('a.account_id');
        $builder->orderBy('a.code', 'ASC');
        
        return $builder->get()->getResult();
    }
    
    public function get_profit_loss()
    {
        $builder = $this->db->table('accounting_items ai');
        $builder->select('a.type, a.code, a.name, SUM(ai.debit) as total_debit, SUM(ai.credit) as total_credit, (SUM(ai.credit) - SUM(ai.debit)) as balance');
        $builder->join('accounts a', 'a.account_id = ai.account_id');
        $builder->join('accounting_entries e', 'e.entry_id = ai.entry_id');
        $builder->where('e.deleted', 0);
        $builder->whereIn('a.type', ['Income', 'Expense']);
        $builder->groupBy('a.account_id');
        $builder->orderBy('a.code', 'ASC');
        
        return $builder->get()->getResult();
    }
}
