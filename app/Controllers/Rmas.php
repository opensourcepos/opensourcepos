<?php

namespace App\Controllers;

use App\Libraries\Rma_lib;
use App\Libraries\Token_lib;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Rma;
use App\Models\Sale;
use App\Models\Stock_location;
use App\Models\Supplier;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Rmas controller
 *
 * Return Merchandise Authorization register. Two unit types:
 *   - STOCK  units: defective stock, quantity deducted on creation,
 *     added back on a replacement/repair resolution.
 *   - CLIENT units: already sold to a customer, no quantity change.
 */
class Rmas extends Secure_Controller
{
    private Token_lib $token_lib;
    private Rma_lib $rma_lib;
    private Item $item;
    private Rma $rma;
    private Sale $sale;
    private Stock_location $stock_location;
    private Supplier $supplier;
    private Customer $customer;

    public function __construct()
    {
        parent::__construct('rmas');

        $this->token_lib      = new Token_lib();
        $this->rma_lib        = new Rma_lib();
        $this->item           = model(Item::class);
        $this->rma            = model(Rma::class);
        $this->sale           = model(Sale::class);
        $this->stock_location = model(Stock_location::class);
        $this->supplier       = model(Supplier::class);
        $this->customer       = model(Customer::class);
    }

    /**
     * Lists RMAs relevant to the logged in user's locations.
     */
    public function getIndex(): string
    {
        $locations    = array_keys($this->stock_location->get_allowed_locations('rmas'));
        $data['rmas'] = $this->rma->get_all($locations)->getResultArray();

        return view('rmas/manage', $data);
    }

    /**
     * New RMA register screen.
     */
    public function getNew(): string
    {
        return $this->_reload();
    }

