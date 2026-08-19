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
        'deleted',
        'is_default',
        'sort_order'
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
    public function getAll(): ResultInterface
    {
        $builder = $this->db->table('stock_locations');
        $builder->where('deleted', 0);
        $builder->orderBy('sort_order', 'ASC');

        return $builder->get();
    }

    /**
     * @param string $moduleId
     * @return ResultInterface
     */
    public function getUndeletedAll(string $moduleId = 'items'): ResultInterface
    {
        $builder = $this->db->table('stock_locations');
        $builder->join('permissions AS permissions', 'permissions.location_id = stock_locations.location_id');
        $builder->join('grants AS grants', 'grants.permission_id = permissions.permission_id');
        $builder->where('person_id', $this->session->get('person_id'));
        $builder->like('permissions.permission_id', $moduleId, 'after');
        $builder->where('deleted', 0);
        $builder->orderBy('stock_locations.sort_order', 'ASC');

        return $builder->get();
    }

    /**
     * @param string $module_id
     * @return bool
     */
    public function showLocations(string $module_id = 'items'): bool
    {
        $stockLocations = $this->get_allowed_locations($module_id);

        return count($stockLocations) > 1;
    }

    /**
     * @return bool
     */
    public function multipleLocations(): bool
    {
        return $this->getAll()->getNumRows() > 1;
    }

    /**
     * @param string $module_id
     * @return array
     */
    public function get_allowed_locations(string $module_id = 'items'): array
    {
        $stock = $this->getUndeletedAll($module_id)->getResultArray();
        $stockLocations = [];

        foreach ($stock as $locationData) {
            $stockLocations[$locationData['location_id']] = $locationData['location_name'];
        }

        return $stockLocations;
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
     * @param string $moduleId
     * @return int|null
     */
    public function getDefaultLocationId(string $moduleId = 'items'): ?int
    {
        $builder = $this->db->table('stock_locations');
        $builder->join('permissions AS permissions', 'permissions.location_id = stock_locations.location_id');
        $builder->join('grants AS grants', 'grants.permission_id = permissions.permission_id');
        $builder->where('person_id', $this->session->get('person_id'));
        $builder->like('permissions.permission_id', $moduleId, 'after');
        $builder->where('deleted', 0);
        $builder->orderBy('stock_locations.is_default', 'DESC');
        $builder->orderBy('stock_locations.sort_order', 'ASC');
        $builder->limit(1);

        $row = $builder->get()->getRow();

        return $row?->location_id;
    }

    /**
     * Marks a single stock location as the default, clearing the flag on all others.
     * @param int $locationId
     * @return bool
     */
    public function setDefaultLocation(int $locationId): bool
    {
        $this->db->transStart();

        $builder = $this->db->table('stock_locations');
        $builder->update(['is_default' => 0]);

        $builder = $this->db->table('stock_locations');
        $builder->where('location_id', $locationId);
        $builder->update(['is_default' => 1]);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Saves sort order of locations.
     * A rotation can still collide against the unique sort_order index mid-batch, so
     * values are shifted to a temp range first, then to final. The temp offset only
     * needs to clear count($orderedLocationIds), since active sort_order values are
     * always kept as a dense 0..N-1 range (deleted rows are NULL, not part of it).
     */
    public function saveSortOrder(array $orderedLocationIds): bool
    {
        if (empty($orderedLocationIds)) {
            return true;
        }

        $table = $this->db->prefixTable('stock_locations');
        $tempOffset = count($orderedLocationIds);

        $this->db->transStart();

        $this->runSortOrderCaseUpdate($table, $orderedLocationIds, $tempOffset);
        $this->runSortOrderCaseUpdate($table, $orderedLocationIds, 0);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * @param string $table
     * @param array $orderedLocationIds
     * @param int $offset
     * @return void
     */
    private function runSortOrderCaseUpdate(string $table, array $orderedLocationIds, int $offset): void
    {
        $caseSql = 'CASE location_id';
        $bindings = [];

        foreach ($orderedLocationIds as $index => $locationId) {
            $caseSql .= ' WHEN ? THEN ?';
            $bindings[] = $locationId;
            $bindings[] = $index + $offset;
        }

        $caseSql .= ' END';

        $placeholders = implode(',', array_fill(0, count($orderedLocationIds), '?'));
        $bindings = array_merge($bindings, $orderedLocationIds);

        $this->db->query(
            "UPDATE $table SET sort_order = $caseSql WHERE location_id IN ($placeholders)",
            $bindings
        );
    }

    /**
     * @param int $location_id
     * @return string
     */
    public function getLocationName(int $location_id): string
    {
        $builder = $this->db->table('stock_locations');
        $builder->where('location_id', $location_id);

        return $builder->get()->getRow()->location_name;
    }

    /**
     * @param array $locationData
     * @param int $locationId
     * @return bool
     */
    public function saveValue(array &$locationData, int $locationId): bool
    {
        $locationName = $locationData['location_name'];

        $locationDataToSave = ['location_name' => $locationName, 'deleted' => 0];

        if (!$this->exists($locationId)) {
            $this->db->transStart();

            $builder = $this->db->table('stock_locations');
            $locationDataToSave['sort_order'] = $builder->where('deleted', 0)->countAllResults();

            $builder = $this->db->table('stock_locations');
            $builder->insert($locationDataToSave);
            $locationId = $this->db->insertID();
            $locationData['location_id'] = $locationId;

            $this->_insert_new_permission('items', $locationId, $locationName);    // TODO: need to refactor out the hungarian notation.
            $this->_insert_new_permission('sales', $locationId, $locationName);
            $this->_insert_new_permission('receivings', $locationId, $locationName);

            // Insert quantities for existing items
            $item = model(Item::class);
            $builder = $this->db->table('item_quantities');
            $items = $item->get_all();

            foreach ($items->getResultArray() as $item) {
                $quantityData = [
                    'item_id'     => $item['item_id'],
                    'location_id' => $locationId,
                    'quantity'    => 0
                ];
                $builder->insert($quantityData);
            }

            $this->db->transComplete();

            return $this->db->transStatus();
        }

        $originalLocationName = $this->getLocationName($locationId);

        if ($originalLocationName != $locationName) {
            $builder = $this->db->table('permissions');
            $builder->delete(['location_id' => $locationId]);

            $this->_insert_new_permission('items', $locationId, $locationName);
            $this->_insert_new_permission('sales', $locationId, $locationName);
            $this->_insert_new_permission('receivings', $locationId, $locationName);
        }

        $builder = $this->db->table('stock_locations');
        $builder->where('location_id', $locationId);

        return $builder->update($locationDataToSave);
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
     * @param int|null $locationId
     * @param bool $purge
     * @return bool
     */
    public function delete($locationId = null, bool $purge = false): bool
    {
        $this->db->transStart();

        // Deleted rows don't occupy a sort position, so sort_order is cleared rather than
        // pushed to some new high value. This keeps the active range a dense 0..N-1 no
        // matter how many locations have been deleted over the life of the install.
        $builder = $this->db->table('stock_locations');
        $builder->where('location_id', $locationId);
        $builder->update(['deleted' => 1, 'sort_order' => null]);

        $builder = $this->db->table('permissions');
        $builder->delete(['location_id' => $locationId]);

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
