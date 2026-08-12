<?php

namespace App\Controllers;

use App\Libraries\Token_lib;
use App\Libraries\Transfer_lib;
use App\Models\Item;
use App\Models\Item_quantity;
use App\Models\Stock_location;
use App\Models\Transfer;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Transfers controller
 *
 * Moves stock between two stock locations. Used in app/Config/Routes.php
 */
class Transfers extends Secure_Controller
{
    private Token_lib $token_lib;
    private Transfer_lib $transfer_lib;
    private Item $item;
    private Item_quantity $item_quantity;
    private Stock_location $stock_location;
    private Transfer $transfer;

    public function __construct()
    {
        parent::__construct('transfers');

        $this->token_lib      = new Token_lib();
        $this->transfer_lib   = new Transfer_lib();
        $this->item           = model(Item::class);
        $this->item_quantity  = model(Item_quantity::class);
        $this->stock_location = model(Stock_location::class);
        $this->transfer       = model(Transfer::class);
    }

    public function getIndex(): string
    {
        return $this->_reload();
    }

    /**
     * Returns search suggestions for an item. Used in app/Views/transfers/register.php
     *
     * @noinspection PhpUnused
     */
    public function getStockItemSearch(): ResponseInterface
    {
        $search      = $this->request->getGet('term');
        $suggestions = $this->item->get_stock_search_suggestions($search, ['search_custom' => false, 'is_deleted' => false], true);

        return $this->response->setJSON($suggestions);
    }

    /**
     * Change the source/destination locations for the transfer.
     * Used in app/Views/transfers/register.php
     *
     * @noinspection PhpUnused
     */
    public function postChangeLocation(): string
    {
        $stock_source      = $this->request->getPost('stock_source', FILTER_SANITIZE_NUMBER_INT);
        $stock_destination = $this->request->getPost('stock_destination', FILTER_SANITIZE_NUMBER_INT);

        if ($this->stock_location->exists($stock_source) && $this->stock_location->exists($stock_destination)) {
            $this->transfer_lib->set_stock_source($stock_source);
            $this->transfer_lib->set_stock_destination($stock_destination);
        }

        return $this->_reload();
    }

