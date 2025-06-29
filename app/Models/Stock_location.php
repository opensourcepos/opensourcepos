<?php

namespace App\Models;

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Model;
use CodeIgniter\Session\Session;

/**
 * Stock_location class
 *
 * @property employee employee
 * @property item item
 * @property session session
 *
 */
class Stock_location extends Model
{
    protected $table = 'stock_locations';
    protected $primaryKey = 'location_id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'location_name',
        'deleted'
    ];

    private Session $session;
    private Employee $employee;

    public function __construct()
    {
        parent::__construct();

        $this->session = session();
    }

    /**
     * @param int $location_id
     * @return bool
     */
    public function exists(int $location_id = NEW_ENTRY): bool
    {
        $builder = $this->db->table('stock_locations');
        $builder->where('location_id', $location_id);

        return ($builder->get()->getNumRows() >= 1);
    }

    /**
     * @return ResultInterface
     */
    public function get_all(): ResultInterface
    {
        $builder = $this->db->table('stock_locations');
        $builder->where('deleted', 0);

        return $builder->get();
    }

    /**
     * @param string $module_id
     * @return ResultInterface
     */
    public function get_undeleted_all(string $module_id = 'items'): ResultInterface
    {
        $builder = $this->db->table('stock_locations');
        $builder->join('permissions AS permissions', 'permissions.location_id = stock_locations.location_id');
        $builder->join('grants AS grants', 'grants.permission_id = permissions.permission_id');
        $builder->where('person_id', $this->session->get('person_id'));
        $builder->like('permissions.permission_id', $module_id, 'after');
        $builder->where('deleted', 0);

        return $builder->get();
    }

    /**
     * @param string $module_id
     * @return bool
     */
    public function show_locations(string $module_id = 'items'): bool
    {
        $stock_locations = $this->get_allowed_locations($module_id);

        return count($stock_locations) > 1;
    }

    /**
     * @return bool
     */
    public function multiple_locations(): bool
    {
        return $this->get_all()->getNumRows() > 1;
    }

    /**
     * @param string $module_id
     * @return array
     */
    public function get_allowed_locations(string $module_id = 'items'): array
    {
        $stock = $this->get_undeleted_all($module_id)->getResultArray();
        $stock_locations = [];

        foreach ($stock as $location_data) {
            $stock_locations[$location_data['location_id']] = $location_data['location_name'];
        }

        return $stock_locations;
    }

    /**
     * @param int $location_id
     * @param string $module_id
     * @return bool
     */
    public function is_allowed_location(int $location_id, string $module_id = 'items'): bool
    {
        $builder = $this->db->table('stock_locations');
        $builder->join('permissions AS permissions', 'permissions.location_id = stock_locations.location_id');
        $builder->join('grants AS grants', 'grants.permission_id = permissions.permission_id');
        $builder->where('person_id', $this->session->get('person_id'));
        $builder->like('permissions.permission_id', $module_id, 'after');
        $builder->where('stock_locations.location_id', $location_id);
        $builder->where('deleted', 0);

        return ($builder->get()->getNumRows() == 1);    // TODO: ===
    }

    /**
     * @param string $module_id
     * @return int
     */
    public function get_default_location_id(string $module_id = 'items'): int
    {
        $builder = $this->db->table('stock_locations');
        $builder->join('permissions AS permissions', 'permissions.location_id = stock_locations.location_id');
        $builder->join('grants AS grants', 'grants.permission_id = permissions.permission_id');
        $builder->where('person_id', $this->session->get('person_id'));
        $builder->like('permissions.permission_id', $module_id, 'after');
        $builder->where('deleted', 0);
        $builder->limit(1);

        return $builder->get()->getRow()->location_id;    // TODO: this is puking. Trying to get property 'location_id' of non-object
    }

    /**
     * @param int $location_id
     * @return string
     */
    public function get_location_name(int $location_id): string
    {
        $builder = $this->db->table('stock_locations');
        $builder->where('location_id', $location_id);

        return $builder->get()->getRow()->location_name;
    }

    /**
     * @param string $location_name
     * @return int
     */
    public function get_location_id(string $location_name): int
    {
        $builder = $this->db->table('stock_locations');
        $builder->where('location_name', $location_name);

        return $builder->get()->getRow()->location_id;
    }

    /**
     * @param array $location_data
     * @param int $location_id
     * @return bool
     */
    public function save_value(array &$location_data, int $location_id): bool
    {
        $location_name = $location_data['location_name'];

        $location_data_to_save = ['location_name' => $location_name, 'deleted' => 0];

        if (!$this->exists($location_id)) {
            $this->db->transStart();

            $builder = $this->db->table('stock_locations');
            $builder->insert($location_data_to_save);
            $location_id = $this->db->insertID();

            $this->_insert_new_permission('items', $location_id, $location_name);    // TODO: need to refactor out the hungarian notation.
            $this->_insert_new_permission('sales', $location_id, $location_name);
            $this->_insert_new_permission('receivings', $location_id, $location_name);

            // Insert quantities for existing items
            $item = model(Item::class);
            $builder = $this->db->table('item_quantities');
            $items = $item->get_all();

            foreach ($items->getResultArray() as $item) {
                $quantity_data = [
                    'item_id'     => $item['item_id'],
                    'location_id' => $location_id,
                    'quantity'    => 0
                ];
                $builder->insert($quantity_data);
            }

            $this->db->transComplete();

            return $this->db->transStatus();
        }

        $original_location_name = $this->get_location_name($location_id);

        if ($original_location_name != $location_name) {
            $builder = $this->db->table('permissions');
            $builder->delete(['location_id' => $location_id]);

            $this->_insert_new_permission('items', $location_id, $location_name);
            $this->_insert_new_permission('sales', $location_id, $location_name);
            $this->_insert_new_permission('receivings', $location_id, $location_name);
        }

        $builder = $this->db->table('stock_locations');
        $builder->where('location_id', $location_id);

        return $builder->update($location_data_to_save);
    }

    /**
     * @param string $module
     * @param int $location_id
     * @param string $location_name
     * @return void
     */
    private function _insert_new_permission(string $module, int $location_id, string $location_name): void    // TODO: refactor out hungarian notation
    {
        // Insert new permission for stock location
        $permission_id = $module . '_' . str_replace(' ', '_', $location_name);    // TODO: String interpolation
        $permission_data = ['permission_id' => $permission_id, 'module_id' => $module, 'location_id' => $location_id];

        $builder = $this->db->table('permissions');
        $builder->insert($permission_data);

        // Insert grants for new permission
        $employee = model(Employee::class);
        $employees = $employee->get_all();

        $builder = $this->db->table('grants');

        foreach ($employees->getResultArray() as $employee) {
            $this->employee = model(Employee::class);

            // Retrieve the menu_group assigned to the grant for the module and use that for the new stock locations
            $menu_group = $this->employee->get_menu_group($module, $employee['person_id']);

            $grants_data = ['permission_id' => $permission_id, 'person_id' => $employee['person_id'], 'menu_group' => $menu_group];

            $builder->insert($grants_data);
        }
    }

    /**
     * Deletes one item
     * @param int|null $location_id
     * @param bool $purge
     * @return bool
     */
    public function delete($location_id = null, bool $purge = false): bool
    {
        $this->db->transStart();

        $builder = $this->db->table('stock_locations');
        $builder->where('location_id', $location_id);
        $builder->update(['deleted' => 1]);

        $builder = $this->db->table('permissions');
        $builder->delete(['location_id' => $location_id]);

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
