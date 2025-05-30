<?php

namespace app\Libraries;

use App\Models\Attribute;
use App\Models\Item;
use App\Models\Item_kit_items;
use App\Models\Item_quantity;
use App\Models\Receiving;
use App\Models\Stock_location;

use CodeIgniter\Session\Session;
use Config\OSPOS;

/**
 * Receiving library
 *
 * Library with utilities to manage receivings
 */
class Receiving_lib
{
    private Attribute $attribute;
    private Item $item;
    private Item_kit_items $item_kit_items;
    private Item_quantity $item_quantity;
    private Receiving $receiving;
    private Stock_location $stock_location;
    private Session $session;

    public function __construct()
    {
        $this->attribute = model(Attribute::class);
        $this->item = model(Item::class);
        $this->item_kit_items = model(Item_kit_items::class);
        $this->item_quantity = model(Item_quantity::class);
        $this->receiving = model(Receiving::class);
        $this->stock_location = model(Stock_location::class);

        $this->session = session();
    }

    /**
     * @return array
     */
    public function get_cart(): array
    {
        if (!$this->session->get('recv_cart')) {
            $this->set_cart([]);
        }

        return $this->session->get('recv_cart');
    }

    /**
     * @param array $cart_data
     * @return void
     */
    public function set_cart(array $cart_data): void
    {
        $this->session->set('recv_cart', $cart_data);
    }

    /**
     * @return void
     */
    public function empty_cart(): void
    {
        $this->session->remove('recv_cart');
    }

    /**
     * @return int
     */
    public function get_supplier(): int
    {
        if (!$this->session->get('recv_supplier')) {
            $this->set_supplier(-1);    // TODO: Replace -1 with a constant.
        }

        return $this->session->get('recv_supplier');
    }

    /**
     * @param int $supplier_id
     * @return void
     */
    public function set_supplier(int $supplier_id): void
    {
        $this->session->set('recv_supplier', $supplier_id);
    }

    /**
     * @return void
     */
    public function remove_supplier(): void
    {
        $this->session->remove('recv_supplier');
    }

    /**
     * @return string
     */
    public function get_mode(): string
    {
        if (!$this->session->get('recv_mode')) {
            $this->set_mode('receive');
        }

        return $this->session->get('recv_mode');
    }

    /**
     * @param string $mode
     * @return void
     */
    public function set_mode(string $mode): void
    {
        $this->session->set('recv_mode', $mode);
    }

    /**
     * @return void
     */
    public function clear_mode(): void    // TODO: This function verb is inconsistent from the others.  Consider refactoring to remove_mode()
    {
        $this->session->remove('recv_mode');
    }

    /**
     * @return int
     */
    public function get_stock_source(): int
    {
        if (!$this->session->get('recv_stock_source')) {
            $this->set_stock_source($this->stock_location->get_default_location_id('receivings'));
        }

        return $this->session->get('recv_stock_source');
    }

    /**
     * @return string
     */
    public function get_comment(): string
    {
        $comment = $this->session->get('recv_comment');

        return empty($comment) ? '' : $comment;
    }

    /**
     * @param string $comment
     * @return void
     */
    public function set_comment(string $comment): void
    {
        $this->session->set('recv_comment', $comment);
    }

    /**
     * @return void
     */
    public function clear_comment(): void    // TODO: This function verb is inconsistent from the others.  Consider refactoring to remove_comment()
    {
        $this->session->remove('recv_comment');
    }

    /**
     * @return string
     */
    public function get_reference(): string
    {
        return $this->session->get('recv_reference') ?? '';
    }

    /**
     * @param string $reference
     * @return void
     */
    public function set_reference(string $reference): void
    {
        $this->session->set('recv_reference', $reference);
    }

    /**
     * @return void
     */
    public function clear_reference(): void    // TODO: This function verb is inconsistent from the others.  Consider refactoring to remove_reference()
    {
        $this->session->remove('recv_reference');
    }

    /**
     * @return bool
     */
    public function is_print_after_sale(): bool
    {
        return $this->session->get('recv_print_after_sale') == 'true'
            || $this->session->get('recv_print_after_sale') == '1';
    }

    /**
     * @param bool $print_after_sale
     * @return void
     */
    public function set_print_after_sale(bool $print_after_sale): void
    {
        $this->session->set('recv_print_after_sale', $print_after_sale);
    }

    /**
     * @param int $stock_source
     * @return void
     */
    public function set_stock_source(int $stock_source): void
    {
        $this->session->set('recv_stock_source', $stock_source);
    }

    /**
     * @return void
     */
    public function clear_stock_source(): void
    {
        $this->session->remove('recv_stock_source');
    }

