<?php

namespace App\Plugins\WhatsAppPlugin\Libraries;

use App\Libraries\Barcode_lib;
use App\Libraries\Email_lib;
use App\Libraries\Sale_lib;
use App\Libraries\Tax_lib;
use App\Libraries\Token_lib;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Sale;
use App\Models\Stock_location;
use App\Models\Tokens\Token_customer;
use App\Models\Tokens\Token_invoice_count;
use App\Models\Tokens\Token_invoice_sequence;
use Config\Database;
use Config\OSPOS;
use Config\Services;

/**
 * Assembles a sale document (invoice / quote / work order / receipt) as a PDF so
 * it can be delivered over WhatsApp.
 *
 * buildData() mirrors the subset of `Sales::_load_sale_data()` that the
 * `sales/{type}_email` views consume, because that method is private. Every call
 * goes through an existing public API, so no totals or tax logic is reimplemented,
 * but this class becomes redundant once core exposes a reusable accessor for sale
 * document data.
 */
class SaleDocument
{
    private array $config;

    public function __construct()
    {
        $this->config = config(OSPOS::class)->settings;
    }

    /**
     * Returns the customer's phone number for a sale, or '' when the sale has no
     * customer or the customer has no number.
     *
     * Deliberately cheap and side-effect free: this runs while a sale document view
     * is rendering, so it must not touch the register's cart session.
     *
     * Reads customer_id straight from `sales` rather than via Sale::getInfo(),
     * which builds a temp table and inner-joins sales_items/payments to compute
     * totals. That would be wasted work for a single column here, and it returns
     * no row at all for a sale with no line items.
     */
    public function customerPhone(int $saleId): string
    {
        $sale = Database::connect()
            ->table('sales')
            ->select('customer_id')
            ->where('sale_id', $saleId)
            ->get()
            ->getRow();

        $customerId = (int) ($sale->customer_id ?? 0);

        if ($customerId === 0) {
            return '';
        }

        return (string) (model(Customer::class)->getInfo($customerId)->phone_number ?? '');
    }

    /**
     * Renders a sale document to a temporary PDF file. $type is one of
     * invoice|quote|work_order|receipt, already validated by the caller, which
     * also owns the returned file and must unlink it.
     *
     * @return array{path: string, display_name: string, caption: string, phone: string, person_id: int|null}|null
     *                                                                                                             Null when the PDF could not be generated.
     */
    public function renderPdf(int $saleId, string $type): ?array
    {
        $data   = $this->buildData($saleId);
        $number = $this->documentNumber($data, $type);

        $emailLib         = new Email_lib();
        $data['mimetype'] = $emailLib->getLogoMimeType();
        $data['img_tag']  = $emailLib->buildLogoImgTag();

        // Same view core uses for the emailed attachment.
        $html = Services::renderer()->setData($data)->render("sales/{$type}_email", $data);

        helper(['dompdf', 'file']);

        // A unique temp name avoids a TOCTOU race with core's getSendPdf(), which
        // writes to a deterministic path. The recipient still sees a friendly name.
        $path = tempnam(sys_get_temp_dir(), 'wa_');

        if ($path === false || file_put_contents($path, create_pdf($html)) === false) {
            if ($path !== false && is_file($path)) {
                unlink($path);
            }

            return null;
        }

        return [
            'path'         => $path,
            'display_name' => lang('Sales.' . $type) . '-' . str_replace('/', '-', $number) . '.pdf',
            'caption'      => $this->buildCaption($data, $number),
            'phone'        => (string) ($data['customer_phone'] ?? ''),
            'person_id'    => isset($data['customer_id']) ? (int) $data['customer_id'] : null,
        ];
    }

    /**
     * Identifier used in the filename and the caption's invoice-sequence token.
     *
     * Receipts have no number of their own — the receipt view identifies the sale
     * by its POS id — and invoice/quote/work-order numbers are null on a sale that
     * was never issued as that document type. Both fall back to the POS id so the
     * filename is never left empty.
     */
    private function documentNumber(array $data, string $type): string
    {
        $number = (string) ($data[$type . '_number'] ?? '');

        return $number !== '' ? $number : (string) $data['sale_id'];
    }

    /**
     * Renders the configured invoice message with the sale's tokens substituted.
     */
    private function buildCaption(array $data, string $number): string
    {
        $tokens = [
            new Token_invoice_sequence($number),
            new Token_invoice_count('POS ' . $data['sale_id_num']),
            new Token_customer($data),
        ];

        return (new Token_lib())->render((string) ($this->config['invoice_email_message'] ?? ''), $tokens);
    }

