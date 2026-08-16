<?php

namespace App\Controllers;

use App\Libraries\Requisition_lib;
use App\Libraries\Token_lib;
use App\Models\Item;
use App\Models\Requisition;
use App\Models\Stock_location;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Requisitions controller
 *
 * A stock request from one location to another with a two-step approval
 * workflow: the source location approves, then an administrator approves and
 * the stock physically moves.
 */
class Requisitions extends Secure_Controller
{
    private Token_lib $token_lib;
    private Requisition_lib $requisition_lib;
    private Item $item;
    private Requisition $requisition;
    private Stock_location $stock_location;

    public function __construct()
    {
        parent::__construct('requisitions');

        $this->token_lib       = new Token_lib();
        $this->requisition_lib = new Requisition_lib();
        $this->item            = model(Item::class);
        $this->requisition     = model(Requisition::class);
        $this->stock_location  = model(Stock_location::class);
    }

    /**
     * Lists requisitions relevant to the logged in user's locations.
     */
    public function getIndex(): string
    {
        $locations            = array_keys($this->stock_location->get_allowed_locations('requisitions'));
        $data['requisitions'] = $this->requisition->get_all($locations)->getResultArray();

        return view('requisitions/manage', $data);
    }

    /**
     * New requisition register screen.
     */
    public function getNew(): string
    {
        return $this->_reload();
    }

    public function getDetail(int|string|null $requisition_id): string
    {
        $data = [];

        $requisition_info = $this->requisition->get_info((int) $requisition_id)->getRowArray();

        if ($requisition_info === null) {
            return redirect()->to('requisitions');
        }

        $data['requisition']           = $requisition_info;
        $data['items']                 = $this->requisition->get_requisition_items((int) $requisition_id)->getResultArray();
        $data['requisition']['status'] = (int) $requisition_info['status'];

        $allowed_locations          = array_map('intval', array_keys($this->stock_location->get_allowed_locations('requisitions')));
        $data['can_approve_source'] = in_array((int) $requisition_info['location_from'], $allowed_locations, true);
        $data['is_admin']           = (int) $this->employee->get_logged_in_employee_info()->person_id === 1;

        return view('requisitions/view', $data);
    }

    /**
     * Returns search suggestions for an item.
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
     * Changes the source location for the requisition.
     *
     * @noinspection PhpUnused
     */
    public function postChangeSource(): string
    {
        $stock_source = $this->request->getPost('stock_source', FILTER_SANITIZE_NUMBER_INT);

        if ($this->stock_location->exists($stock_source)) {
            $this->requisition_lib->set_stock_source($stock_source);
            $this->requisition_lib->refresh_cart_stock($stock_source);
        }

        return $this->_reload();
    }

    /**
     * Sets the requisition comment.
     *
     * @noinspection PhpUnused
     */
    public function postSetComment(): ResponseInterface
    {
        $this->requisition_lib->set_comment($this->request->getPost('comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Adds an item to the requisition cart.
     *
     * @noinspection PhpUnused
     */
    public function postAdd(): string
    {
        $data = [];

        $item_id_or_number = $this->request->getPost('item', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $quantity          = 1;
        $this->token_lib->parse_barcode($quantity, $price, $item_id_or_number);

        $item_location = $this->requisition_lib->get_stock_source();

        if (! $this->requisition_lib->add_item($item_id_or_number, $quantity, $item_location)) {
            $data['error'] = lang('Requisitions.unable_to_add_item');
        }

        return $this->_reload($data);
    }

    /**
     * Edits a line item in the current requisition.
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
            $this->requisition_lib->edit_item($line, $quantity);
        } else {
            $data['error'] = lang('Requisitions.error_editing_item');
        }

        return $this->_reload($data);
    }

    /**
     * Deletes a line item from the current requisition.
     *
     * @noinspection PhpUnused
     */
    public function getDeleteItem(int|string|null $line): string
    {
        $this->requisition_lib->delete_item($line);

        return $this->_reload();
    }

    /**
     * Submits the requisition request (status: PENDING).
     *
     * @noinspection PhpUnused
     */
    public function postSubmitRequest(): RedirectResponse|string
    {
        $data = [];

        $cart              = $this->requisition_lib->get_cart();
        $stock_source      = $this->requisition_lib->get_stock_source();
        $stock_destination = $this->requisition_lib->get_stock_destination();
        $comment           = $this->requisition_lib->get_comment();

        if ($stock_source === $stock_destination) {
            $data['error'] = lang('Requisitions.error_same_location');

            return $this->_reload($data);
        }

        $employee_id    = $this->employee->get_logged_in_employee_info()->person_id;
        $requisition_id = $this->requisition->save_value($cart, $employee_id, $stock_source, $stock_destination, $comment);

        if ($requisition_id === -1) {
            $data['error'] = lang('Requisitions.transaction_failed');

            return $this->_reload($data);
        }

        $this->requisition_lib->clear_all();

        return redirect()->to('requisitions');
    }

    /**
     * Step 1: the source location approves the request.
     *
     * @noinspection PhpUnused
     */
    public function postApproveSource(int|string|null $requisition_id): RedirectResponse|string
    {
        $requisition_id = (int) $requisition_id;
        $requisition    = $this->requisition->get_info($requisition_id)->getRowArray();

        if ($requisition !== null && (int) $requisition['status'] === Requisition::STATUS_PENDING) {
            $allowed_locations = array_map('intval', array_keys($this->stock_location->get_allowed_locations('requisitions')));

            if (in_array((int) $requisition['location_from'], $allowed_locations, true)) {
                $this->requisition->approve_source($requisition_id);
            }
        }

        return redirect()->to('requisitions');
    }

    /**
     * Step 2: the administrator approves the request and stock moves.
     *
     * @noinspection PhpUnused
     */
    public function postApprove(int|string|null $requisition_id): RedirectResponse|string
    {
        $employee_id = (int) $this->employee->get_logged_in_employee_info()->person_id;

        if ($employee_id === 1) {
            $this->requisition->approve((int) $requisition_id, $employee_id);
        }

        return redirect()->to('requisitions');
    }

    /**
     * Rejects a pending requisition.
     *
     * @noinspection PhpUnused
     */
    public function postReject(int|string|null $requisition_id): RedirectResponse|string
    {
        $employee_id = (int) $this->employee->get_logged_in_employee_info()->person_id;

        $this->requisition->reject((int) $requisition_id, $employee_id);

        return redirect()->to('requisitions');
    }

    /**
     * Reloads the new-requisition register view.
     */
    private function _reload(array $data = []): string
    {
        $data['stock_locations']   = $this->stock_location->get_allowed_locations('requisitions');
        $data['stock_source']      = $this->requisition_lib->get_stock_source();
        $data['stock_destination'] = $this->requisition_lib->get_stock_destination();
        $data['cart']              = $this->requisition_lib->get_cart();
        $data['comment']           = $this->requisition_lib->get_comment();

        return view('requisitions/register', $data);
    }
}
