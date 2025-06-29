<?php

namespace App\Models;

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;
use stdClass;

/**
 * Expense_category class
 */
class Expense_category extends Model
{
    protected $table = 'expense_categories';
    protected $primaryKey = 'expense_category_id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'category_name',
        'category_description',
        'deleted'
    ];

    /**
     * Determines if a given Expense_id is an Expense category
     */
    public function exists(int $expense_category_id): bool
    {
        $builder = $this->db->table('expense_categories');
        $builder->where('expense_category_id', $expense_category_id);

        return ($builder->get()->getNumRows() == 1);    // TODO: ===
    }

    /**
     * Gets total of rows
     */
    public function get_total_rows(): int
    {
        $builder = $this->db->table('expense_categories');
        $builder->where('deleted', 0);

        return $builder->countAllResults();
    }

    /**
     * Gets information about a particular category
     */
    public function get_info(int $expense_category_id): object
    {
        $builder = $this->db->table('expense_categories');
        $builder->where('expense_category_id', $expense_category_id);
        $builder->where('deleted', 0);
        $query = $builder->get();

        if ($query->getNumRows() == 1) {    // TODO: ===
            return $query->getRow();
        } else {
            // Get empty base parent object, as $item_kit_id is NOT an item kit
            $expense_obj = new stdClass();

            // Get all the fields from items table
            foreach ($this->db->getFieldNames('expense_categories') as $field) {
                $expense_obj->$field = '';
            }

            return $expense_obj;
        }
    }

    /**
     * Returns all the expense_categories
     */
    public function get_all(int $rows = 0, int $limit_from = 0, bool $no_deleted = false): ResultInterface
    {
        $builder = $this->db->table('expense_categories');

        if ($no_deleted) {
            $builder->where('deleted', 0);
        }

        $builder->orderBy('category_name', 'asc');

        if ($rows > 0) {
            $builder->limit($rows, $limit_from);
        }

        return $builder->get();
    }

    /**
     * Gets information about multiple expense_category_id
     */
    public function get_multiple_info(array $expense_category_ids): ResultInterface
    {
        $builder = $this->db->table('expense_categories');
        $builder->whereIn('expense_category_id', $expense_category_ids);
        $builder->orderBy('category_name', 'asc');

        return $builder->get();
    }

    /**
     * Inserts or updates an expense_category
     */
    public function save_value(array &$expense_category_data, int $expense_category_id = NEW_ENTRY): bool
    {
        $builder = $this->db->table('expense_categories');

        if ($expense_category_id == NEW_ENTRY || !$this->exists($expense_category_id)) {
            if ($builder->insert($expense_category_data)) {
                $expense_category_data['expense_category_id'] = $this->db->insertID();

                return true;
            }

            return false;
        }

        $builder->where('expense_category_id', $expense_category_id);

        return $builder->update($expense_category_data);
    }

    /**
     * Deletes a list of expense_category
     */
    public function delete_list(array $expense_category_ids): bool
    {
        $builder = $this->db->table('expense_categories');
        $builder->whereIn('expense_category_id', $expense_category_ids);

        return $builder->update(['deleted' => 1]);
    }

    /**
     * Gets rows
     */
    public function get_found_rows(string $search): int
    {
        return $this->search($search, 0, 0, 'category_name', 'asc', true);
    }

    /**
     * Perform a search on expense_category
     */
    public function search(string $search, ?int $rows = 0, ?int $limit_from = 0, ?string $sort = 'category_name', ?string $order = 'asc', ?bool $count_only = false)
    {
        // Set default values
        if ($rows == null) $rows = 0;
        if ($limit_from == null) $limit_from = 0;
        if ($sort == null) $sort = 'category_name';
        if ($order == null) $order = 'asc';
        if ($count_only == null) $count_only = false;

        $builder = $this->db->table('expense_categories AS expense_categories');

        // get_found_rows case
        if ($count_only) {
            $builder->select('COUNT(expense_categories.expense_category_id) as count');
        }

        $builder->groupStart();
        $builder->like('category_name', $search);
        $builder->orLike('category_description', $search);
        $builder->groupEnd();
        $builder->where('deleted', 0);

        // get_found_rows case
        if ($count_only) {
            return $builder->get()->getRow()->count;
        }

        $builder->orderBy($sort, $order);

        if ($rows > 0) {
            $builder->limit($rows, $limit_from);
        }

        return $builder->get();
    }
}
