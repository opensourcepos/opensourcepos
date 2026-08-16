<?php

namespace app\Libraries;

use App\Models\Item;
use App\Models\Item_quantity;
use App\Models\Rma;
use App\Models\Stock_location;
use CodeIgniter\Session\Session;

/**
 * RMA library
 *
 * Utility to manage the RMA register cart and the selected RMA type, stock
 * location, supplier and customer.
 */
class Rma_lib
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
        if (! $this->session->get('rma_cart')) {
            $this->set_cart([]);
        }

        return $this->session->get('rma_cart');
    }

    public function set_cart(array $cart_data): void
    {
        $this->session->set('rma_cart', $cart_data);
    }

    public function empty_cart(): void
    {
        $this->session->remove('rma_cart');
    }

    public function get_rma_type(): int
    {
        return (int) $this->session->get('rma_type', Rma::TYPE_STOCK);
    }

    public function set_rma_type(int $rma_type): void
    {
        $this->session->set('rma_type', $rma_type);
    }

    public function get_location(): int
    {
        if (! $this->session->get('rma_location')) {
            $this->set_location($this->stock_location->get_default_location_id('rmas'));
        }

        return (int) $this->session->get('rma_location');
    }

    public function set_location(int $location_id): void
    {
        $this->session->set('rma_location', $location_id);
    }

    public function get_supplier_id(): ?int
    {
        $supplier_id = $this->session->get('rma_supplier_id');

        return $supplier_id === null || $supplier_id === '' ? null : (int) $supplier_id;
    }

    public function set_supplier_id(?int $supplier_id): void
    {
        $this->session->set('rma_supplier_id', $supplier_id);
    }

    public function get_customer_id(): ?int
    {
        $customer_id = $this->session->get('rma_customer_id');

        return $customer_id === null || $customer_id === '' ? null : (int) $customer_id;
    }

    public function set_customer_id(?int $customer_id): void
    {
        $this->session->set('rma_customer_id', $customer_id);
    }

    /**
     * Holds the completed sale (receipt) loaded for a client-unit RMA so its
     * items can be picked from the register.
     */
    public function get_sale_id(): ?int
    {
        $sale_id = $this->session->get('rma_sale_id');

        return $sale_id === null || $sale_id === '' ? null : (int) $sale_id;
    }

    public function set_sale_id(?int $sale_id): void
    {
        $this->session->set('rma_sale_id', $sale_id);
    }

    public function get_comment(): string
    {
        $comment = $this->session->get('rma_comment');

        return empty($comment) ? '' : $comment;
    }

    public function set_comment(string $comment): void
    {
        $this->session->set('rma_comment', $comment);
    }

    public function clear_comment(): void
    {
        $this->session->remove('rma_comment');
    }

    /**
     * Adds an item to the RMA cart.
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
                'issue'         => '',
                'serial_number' => '',
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

    public function edit_item(int|string $line, float $quantity, string $issue = '', string $serial_number = ''): bool
    {
        $items = $this->get_cart();

        if (isset($items[$line])) {
            $items[$line]['quantity']      = $quantity;
            $items[$line]['issue']         = $issue;
            $items[$line]['serial_number'] = $serial_number;
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

    /**
     * Re-points every cart line at the given location and refreshes the
     * in-stock figure.
     */
    public function refresh_cart_stock(int $location_id): void
    {
        $items = $this->get_cart();

        foreach ($items as &$item) {
            $item['item_location'] = $location_id;
            $item['stock_name']    = $this->stock_location->get_location_name($location_id);
            $item['in_stock']      = (float) $this->item_quantity->get_item_quantity($item['item_id'], $location_id)->quantity;
        }
        unset($item);

        $this->set_cart($items);
    }

    public function get_total(): float
    {
        $total = 0;

        foreach ($this->get_cart() as $item) {
            $total += (float) $item['quantity'];
        }

        return $total;
    }

    public function clear_all(): void
    {
        $this->empty_cart();
        $this->session->remove('rma_type');
        $this->session->remove('rma_location');
        $this->session->remove('rma_supplier_id');
        $this->session->remove('rma_customer_id');
        $this->session->remove('rma_sale_id');
        $this->clear_comment();
    }
}