    /**
     * @return string
     */
    public function get_stock_destination(): string
    {
        if (!$this->session->get('recv_stock_destination')) {
            $this->set_stock_destination($this->stock_location->get_default_location_id('receivings'));
        }

        return $this->session->get('recv_stock_destination');
    }

    /**
     * @param string $stock_destination
     * @return void
     */
    public function set_stock_destination(?string $stock_destination): void
    {
        $this->session->set('recv_stock_destination', $stock_destination);
    }

    /**
     * @return void
     */
    public function clear_stock_destination(): void
    {
        $this->session->remove('recv_stock_destination');
    }
    // TODO: This array signature needs to be reworked.  It's way too long. Perhaps an object needs to be passed rather than these?

    /**
     * @param string $itemId
     * @param int $quantity
     * @param int|null $itemLocation
     * @param float $discount
     * @param int $discountType
     * @param float|null $price
     * @param string|null $description
     * @param string|null $serialNumber
     * @param float|null $receivingQuantity
     * @param int|null $receivingId
     * @param bool $includeDeleted
     * @return bool
     */
    public function add_item(string $itemId, int $quantity = 1, ?int $itemLocation = null, float $discount = 0, int $discountType = 0, ?float $price = null, ?string $description = null, ?string $serialNumber = null, ?float $receivingQuantity = null, ?int $receivingId = null, bool $includeDeleted = false): bool
    {
        $config = config(OSPOS::class)->settings;
        $itemInfo = $this->item->get_info_by_id_or_number($itemId, $includeDeleted);

        if (empty($itemInfo)) {
            return false;
        }

        $itemId = $itemInfo->item_id;
        $items = $this->get_cart();

        $maxKey = 0;
        $itemAlreadyInSale = false;
        $updateKey = 0;

        foreach ($items as $item) {
            if ($maxKey <= $item['line']) {
                $maxKey = $item['line'];
            }

            if ($item['item_id'] == $itemId && $item['item_location'] == $itemLocation) {
                $itemAlreadyInSale = true;
                $updateKey = $item['line'];
            }
        }

        $insertKey = $maxKey + 1;
        $itemInfo = $this->item->get_info((int) $itemId);

        $price = $price != null ? $price : $itemInfo->cost_price;

        if ($config['multi_pack_enabled']) {
            $itemInfo->name .= NAME_SEPARATOR . $itemInfo->pack_name;
        }

        if ($itemInfo->receiving_quantity == 0 || $itemInfo->receiving_quantity == 1) {
            $receivingQuantityChoices = [1 => 'x1'];
        } else {
            $receivingQuantityChoices = [
                to_quantity_decimals($itemInfo->receiving_quantity) => 'x' . to_quantity_decimals($itemInfo->receiving_quantity),
                1 => 'x1'
            ];
        }

        if (is_null($receivingQuantity)) {
            $receivingQuantity = $itemInfo->receiving_quantity;
        }

        $attributeLinks = $this->attribute->get_link_values((int) $itemId, 'receiving_id', $receivingId, Attribute::SHOW_IN_RECEIVINGS)->getRowObject();

        $item = [
            $insertKey => [
                'item_id'                    => $itemId,
                'item_location'              => $itemLocation,
                'item_number'                => $itemInfo->item_number,
                'stock_name'                 => $this->stock_location->get_location_name($itemLocation),
                'line'                       => $insertKey,
                'name'                       => $itemInfo->name,
                'description'                => $description != null ? $description : $itemInfo->description,
                'serialnumber'               => $serialNumber != null ? $serialNumber : '',
                'attribute_values'           => $attributeLinks->attribute_values,
                'attribute_dtvalues'         => $attributeLinks->attribute_dtvalues,
                'allow_alt_description'      => $itemInfo->allow_alt_description,
                'is_serialized'              => $itemInfo->is_serialized,
                'quantity'                   => $quantity,
                'discount'                   => $discount,
                'discount_type'              => $discountType,
                'in_stock'                   => $this->item_quantity->get_item_quantity((int) $itemId, $itemLocation)->quantity,
                'price'                      => $price,
                'receiving_quantity'         => $receivingQuantity,
                'receiving_quantity_choices' => $receivingQuantityChoices,
                'total'                      => $this->get_item_total($quantity, $price, $discount, $discountType, $receivingQuantity)
            ]
        ];

        if ($itemAlreadyInSale) {
            $items[$updateKey]['quantity'] += $quantity;
            $items[$updateKey]['total'] = $this->get_item_total($items[$updateKey]['quantity'], $price, $discount, $discountType, $items[$updateKey]['receiving_quantity']);
        } else {
            $items += $item;
        }

        $this->set_cart($items);

        return true;
    }

