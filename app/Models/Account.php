<?php

namespace App\Models;

use CodeIgniter\Model;

class Account extends Model
{
    protected $table = 'accounts';
    protected $primaryKey = 'account_id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'code',
        'name',
        'type',
        'parent_id',
        'deleted'
    ];

    public function get_info($account_id)
    {
        $builder = $this->db->table($this->table);
        $builder->where('account_id', $account_id);
        $query = $builder->get();

        if ($query->getNumRows() == 1) {
            return $query->getRow();
        } else {
            $obj = new \stdClass();
            $obj->account_id = null;
            $obj->code = '';
            $obj->name = '';
            $obj->type = 'Asset';
            $obj->parent_id = null;
            $obj->deleted = 0;
            return $obj;
        }
    }

    public function get_all()
    {
        $builder = $this->db->table($this->table);
        $builder->where('deleted', 0);
        $builder->orderBy('code', 'asc');
        return $builder->get();
    }

    public function get_by_code($code)
    {
        $builder = $this->db->table($this->table);
        $builder->where('code', $code);
        $query = $builder->get();
        if ($query->getNumRows() == 1) {
            return $query->getRow();
        }
        return null;
    }

    public function exists($account_id)
    {
        $builder = $this->db->table($this->table);
        $builder->where('account_id', $account_id);
        return ($builder->get()->getNumRows() == 1);
    }

    public function save_value(&$account_data, $account_id = false)
    {
        if (!$account_id || !$this->exists($account_id)) {
            if ($this->db->table($this->table)->insert($account_data)) {
                $account_data['account_id'] = $this->db->insertID();
                return true;
            }
            return false;
        }

        $builder = $this->db->table($this->table);
        $builder->where('account_id', $account_id);
        return $builder->update($account_data);
    }
}
