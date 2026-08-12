<?php

namespace app\Libraries;

use App\Models\Item;
use App\Models\Item_quantity;
use App\Models\Stock_location;
use CodeIgniter\Session\Session;

/**
 * Transfer library
 *
 * Library with utilities to manage the transfer register cart and
 * the source/destination stock locations.
 */
class Transfer_lib
{
    private Item $item;
    private Item_quantity $item_quantity;
    private Stock_location $stock_location;
    private Session $session;

    public function __construct()
    {
        $this->item           = model(Item::class);
        $this->item_quantity  = model(Item_quantity::class);
        $this->stock_location = model(Stock_location::class);

        $this->session = session();
    }

    public function get_cart(): array
    {
        if (! $this->session->get('transfer_cart')) {
            $this->set_cart([]);
        }

        return $this->session->get('transfer_cart');
    }

    public function set_cart(array $cart_data): void
    {
        $this->session->set('transfer_cart', $cart_data);
    }

    public function empty_cart(): void
    {
        $this->session->remove('transfer_cart');
    }

    public function get_stock_source(): int
    {
        if (! $this->session->get('transfer_stock_source')) {
            $this->set_stock_source($this->stock_location->get_default_location_id('receivings'));
        }

        return (int) $this->session->get('transfer_stock_source');
    }

    public function set_stock_source(int $stock_source): void
    {
        $this->session->set('transfer_stock_source', $stock_source);
    }

    public function clear_stock_source(): void
    {
        $this->session->remove('transfer_stock_source');
    }

    public function get_stock_destination(): int
    {
        if (! $this->session->get('transfer_stock_destination')) {
            $this->set_stock_destination($this->stock_location->get_default_location_id('receivings'));
        }

        return (int) $this->session->get('transfer_stock_destination');
    }

    public function set_stock_destination(int $stock_destination): void
    {
        $this->session->set('transfer_stock_destination', $stock_destination);
    }

    public function clear_stock_destination(): void
    {
        $this->session->remove('transfer_stock_destination');
    }

    public function get_comment(): string
    {
        $comment = $this->session->get('transfer_comment');

        return empty($comment) ? '' : $comment;
    }

    public function set_comment(string $comment): void
    {
        $this->session->set('transfer_comment', $comment);
    }

    public function clear_comment(): void
    {
        $this->session->remove('transfer_comment');
    }

    /**
     * Adds an item to the transfer cart.
     *
     * @param int|null $item_location Source location to transfer from
     */
    public function add_item(string $item_id, float $quantity, ?int $item_location = null): bool
    {
        $item_info = $this->item->get_info_by_id_or_number($item_id, true);

        if (empty($item_info)) {
            return false;
        }

        $item_id = (int) $item_info->item_id;
        $items   = $this->get_cart();

        $max_key              = 0;
        $item_already_in_cart = false;
        $update_key           = 0;

        foreach ($items as $item) {
            if ($max_key <= $item['line']) {
                $max_key = $item['line'];
            }

            if ($item['item_id'] === $item_id) {
                $item_already_in_cart = true;
                $update_key           = $item['line'];
            }
        }

        $insert_key = $max_key + 1;
        $item_info  = $this->item->get_info($item_id);

        $item = [
            $insert_key => [
                'item_id'       => $item_id,
                'item_location' => $item_location,
                'item_number'   => $item_info->item_number,
                'stock_name'    => $item_location !== null ? $this->stock_location->get_location_name($item_location) : '',
                'line'          => $insert_key,
                'name'          => $item_info->name,
                'description'   => $item_info->description,
                'quantity'      => $quantity,
                'in_stock'      => $item_location !== null ? (float) $this->item_quantity->get_item_quantity($item_id, $item_location)->quantity : 0,
            ],
        ];

        if ($item_already_in_cart) {
            $items[$update_key]['quantity'] += $quantity;
        } else {
            $items += $item;
        }

        $this->set_cart($items);

        return true;
    }

    /**
     * Edits the quantity of a cart line.
     */
    public function edit_item(int|string $line, float $quantity): bool
    {
        $items = $this->get_cart();

        if (isset($items[$line])) {
            $items[$line]['quantity'] = $quantity;
            $this->set_cart($items);

            return true;
        }

        return false;
    }

    public function delete_item(int|string $line): void
    {
        $items = $this->get_cart();
        unset($items[$line]);
        $this->set_cart($items);
    }

    public function clear_all(): void
    {
        $this->empty_cart();
        $this->clear_stock_source();
        $this->clear_stock_destination();
        $this->clear_comment();
    }

    public function get_total(): float
    {
        $total = 0;

        foreach ($this->get_cart() as $item) {
            $total += (float) $item['quantity'];
        }

        return $total;
    }
}
