<?php

namespace app\Libraries;

use CodeIgniter\Model;
use CodeIgniter\Session\Session;
use App\Models\Stock_location;

/**
 * Item library
 *
 * Library with utilities to manage items
 */

class Item_lib
{
    private Session $session;
    private Stock_location $stock_location;

    public function __construct()
    {
        $this->session = Session();
        $this->stock_location = model(Stock_location::class);
    }

    /**
     * @return string
     */
    public function get_item_location(): string
    {
        if (!$this->session->get('item_location')) {
            $location_id = $this->stock_location->get_default_location_id();
            $this->set_item_location($location_id);
        }

        return $this->session->get('item_location');
    }

    /**
     * @param array|string|null $location
     * @return void
     */
    public function set_item_location(array|string|null $location): void
    {
        if (is_array($location)) {
            $location = $location[0] ?? null;
        }

        $this->session->set('item_location', $location);
    }

    /**
     * Returns the stock locations that should be shown as quantity columns in the item list.
     *
     * @return list<int>
     */
    public function get_item_locations(): array
    {
        $locations = $this->session->get('item_locations');

        if (empty($locations)) {
            $locations = [(int)$this->get_item_location()];
        }

        return array_values(array_map('intval', $locations));
    }

    /**
     * @param array|int $locations
     * @return void
     */
    public function set_item_locations(array|int $locations): void
    {
        $this->session->set('item_locations', array_values(array_map('intval', (array)$locations)));
    }

    /**
     * @return void
     */
    public function clear_item_location(): void    // TODO: This isn't called from anywhere in the code.
    {
        $this->session->remove('item_location');
    }
}