    /**
     * Builds the view data for a sale document.
     *
     * Like core, this loads the sale into the register cart to compute totals;
     * callers are expected to clear it afterwards via clearCart().
     */
    private function buildData(int $saleId): array
    {
        $saleModel = model(Sale::class);
        $saleLib   = new Sale_lib();
        $taxLib    = new Tax_lib();

        $saleLib->clear_all();

        $data = ['cash_rounding' => $saleLib->reset_cash_rounding()];

        $saleInfo = $saleModel->getInfo($saleId)->getRowArray();
        $saleLib->copy_entire_sale($saleId);

        $data['cart']                  = $saleLib->get_cart();
        $data['payments']              = $saleLib->getPayments();
        $data['selected_payment_type'] = $saleLib->get_payment_type();

        $taxDetails                   = $taxLib->get_taxes($data['cart'], $saleId);
        $data['taxes']                = $saleModel->get_sales_taxes($saleId);
        $data['discount']             = $saleLib->get_discount();
        $data['transaction_time']     = to_datetime(strtotime($saleInfo['sale_time']));
        $data['transaction_date']     = to_date(strtotime($saleInfo['sale_time']));
        $data['show_stock_locations'] = model(Stock_location::class)->show_locations('sales');
        $data['include_hsn']          = (bool) $this->config['include_hsn'];

        $totals  = $saleLib->get_totals($taxDetails[0]);
        $session = session();
        $session->set('cash_adjustment_amount', $totals['cash_adjustment_amount']);

        $data['subtotal']             = $totals['subtotal'];
        $data['payments_total']       = $totals['payment_total'];
        $data['payments_cover_total'] = $totals['payments_cover_total'];
        $data['cash_mode']            = $session->get('cash_mode');
        $data['prediscount_subtotal'] = $totals['prediscount_subtotal'];
        $data['cash_total']           = $totals['cash_total'];
        $data['non_cash_total']       = $totals['total'];
        $data['cash_amount_due']      = $totals['cash_amount_due'];
        $data['non_cash_amount_due']  = $totals['amount_due'];

        if ($data['cash_mode'] && ($data['selected_payment_type'] === lang('Sales.cash') || $data['payments_total'] > 0)) {
            $data['total']      = $totals['cash_total'];
            $data['amount_due'] = $totals['cash_amount_due'];
        } else {
            $data['total']      = $totals['total'];
            $data['amount_due'] = $totals['amount_due'];
        }

        $data['amount_change'] = $data['amount_due'] * -1;

        $employeeInfo     = model(Employee::class)->getInfo($saleLib->get_employee());
        $data['employee'] = $employeeInfo->first_name . ' ' . mb_substr($employeeInfo->last_name, 0, 1);

        $this->loadCustomerData($saleLib->get_customer(), $data);

        $data['sale_id_num']    = $saleId;
        $data['sale_id']        = 'POS ' . $saleId;
        $data['comments']       = $saleInfo['comment'];
        $data['invoice_number'] = $saleInfo['invoice_number'];
        $data['quote_number']   = $saleInfo['quote_number'];
        $data['sale_status']    = $saleInfo['sale_status'];

        // Sale::getInfo() does not select work_order_number, but the
        // work_order_email view requires it, so read it from the model directly.
        $data['work_order_number'] = $saleModel->get_work_order_number($saleId);

        $data['company_info'] = implode("\n", [$this->config['address'], $this->config['phone']]);

        if ($this->config['account_number']) {
            $data['company_info'] .= "\n" . lang('Sales.account_number') . ': ' . $this->config['account_number'];
        }

        if ($this->config['tax_id'] !== '') {
            $data['company_info'] .= "\n" . lang('Sales.tax_id') . ': ' . $this->config['tax_id'];
        }

        $data['barcode']           = (new Barcode_lib())->generate_receipt_barcode($data['sale_id']);
        $data['print_after_sale']  = false;
        $data['price_work_orders'] = false;
        $data['config']            = $this->config;

        return $data;
    }

    /**
     * Mirrors the customer half of `Sales::_load_customer_data()`, plus the phone
     * number the WhatsApp send needs (core's version does not expose it).
     */
    private function loadCustomerData(int $customerId, array &$data): void
    {
        if ($customerId === NEW_ENTRY) {
            return;
        }

        $customerInfo = model(Customer::class)->getInfo($customerId);

        $data['customer_id'] = $customerId;
        $data['customer']    = ! empty($customerInfo->company_name)
            ? $customerInfo->company_name
            : $customerInfo->first_name . ' ' . $customerInfo->last_name;
        $data['first_name']        = $customerInfo->first_name;
        $data['last_name']         = $customerInfo->last_name;
        $data['customer_email']    = $customerInfo->email;
        $data['customer_phone']    = $customerInfo->phone_number;
        $data['customer_address']  = $customerInfo->address_1;
        $data['customer_location'] = ! empty($customerInfo->zip) || ! empty($customerInfo->city)
            ? $customerInfo->zip . ' ' . $customerInfo->city . "\n" . $customerInfo->state
            : '';
        $data['customer_account_number'] = $customerInfo->account_number;
        $data['customer_discount']       = $customerInfo->discount;
        $data['customer_discount_type']  = $customerInfo->discount_type;

        $data['customer_info'] = implode("\n", [
            $data['customer'],
            $data['customer_address'],
            $data['customer_location'],
        ]);

        if ($data['customer_account_number']) {
            $data['customer_info'] .= "\n" . lang('Sales.account_number') . ': ' . $data['customer_account_number'];
        }

        if ($customerInfo->tax_id !== '') {
            $data['customer_info'] .= "\n" . lang('Sales.tax_id') . ': ' . $customerInfo->tax_id;
        }

        $data['tax_id'] = $customerInfo->tax_id;
    }

    /**
     * Clears the register cart, matching what core's send endpoints do once the
     * document has been delivered.
     */
    public function clearCart(): void
    {
        (new Sale_lib())->clear_all();
    }
}
