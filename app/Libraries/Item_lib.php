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
     * @param string|null $location
     * @return void
     */
    public function set_item_location(?string $location): void
    {
        $this->session->set('item_location', $location);
    }

    /**
     * @return void
     */
    public function clear_item_location(): void    // TODO: This isn't called from anywhere in the code.
    {
        $this->session->remove('item_location');
    }
}