    /**
     * Sets the transfer comment. Used in app/Views/transfers/register.php
     *
     * @noinspection PhpUnused
     */
    public function postSetComment(): ResponseInterface
    {
        $this->transfer_lib->set_comment($this->request->getPost('comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Add an item to the transfer cart. Used in app/Views/transfers/register.php
     *
     * @noinspection PhpUnused
     */
    public function postAdd(): string
    {
        $data = [];

        $item_id_or_number = $this->request->getPost('item', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $quantity          = 1;
        $this->token_lib->parse_barcode($quantity, $price, $item_id_or_number);

        $item_location = $this->transfer_lib->get_stock_source();

        if (! $this->transfer_lib->add_item($item_id_or_number, $quantity, $item_location)) {
            $data['error'] = lang('Transfers.unable_to_add_item');
        }

        return $this->_reload($data);
    }

    /**
     * Edit a line item in the current transfer. Used in app/Views/transfers/register.php
     *
     * @noinspection PhpUnused
     */
    public function postEditItem(int|string|null $line): string
    {
        $data = [];

        $validation_rule = [
            'quantity' => 'trim|required|decimal_locale',
        ];

        $quantity = parse_quantity($this->request->getPost('quantity'));

        if ($this->validate($validation_rule)) {
            $this->transfer_lib->edit_item($line, $quantity);
        } else {
            $data['error'] = lang('Transfers.error_editing_item');
        }

        return $this->_reload($data);
    }

    /**
     * Delete a line item from the current transfer. Used in app/Views/transfers/register.php
     *
     * @noinspection PhpUnused
     */
    public function getDeleteItem(int|string|null $line): string
    {
        $this->transfer_lib->delete_item($line);

        return $this->_reload();
    }

    /**
     * Complete the transfer: persist it and show the receipt.
     * Used in app/Views/transfers/register.php
     *
     * @noinspection PhpUnused
     */
    public function postComplete(): string
    {
        $data = [];

        $cart              = $this->transfer_lib->get_cart();
        $stock_source      = $this->transfer_lib->get_stock_source();
        $stock_destination = $this->transfer_lib->get_stock_destination();
        $comment           = $this->transfer_lib->get_comment();

        if ($stock_source === $stock_destination) {
            $data['error'] = lang('Transfers.error_same_location');

            return $this->_reload($data);
        }

        // Validate quantities don't exceed available stock at the source
        foreach ($cart as $item) {
            $in_stock = (float) $this->item_quantity->get_item_quantity($item['item_id'], $stock_source)->quantity;
            if ((float) $item['quantity'] > $in_stock) {
                $data['error'] = lang('Transfers.error_insufficient_stock');

                return $this->_reload($data);
            }
        }

        $employee_id = $this->employee->get_logged_in_employee_info()->person_id;
        $transfer_id = $this->transfer->save_value($cart, $employee_id, $stock_source, $stock_destination, $comment);

        if ($transfer_id === -1) {
            $data['error_message'] = lang('Transfers.transaction_failed');

            $view = view('transfers/receipt', $data);
        } else {
            $transfer_info = $this->transfer->get_info($transfer_id)->getRowArray();

            $data['transfer_id']        = 'TRF ' . $transfer_id;
            $data['transaction_time']   = to_datetime(strtotime($transfer_info['transfer_time']));
            $data['employee']           = $transfer_info['employee_name'];
            $data['location_from_name'] = $transfer_info['location_from_name'];
            $data['location_to_name']   = $transfer_info['location_to_name'];
            $data['comment']            = $transfer_info['comment'];
            $data['barcode']            = '';

            foreach ($this->transfer->get_transfer_items($transfer_id)->getResultArray() as $item) {
                $data['cart'][] = [
                    'name'        => $item['name'],
                    'item_number' => $item['item_number'],
                    'quantity'    => $item['quantity'],
                ];
            }

            $view = view('transfers/receipt', $data);
        }

        $this->transfer_lib->clear_all();

        return $view;
    }

    /**
     * Shows the receipt for a completed transfer. Used in app/Views/transfers/register.php
     *
     * @noinspection PhpUnused
     */
    public function getReceipt(int|string|null $transfer_id): string
    {
        $transfer_info = $this->transfer->get_info((int) $transfer_id)->getRowArray();
        if ($transfer_info === null) {
            return view('transfers/receipt', ['error_message' => lang('Transfers.transaction_failed')]);
        }

        $data['transfer_id']        = 'TRF ' . $transfer_id;
        $data['transaction_time']   = to_datetime(strtotime($transfer_info['transfer_time']));
        $data['employee']           = $transfer_info['employee_name'];
        $data['location_from_name'] = $transfer_info['location_from_name'];
        $data['location_to_name']   = $transfer_info['location_to_name'];
        $data['comment']            = $transfer_info['comment'];
        $data['cart']               = [];
        $data['barcode']            = '';

        foreach ($this->transfer->get_transfer_items((int) $transfer_id)->getResultArray() as $item) {
            $data['cart'][] = [
                'name'        => $item['name'],
                'item_number' => $item['item_number'],
                'quantity'    => $item['quantity'],
            ];
        }

        return view('transfers/receipt', $data);
    }

    /**
     * Reloads the register view.
     */
    private function _reload(array $data = []): string
    {
        $data['stock_locations']      = $this->stock_location->get_allowed_locations('receivings');
        $data['show_stock_locations'] = count($data['stock_locations']) > 1;
        $data['stock_source']         = $this->transfer_lib->get_stock_source();
        $data['stock_destination']    = $this->transfer_lib->get_stock_destination();
        $data['cart']                 = $this->transfer_lib->get_cart();
        $data['total']                = $this->transfer_lib->get_total();
        $data['comment']              = $this->transfer_lib->get_comment();

        $employee_id      = $this->employee->get_logged_in_employee_info()->person_id;
        $employee_info    = $this->employee->get_info($employee_id);
        $data['employee'] = $employee_info->first_name . ' ' . $employee_info->last_name;

        return view('transfers/register', $data);
    }
}