    /**
     * @param $line
     * @param string $description
     * @param string $serialnumber
     * @param float $quantity
     * @param float $discount
     * @param int|null $discount_type
     * @param float $price
     * @param float $receiving_quantity
     * @return bool
     */
    public function edit_item($line, string $description, string $serialnumber, float $quantity, float $discount, ?int $discount_type, float $price, float $receiving_quantity): bool
    {
        $items = $this->get_cart();
        if (isset($items[$line])) {
            $line = &$items[$line];
            $line['description'] = $description;
            $line['serialnumber'] = $serialnumber;
            $line['quantity'] = $quantity;
            $line['receiving_quantity'] = $receiving_quantity;
            $line['discount'] = $discount;

            if (!is_null($discount_type)) {
                $line['discount_type'] = $discount_type;
            }

            $line['price'] = $price;
            $line['total'] = $this->get_item_total($quantity, $price, $discount, $discount_type, $receiving_quantity);
            $this->set_cart($items);
        }

        return false;    // TODO: This function will always return false.
    }

    /**
     * @param $line int|string The item_number or item_id of the item to be removed from the receiving.
     */
    public function delete_item($line): void
    {
        $items = $this->get_cart();
        unset($items[$line]);
        $this->set_cart($items);
    }

    /**
     * @param int $receipt_receiving_id
     * @return void
     */
    public function return_entire_receiving(int $receipt_receiving_id): void
    {
        // RECV #
        $pieces = explode(' ', $receipt_receiving_id);

        if (preg_match("/(RECV|KIT)/", $pieces[0])) {    // TODO: this needs to be converted to ternary notation.
            $receiving_id = $pieces[1];
        } else {
            $receiving_id = $this->receiving->get_receiving_by_reference($receipt_receiving_id)->getRow()->receiving_id;
        }

        $this->empty_cart();
        $this->remove_supplier();
        $this->clear_comment();

        foreach ($this->receiving->get_receiving_items($receiving_id)->getResult() as $row) {
            $this->add_item($row->item_id, -$row->quantity_purchased, $row->item_location, $row->discount, $row->discount_type, $row->item_unit_price, $row->description, $row->serialnumber, $row->receiving_quantity, $receiving_id, true);
        }

        $this->set_supplier($this->receiving->get_supplier($receiving_id)->person_id);
    }

    /**
     * @param string $external_item_kit_id
     * @param int $item_location
     * @param float $discount
     * @param int $discount_type
     * @return void
     */
    public function add_item_kit(string $external_item_kit_id, int $item_location, float $discount, int $discount_type): void
    {
        // KIT #
        $pieces = explode(' ', $external_item_kit_id);
        $item_kit_id = count($pieces) > 1 ? $pieces[1] : $external_item_kit_id;

        foreach ($this->item_kit_items->get_info($item_kit_id) as $item_kit_item) {
            $this->add_item($item_kit_item['item_id'], $item_kit_item['quantity'], $item_location, $discount, $discount_type);
        }
    }

    /**
     * @param int $receiving_id
     * @return void
     */
    public function copy_entire_receiving(int $receiving_id): void
    {
        $this->empty_cart();
        $this->remove_supplier();

        foreach ($this->receiving->get_receiving_items($receiving_id)->getResult() as $row) {
            $this->add_item($row->item_id, $row->quantity_purchased, $row->item_location, $row->discount, $row->discount_type, $row->item_unit_price, $row->description, $row->serialnumber, $row->receiving_quantity, $receiving_id, true);
        }

        $this->set_supplier((int) $this->receiving->get_supplier($receiving_id)->person_id);
        // $this->set_reference($this->receiving->get_info($receiving_id)->getRow()->reference);    // TODO: If this code won't be added back in, then let's delete it.
    }

    /**
     * @return void
     */
    public function clear_all(): void
    {
        $this->clear_mode();
        $this->empty_cart();
        $this->remove_supplier();
        $this->clear_comment();
        $this->clear_reference();
    }

    /**
     * @param float $quantity
     * @param float $price
     * @param float $discount
     * @param int|null $discount_type
     * @param float $receiving_quantity
     * @return string
     */
    public function get_item_total(float $quantity, float $price, float $discount, ?int $discount_type, float $receiving_quantity): string
    {
        $extended_quantity = bcmul($quantity, $receiving_quantity);
        $total = bcmul($extended_quantity, $price);
        $discount_amount = $discount;

        if ($discount_type == PERCENT) {    // TODO: === ?
            $discount_fraction = bcdiv($discount, 100);
            $discount_amount = bcmul($total, $discount_fraction);
        }

        return bcsub($total, $discount_amount);
    }

    /**
     * @return string
     */
    public function get_total(): string
    {
        $total = 0;
        foreach ($this->get_cart() as $item) {
            $total = bcadd($total, $this->get_item_total(($item['quantity']), $item['price'], $item['discount'], $item['discount_type'], $item['receiving_quantity']));
        }

        return $total;
    }
}
