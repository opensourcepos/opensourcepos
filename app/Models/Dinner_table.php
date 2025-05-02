<?php

namespace App\Models;

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;

/**
 * Dinner_table class
 */
class Dinner_table extends Model
{
    protected $table = 'dinner_tables';
    protected $primaryKey = 'dinner_table_id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'name',
        'status',
        'deleted'
    ];

    /**
     * @param int $dinner_table_id
     * @return bool
     */
    public function exists(int $dinner_table_id): bool
    {
        $builder = $this->db->table('dinner_tables');
        $builder->where('dinner_table_id', $dinner_table_id);

        return ($builder->get()->getNumRows() >= 1);
    }

    /**
     * @param array $table_data
     * @param int $dinner_table_id
     * @return bool
     */
    public function save_value(array $table_data, int $dinner_table_id): bool
    {
        $table_data_to_save = ['name' => $table_data['name'], 'deleted' => 0];

        $builder = $this->db->table('dinner_tables');
        if (!$this->exists($dinner_table_id)) {
            return $builder->insert($table_data_to_save);
        }

        $builder->where('dinner_table_id', $dinner_table_id);

        return $builder->update($table_data_to_save);
    }

    /**
     * Get empty tables
     */
    public function get_empty_tables(?int $current_dinner_table_id): array
    {
        $builder = $this->db->table('dinner_tables');
        $builder->where('status', 0);
        $builder->orWhere('dinner_table_id', $current_dinner_table_id);
        $builder->where('deleted', 0);

        $empty_tables = $builder->get()->getResultArray();

        $empty_tables_array = [];    // TODO: Variable names should not contain the name of the datatype.
        foreach ($empty_tables as $empty_table) {
            $empty_tables_array[$empty_table['dinner_table_id']] = $empty_table['name'];
        }

        return $empty_tables_array;
    }

    /**
     * @param int $dinner_table_id
     * @return string
     */
    public function get_name(int $dinner_table_id): string
    {
        if (empty($dinner_table_id)) {
            return '';
        } else {    // TODO: No need for this else statement.  Just put it's contents outside of the else since the if has a return in it.
            $builder = $this->db->table('dinner_tables');
            $builder->where('dinner_table_id', $dinner_table_id);

            return $builder->get()->getRow()->name;
        }
    }

    /**
     * @param int $dinner_table_id
     * @return bool
     */
    public function is_occupied(int $dinner_table_id): bool
    {
        if (empty($dinner_table_id)) {
            return false;
        } else {    // TODO: No need for this else statement.  Just put it's contents outside of the else since the if has a return in it.
            $builder = $this->db->table('dinner_tables');
            $builder->where('dinner_table_id', $dinner_table_id);

            return ($builder->get()->getRow()->status == 1);    // TODO: === ?
        }
    }

    /**
     * @return ResultInterface
     */
    public function get_all(): ResultInterface
    {
        $builder = $this->db->table('dinner_tables');
        $builder->where('deleted', 0);

        return $builder->get();
    }

    /**
     * Deletes one dinner table
     */
    public function delete($dinner_table_id = null, bool $purge = false): bool
    {
        $builder = $this->db->table('dinner_tables');
        $builder->where('dinner_table_id', $dinner_table_id);

        return $builder->update(['deleted' => 1]);
    }

    /**
     * Occupy table
     * Ignore the Delivery and Takeaway "tables".  They should never be occupied.
     */
    public function occupy(int $dinner_table_id): bool
    {
        if ($dinner_table_id > 2) {
            $builder = $this->db->table('dinner_tables');
            $builder->where('dinner_table_id', $dinner_table_id);
            return $builder->update(['status' => 1]);
        } else {    // TODO: THIS ELSE STATEMENT ISN'T NEEDED.  JUST DO THE IF AND THEN RETURN true AFTER IT.
            return true;
        }
    }

    /**
     * Release table
     */
    public function release(int $dinner_table_id): bool
    {
        if ($dinner_table_id > 2) {
            $builder = $this->db->table('dinner_tables');
            $builder->where('dinner_table_id', $dinner_table_id);
            return $builder->update(['status' => 0]);
        } else {
            return true;
        }
    }

    /**
     * Swap tables
     */
    public function swap_tables(int $release_dinner_table_id, int $occupy_dinner_table_id): bool
    {
        return $this->release($release_dinner_table_id) && $this->occupy($occupy_dinner_table_id);
    }
}
