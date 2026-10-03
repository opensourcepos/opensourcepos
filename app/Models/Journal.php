<?php

namespace App\Models;

use CodeIgniter\Model;

class Journal extends Model
{
    protected $table = 'journals';
    protected $primaryKey = 'journal_id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'name',
        'code',
        'type',
        'deleted'
    ];

    public function get_info($journal_id)
    {
        $builder = $this->db->table($this->table);
        $builder->where('journal_id', $journal_id);
        $query = $builder->get();

        if ($query->getNumRows() == 1) {
            return $query->getRow();
        } else {
            $obj = new \stdClass();
            $obj->journal_id = null;
            $obj->name = '';
            $obj->code = '';
            $obj->type = 'General';
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
}