    public function getDetail(int|string|null $rma_id): string
    {
        $data = [];

        $rma_info = $this->rma->get_info((int) $rma_id)->getRowArray();

        if ($rma_info === null) {
            return redirect()->to('rmas');
        }

        $data['rma']                       = $rma_info;
        $data['items']                     = $this->rma->get_rma_items((int) $rma_id)->getResultArray();
        $data['rma']['resolution_options'] = $this->rma->resolution_options((int) $rma_info['rma_type']);

        return view('rmas/view', $data);
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
     * Sets the RMA unit type (stock or client).
     *
     * @noinspection PhpUnused
     */
    public function postChangeType(): string
    {
        $rma_type = (int) $this->request->getPost('rma_type', FILTER_SANITIZE_NUMBER_INT);

        if ($rma_type === Rma::TYPE_CLIENT) {
            $this->rma_lib->set_rma_type(Rma::TYPE_CLIENT);
        } else {
            $this->rma_lib->set_rma_type(Rma::TYPE_STOCK);
            $this->rma_lib->set_sale_id(null);
        }

        return $this->_reload();
    }

    /**
     * Changes the location for the RMA.
     *
     * @noinspection PhpUnused
     */
    public function postChangeLocation(): string
    {
        $location_id = $this->request->getPost('location', FILTER_SANITIZE_NUMBER_INT);

        if ($this->stock_location->exists($location_id)) {
            $this->rma_lib->set_location($location_id);
            $this->rma_lib->refresh_cart_stock($location_id);
        }

        return $this->_reload();
    }

    /**
     * Sets the supplier for a stock-unit RMA.
     *
     * @noinspection PhpUnused
     */
    public function postSelectSupplier(): string
    {
        $supplier_id = $this->request->getPost('supplier', FILTER_SANITIZE_NUMBER_INT);
        $this->rma_lib->set_supplier_id($supplier_id === '' || $supplier_id === null ? null : (int) $supplier_id);

        return $this->_reload();
    }

    /**
     * Sets the customer for a client-unit RMA.
     *
     * @noinspection PhpUnused
     */
    public function postSelectCustomer(): string
    {
        $customer_id = $this->request->getPost('customer', FILTER_SANITIZE_NUMBER_INT);
        $this->rma_lib->set_customer_id($customer_id === '' || $customer_id === null ? null : (int) $customer_id);

        return $this->_reload();
    }

    /**
     * Sets the RMA comment.
     *
     * @noinspection PhpUnused
     */
    public function postSetComment(): ResponseInterface
    {
        $this->rma_lib->set_comment($this->request->getPost('comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Adds an item to the RMA cart.
     *
     * @noinspection PhpUnused
     */
    public function postAdd(): string
    {
        $data = [];

        $item_id_or_number = $this->request->getPost('item', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $quantity          = 1;
        $this->token_lib->parse_barcode($quantity, $price, $item_id_or_number);

        // Client units: allow scanning a completed sale/receipt. Accepts
        // "POS 70", a bare sale id that exists (e.g. "70"), or an invoice
        // number, then loads its items for the picker.
        if ($this->rma_lib->get_rma_type() === Rma::TYPE_CLIENT) {
            $sale_id = null;

            if ($this->sale->isValidReceipt($item_id_or_number)) {
                // isValidReceipt only guarantees some receipt form; normalize
                // "POS 70" / "<invoice>" into the numeric sale id.
                $pieces = explode(' ', trim($item_id_or_number));

                if (count($pieces) === 2 && strtoupper($pieces[0]) === 'POS' && ctype_digit($pieces[1])) {
                    $sale_id = (int) $pieces[1];
                } else {
                    $invoice_sale = $this->sale->get_sale_by_invoice_number($item_id_or_number)->getRow();

                    if ($invoice_sale !== null) {
                        $sale_id = (int) $invoice_sale->sale_id;
                    }
                }
            } elseif (ctype_digit(trim($item_id_or_number)) && $this->sale->exists((int) trim($item_id_or_number))) {
                // A bare, pasted sale id.
                $sale_id = (int) trim($item_id_or_number);
            }

            if ($sale_id !== null) {
                $this->rma_lib->set_sale_id($sale_id);

                // Point the register at the sale's location so the returned
                // units are recorded under the correct location.
                $sale_items = $this->sale->get_sale_items_ordered($sale_id)->getResultArray();

                if (count($sale_items) > 0 && ! empty($sale_items[0]['item_location'])) {
                    $this->rma_lib->set_location((int) $sale_items[0]['item_location']);
                    $this->rma_lib->refresh_cart_stock((int) $sale_items[0]['item_location']);
                }

                return $this->_reload($data);
            }
        }

        $item_location = $this->rma_lib->get_location();

        if (! $this->rma_lib->add_item($item_id_or_number, $quantity, $item_location)) {
            $data['error'] = lang('Rmas.unable_to_add_item');
        }

        return $this->_reload($data);
    }

    /**
     * Adds a line item of a loaded client-unit sale to the RMA cart using the
     * quantity purchased on that sale.
     *
     * @noinspection PhpUnused
     */
    public function getAddSaleItem(int|string|null $line): string
    {
        $data    = [];
        $sale_id = $this->rma_lib->get_sale_id();

        if ($sale_id === null) {
            $data['error'] = lang('Rmas.no_sale_loaded');

            return $this->_reload($data);
        }

        $sale_items = $this->sale->get_sale_items_ordered($sale_id)->getResultArray();
        $item_found = false;

        foreach ($sale_items as $sale_item) {
            if ((int) $sale_item['line'] === (int) $line) {
                $item_location = (int) $sale_item['item_location'];
                $quantity      = (float) $sale_item['quantity_purchased'];

                if (! $this->rma_lib->add_item((string) $sale_item['item_id'], $quantity, $item_location)) {
                    $data['error'] = lang('Rmas.unable_to_add_item');
                } else {
                    $item_found = true;
                }

                break;
            }
        }

        if (! $item_found) {
            $data['error'] = lang('Rmas.unable_to_add_item');
        }

        return $this->_reload($data);
    }

    /**
     * Edits a line item in the current RMA.
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
        $issue    = $this->request->getPost('issue', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
        $serial   = $this->request->getPost('serial_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';

        if ($this->validate($validation_rule)) {
            $this->rma_lib->edit_item($line, $quantity, $issue, $serial);
        } else {
            $data['error'] = lang('Rmas.error_editing_item');
        }

        return $this->_reload($data);
    }

    /**
     * Deletes a line item from the current RMA.
     *
     * @noinspection PhpUnused
     */
    public function getDeleteItem(int|string|null $line): string
    {
        $this->rma_lib->delete_item($line);

        return $this->_reload();
    }

    /**
     * Submits the RMA. STOCK units deduct the quantity from the location.
     *
     * @noinspection PhpUnused
     */
    public function postSubmit(): RedirectResponse|string
    {
        $data = [];

        $cart        = $this->rma_lib->get_cart();
        $rma_type    = $this->rma_lib->get_rma_type();
        $location_id = $this->rma_lib->get_location();
        $comment     = $this->rma_lib->get_comment();
        $supplier_id = null;
        $customer_id = null;
        $sale_id     = null;

        if ($rma_type === Rma::TYPE_STOCK) {
            $supplier_id = $this->rma_lib->get_supplier_id();
        } else {
            $customer_id = $this->rma_lib->get_customer_id();
            $sale_id     = $this->rma_lib->get_sale_id();
        }

        $employee_id = $this->employee->get_logged_in_employee_info()->person_id;
        $rma_id      = $this->rma->save_value($cart, $employee_id, $rma_type, $location_id, $supplier_id, $customer_id, $sale_id, $comment);

        if ($rma_id === -1) {
            $data['error'] = lang('Rmas.transaction_failed');

            return $this->_reload($data);
        }

        $this->rma_lib->clear_all();

        return redirect()->to('rmas');
    }

    /**
     * Resolves the RMA with the given resolution.
     *
     * @noinspection PhpUnused
     */
    public function postResolve(int|string|null $rma_id): RedirectResponse|string
    {
        $rma_id     = (int) $rma_id;
        $rma_info   = $this->rma->get_info($rma_id)->getRowArray();
        $resolution = $this->request->getPost('resolution', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($rma_info !== null && $rma_info['resolution'] === null && array_key_exists($resolution, $this->rma->resolution_options((int) $rma_info['rma_type']))) {
            $employee_id = $this->employee->get_logged_in_employee_info()->person_id;
            $this->rma->resolve($rma_id, $resolution, (int) $employee_id);
        }

        return redirect()->to('rmas');
    }

    /**
     * Deletes an RMA that has not been resolved yet, restoring any deducted
     * stock quantities for stock units.
     *
     * @noinspection PhpUnused
     */
    public function postDeleteRMA(int|string|null $rma_id): RedirectResponse|string
    {
        $rma_id   = (int) $rma_id;
        $rma_info = $this->rma->get_info($rma_id)->getRowArray();

        if ($rma_info !== null && $rma_info['resolution'] === null) {
            $this->rma->delete_value($rma_id);
        }

        return redirect()->to('rmas');
    }

    /**
     * Reloads the new-RMA register view.
     */
    private function _reload(array $data = []): string
    {
        $supplier_id = $this->rma_lib->get_supplier_id();
        $customer_id = $this->rma_lib->get_customer_id();

        $data['rma_type']        = $this->rma_lib->get_rma_type();
        $data['stock_locations'] = $this->stock_location->get_allowed_locations('rmas');
        $data['location']        = $this->rma_lib->get_location();
        $data['supplier_id']     = $supplier_id;
        $data['customer_id']     = $customer_id;
        $data['suppliers']       = $this->_get_supplier_options();
        $data['customers']       = $this->_get_customer_options();
        $data['sale_id']         = $this->rma_lib->get_sale_id();
        $data['sale_items']      = $data['sale_id'] !== null ? $this->sale->get_sale_items_ordered($data['sale_id'])->getResultArray() : [];
        $data['cart']            = $this->rma_lib->get_cart();
        $data['comment']         = $this->rma_lib->get_comment();

        return view('rmas/register', $data);
    }

    /**
     * Builds the supplier dropdown options (id => company name).
     */
    private function _get_supplier_options(): array
    {
        $options         = ['' => lang('Common.none_selected_text')];
        $supplier_result = $this->supplier->get_all();

        foreach ($supplier_result->getResultArray() as $supplier) {
            $options[$supplier['person_id']] = $supplier['company_name'];
        }

        return $options;
    }

    /**
     * Builds the customer dropdown options (id => full name).
     */
    private function _get_customer_options(): array
    {
        $options         = ['' => lang('Common.none_selected_text')];
        $customer_result = $this->customer->get_all();

        foreach ($customer_result->getResultArray() as $customer) {
            $name = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));

            if ($name !== '') {
                $options[$customer['person_id']] = $name;
            }
        }

        return $options;
    }
}
