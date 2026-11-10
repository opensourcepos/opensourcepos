<?php

namespace app\Libraries;

use App\Models\Customer;
use App\Models\Item_taxes;
use App\Models\Enums\Rounding_mode;
use App\Models\Sale;
use App\Models\Tax;
use App\Models\Tax_category;
use App\Models\Tax_code;
use App\Models\Tax_jurisdiction;
use App\Libraries\Sale_lib;
use Config\OSPOS;

/**
 * Tax library
 *
 * Library with utilities to manage taxes
 */
class Tax_lib
{
    public const TAX_TYPE_EXCLUDED = '1';    // TODO: These constants need to be moved to constants.php
    public const TAX_TYPE_INCLUDED = '0';
    private Sale_lib $sale_lib;
    private Customer $customer;
    private Item_taxes $item_taxes;
    private Sale $sale;
    private Tax $tax;
    private Tax_category $tax_category;
    private Tax_code $tax_code;
    private Tax_jurisdiction $tax_jurisdiction;
    private array $config;

    public function __construct()
    {
        $this->sale_lib = new Sale_lib();

        $this->customer = model(Customer::class);
        $this->item_taxes = model(Item_taxes::class);
        $this->sale = model(Sale::class);
        $this->tax = model(Tax::class);
        $this->tax_category = model(Tax_category::class);
        $this->tax_code = model(Tax_code::class);
        $this->tax_jurisdiction = model(Tax_jurisdiction::class);
        $this->config = config(OSPOS::class)->settings;
    }

    /**
     * @return array
     */
    public function get_tax_types(): array
    {
        return [
            Tax_lib::TAX_TYPE_EXCLUDED => lang('Taxes.tax_excluded'),
            Tax_lib::TAX_TYPE_INCLUDED => lang('Taxes.tax_included')
        ];
    }

    /**
     * Compute the tax basis and returns the tax amount
     */
    public function get_item_sales_tax(string $quantity, string $price, string $discount, int $discount_type, string $tax_percentage, int $rounding_code): string    // TODO: It appears this function is never called in the code.
    {
        $decimals = tax_decimals();

        // The tax basis should be returned to the currency scale
        $tax_basis = $this->sale_lib->get_item_total($quantity, $price, $discount, $discount_type, true);

        return $this->get_tax_for_amount($tax_basis, $tax_percentage, $rounding_code, $decimals);
    }

    /**
     * Computes the item level sales tax amount for a given tax basis
     */
    public function get_tax_for_amount(string $tax_basis, string $tax_percentage, int $rounding_mode, int $decimals): string
    {
        $tax_amount = bcmul($tax_basis, bcdiv($tax_percentage, '100'));

        return rounding_mode::round_number($rounding_mode, $tax_amount, $decimals);
    }

    /**
     * Compute taxes for all items in the cart
     */
    public function get_taxes(array &$cart, int $saleId = -1): array    // TODO: Replace -1 with constant.
    {
        $registerMode = $this->sale_lib->get_mode();
        $taxDecimals = tax_decimals();
        $customerId = $this->sale_lib->get_customer();
        $customerInfo = $this->customer->getInfo($customerId);
        $taxes = [];
        $itemTaxes = [];

        // Charge sales tax if customer is not selected (walk-in) or customer is flagged as taxable
        if ($customerId == -1 || $customerInfo->taxable) {    // TODO: Replace -1 with constant.
            foreach ($cart as $line => $item) {
                $taxed = false;

                if (!$this->config['use_destination_based_tax']) {
                    // Start of current Base System tax calculations

                    if ($saleId == -1) {    // TODO: Replace -1 with constant. Also, replace with ternary notation.
                        $taxInfo = $this->item_taxes->get_info($item['item_id']);
                    } else {
                        $taxInfo = $this->sale->get_sales_item_taxes($saleId, $item['item_id']);
                    }

                    $taxGroupSequence = 0;
                    $cascadeLevel = 0;    // TODO: This variable is set but never used.
                    $cascadeBasisLevel = 0;    // TODO: This variable is set but never used.

                    foreach ($taxInfo as $tax) {
                        // This computes tax for each line item and adds it to the tax type total
                        $taxBasis = $this->sale_lib->get_item_total($item['quantity'], $item['price'], $item['discount'], $item['discount_type'], true);
                        $taxAmount = '0.0';

                        if ($this->config['tax_included']) {
                            $taxType = Tax_lib::TAX_TYPE_INCLUDED;
                            $taxAmount = $this->get_included_tax($item['quantity'], $item['price'], $item['discount'], $item['discount_type'], $tax['percent'], $taxDecimals, Rounding_mode::HALF_UP);
                        } else {
                            $taxType = Tax_lib::TAX_TYPE_EXCLUDED;
                            $taxAmount = $this->get_tax_for_amount($taxBasis, $tax['percent'], Rounding_mode::HALF_UP, $taxDecimals);
                        }

                        if ($taxAmount <> 0) {
                            $taxGroupSequence++;
                            $this->update_taxes($taxes, $taxType, $tax['name'], $tax['percent'], $taxBasis, $taxAmount, $taxGroupSequence, Rounding_mode::HALF_UP, -1, $tax['name']);    // TODO: Replace -1 with constant.
                            $taxed = true;
                        }

                        $itemsTaxesDetail = [];
                        $itemsTaxesDetail['item_id'] = $item['item_id'];
                        $itemsTaxesDetail['line'] = $item['line'];
                        $itemsTaxesDetail['name'] = $tax['name'];
                        $itemsTaxesDetail['percent'] = $tax['percent'];
                        $itemsTaxesDetail['tax_type'] = $taxType;
                        $itemsTaxesDetail['rounding_code'] = Rounding_mode::HALF_UP;
                        $itemsTaxesDetail['cascade_sequence'] = 0;
                        $itemsTaxesDetail['item_tax_amount'] = $taxAmount;
                        $itemsTaxesDetail['sales_tax_code_id'] = null;
                        $itemsTaxesDetail['jurisdiction_id'] = null;
                        $itemsTaxesDetail['tax_category_id'] = null;

                        $itemTaxes[] = $itemsTaxesDetail;
                    }
                } else {
                    // Start of destination based tax calculations
                    if ($item['tax_category_id'] == null) {    // TODO: === ?
                        $item['tax_category_id'] = $this->config['default_tax_category'];
                    }

                    $taxed = $this->apply_destination_tax($item, $customerInfo->city, $customerInfo->state, $customerInfo->sales_tax_code_id, $registerMode, 0, $taxes, $itemTaxes, $item['line']);
                }

                if ($taxed) {    // TODO: Convert to ternary notation
                    $cart[$line]['taxed_flag'] = lang('Sales.taxed_ind');
                } else {
                    $cart[$line]['taxed_flag'] = lang('Sales.nontaxed_ind');
                }
            }
            $this->round_taxes($taxes);
        }

        $taxDetails = [];
        $taxDetails[0] = $taxes;
        $taxDetails[1] = $itemTaxes;

        return $taxDetails;
    }

    /**
     * @param string $quantity
     * @param string $price
     * @param string $discount_percentage
     * @param int $discount_type
     * @param string $tax_percentage
     * @param $tax_decimal
     * @param $rounding_code
     * @return string
     */
    public function get_included_tax(string $quantity, string $price, string $discount_percentage, int $discount_type, string $tax_percentage, $tax_decimal, $rounding_code): string    // TODO: $tax_decimal and $rounding_code are in the signature but never used in the function.
    {
        $item_total = $this->sale_lib->get_item_total($quantity, $price, $discount_percentage, $discount_type, true);
        $tax_fraction = bcdiv(bcadd('100', $tax_percentage), '100');
        $price_tax_excl = bcdiv($item_total, $tax_fraction);
        return bcsub($item_total, $price_tax_excl);
    }

    /**
     * Updates the sales_tax array which is later saved to the `sales_taxes` table and used for printing taxes on receipts and invoices
     */
    public function update_taxes(array &$taxes, string $tax_type, string $tax_group, string $tax_rate, string $tax_basis, string $item_tax_amount, int $tax_group_sequence, int $rounding_code, int $sale_id, string $name = '', ?int $tax_code_id = null, ?int $jurisdiction_id = null, ?int $tax_category_id = null): void
    {
        $tax_group_index = $this->clean('X' . (float)$tax_rate . '% ' . $tax_group);    // TODO: Not sure we should be casting to a float here.  The clean() function takes a string, so it just gets converted back to a string and there's risk of inaccuracies in the value displayed.

        if (!array_key_exists($tax_group_index, $taxes)) {
            $insertkey = $tax_group_index;    // TODO: this variable does not follow naming conventions.

            $tax = [
                $insertkey => [
                    'sale_id'           => $sale_id,
                    'tax_type'          => $tax_type,
                    'tax_group'         => $tax_group,
                    'sale_tax_basis'    => $tax_basis,
                    'sale_tax_amount'   => $item_tax_amount,
                    'print_sequence'    => $tax_group_sequence,
                    'name'              => $name,
                    'tax_rate'          => $tax_rate,
                    'sales_tax_code_id' => $tax_code_id,
                    'jurisdiction_id'   => $jurisdiction_id,
                    'tax_category_id'   => $tax_category_id,
                    'rounding_code'     => $rounding_code
                ]
            ];

            // Add to existing array
            $taxes += $tax;
        } else {
            // Important: the sales amounts are accumulated for the group at the maximum configurable scale value of 4
            // but the scale will in reality be the scale specified by the tax_decimal configuration value  used for sales_items_taxes
            $taxes[$tax_group_index]['sale_tax_basis'] = bcadd($taxes[$tax_group_index]['sale_tax_basis'], $tax_basis, 4);
            $taxes[$tax_group_index]['sale_tax_amount'] = bcadd($taxes[$tax_group_index]['sale_tax_amount'], $item_tax_amount, 4);
        }
    }

    /**
     * If invoice taxing (as opposed to invoice_item_taxing) rules apply then recalculate the sales tax after tax group totals are final
     * This is currently used ONLY for the original sales tax migration.
     */
    public function apply_invoice_taxing(array &$taxes): void
    {
        if (!empty($taxes)) {
            $sort = [];
            foreach ($taxes as $k => $v) {
                $sort['print_sequence'][$k] = $v['print_sequence'];
            }
            array_multisort($sort['print_sequence'], SORT_ASC, $taxes);
        }

        $decimals = totals_decimals();

        foreach ($taxes as $row_number => $tax) {
            $taxes[$row_number]['sale_tax_amount'] = $this->get_tax_for_amount($tax['sale_tax_basis'], $tax['tax_rate'], $tax['rounding_code'], $decimals);
        }
    }

    /**
     * Apply rounding rules to the accumulated sales tax amounts
     */
    public function round_taxes(array &$taxes): void
    {
        if (!empty($taxes)) {
            $sort = [];
            foreach ($taxes as $k => $v) {
                $sort['print_sequence'][$k] = $v['print_sequence'];
            }
            array_multisort($sort['print_sequence'], SORT_ASC, $taxes);
        }

        // If tax included then round decimal to tax decimals, otherwise round it to currency_decimals
        if ($this->config['tax_included']) {    // TODO: Convert to ternary notation
            $decimals = tax_decimals();
        } else {
            $decimals = totals_decimals();
        }

        foreach ($taxes as $rowNumber => $salesTax) {
            $taxAmount = (float) $salesTax['sale_tax_amount'];
            $roundingCode = $salesTax['rounding_code'];

            $taxes[$rowNumber]['sale_tax_amount'] = Rounding_mode::round_number($roundingCode, $taxAmount, $decimals);
            if (isset($salesTax['cash_tax_amount'])) {
                $taxes[$rowNumber]['cash_tax_amount'] = Rounding_mode::round_number($roundingCode, (float) $salesTax['cash_tax_amount'], $decimals);
            }
        }
    }

    /**
     * Determine the applicable tax code and then determine the tax amount to be applied.
     * If a tax amount was identified then accumulate into the sales_taxes array
     */
    public function apply_destination_tax(array &$item, ?string $city, ?string $state, ?int $salesTaxCodeId, string $registerMode, int $saleId, array &$taxes, array &$itemTaxes, int $line): bool
    {
        $taxed = false;

        $taxCodeId = $this->get_applicable_tax_code($registerMode, $city, $state, $salesTaxCodeId);

        // If tax code cannot be determined or the price is zero then skip this item
        if ($taxCodeId != null && $taxCodeId != -1 && $item['price'] != 0) {    // TODO: Replace -1 with constant. Also === ?
            $taxDecimals = tax_decimals();

            $taxDefinition = $this->tax->get_taxes($taxCodeId, $item['tax_category_id']);

            // The tax basis should be returned to the currency scale
            $taxBasis = $this->sale_lib->get_item_total($item['quantity'], $item['price'], $item['discount'], $item['discount_type'], true);

            $row = 0;    // TODO: This variable is set but never used.

            $lastCascadeSequence = 0;
            $cascadeTaxAmount = '0.0';

            foreach ($taxDefinition as $tax) {
                $cascadeSequence = $tax['cascade_sequence'];
                if ($cascadeSequence != $lastCascadeSequence) {
                    $lastCascadeSequence = $cascadeSequence;
                    $taxBasis = bcadd($taxBasis, $cascadeTaxAmount);
                }

                $taxRate = $tax['tax_rate'];
                $roundingCode = $tax['tax_rounding_code'];

                // This computes tax for each line item and adds it to the tax type total
                $taxType = $tax['tax_type'];

                if ($taxType == Tax_lib::TAX_TYPE_INCLUDED) {
                    $taxAmount = $this->get_included_tax($item['quantity'], $item['price'], $item['discount'], $item['discount_type'], $taxRate, $taxDecimals, $roundingCode);
                } else {
                    $taxAmount = $this->get_tax_for_amount($taxBasis, $taxRate, $roundingCode, $taxDecimals);
                    $cascadeTaxAmount = bcadd($cascadeTaxAmount, $taxAmount);
                }

                if ($taxAmount != 0) {
                    $taxed = true;
                    $this->update_taxes($taxes, $taxType, $tax['tax_group'], $taxRate, $taxBasis, $taxAmount, $tax['tax_group_sequence'], $roundingCode, $saleId, $tax['tax_group'], $taxCodeId, $tax['rate_jurisdiction_id'], $item['tax_category_id']);
                }

                $itemTaxesDetail = [];
                $itemTaxesDetail['line'] = $line;
                $itemTaxesDetail['item_id'] = $item['item_id'];
                $itemTaxesDetail['name'] = $tax['tax_group'];
                $itemTaxesDetail['percent'] = $tax['tax_rate'];
                $itemTaxesDetail['tax_type'] = $taxType;
                $itemTaxesDetail['rounding_code'] = $roundingCode;
                $itemTaxesDetail['cascade_sequence'] = $cascadeSequence;
                $itemTaxesDetail['item_tax_amount'] = $taxAmount;
                $itemTaxesDetail['sales_tax_code_id'] = $taxCodeId;
                $itemTaxesDetail['jurisdiction_id'] = $tax['rate_jurisdiction_id'];
                $itemTaxesDetail['tax_category_id'] = $tax['rate_tax_category_id'];
                $itemTaxesDetail['tax_group_sequence'] = $tax['tax_group_sequence'];

                $itemTaxes[] = $itemTaxesDetail;
            }
        }

        return $taxed;
    }

    /**
     * @param string $registerMode
     * @param string|null $city
     * @param string|null $state
     * @param int|null $salesTaxCodeId
     * @return int|null
     */
    public function get_applicable_tax_code(string $registerMode, ?string $city, ?string $state, ?int $salesTaxCodeId): ?int
    {
        if ($registerMode == 'sale') {
            $salesTaxCodeId = $this->config['default_tax_code']; // Overrides customer assigned code
        } else {
            if ($salesTaxCodeId == null || $salesTaxCodeId == 0) {
                $salesTaxCodeId = $this->tax_code->get_sales_tax_code($city ?? '', $state ?? '');

                if ($salesTaxCodeId == null || $salesTaxCodeId == 0) {
                    $salesTaxCodeId = $this->config['default_tax_code']; // Overrides customer assigned code
                }
            }
        }

        return ($salesTaxCodeId === '' || $salesTaxCodeId === null) ? null : (int) $salesTaxCodeId;
    }

    /**
     * @param string $string
     * @return string
     */
    public function clean(string $string): string    // TODO: $string is not a good choice of variable name here.
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }

    /**
     * @return array
     */
    public function get_tax_code_options(): array
    {
        $tax_codes = $this->tax_code->get_all()->getResultArray();
        $tax_code_options = [];
        $tax_code_options[''] = '';

        foreach ($tax_codes as $tax_code) {
            $a = $tax_code['tax_code_id'];    // TODO: Need to refactor single-letter variables into meaningful variable names
            $b = $tax_code['tax_code_name'];
            $tax_code_options[$a] = $b;
        }

        return $tax_code_options;
    }

    /**
     * @return array
     */
    public function get_tax_jurisdiction_options(): array
    {
        $tax_jurisdictions = $this->tax_jurisdiction->get_all()->getResultArray();
        $tax_jurisdiction_options = [];
        $tax_jurisdiction_options[0] = '';

        foreach ($tax_jurisdictions as $tax_jurisdiction) {    // TODO: Need to refactor single-letter variables into meaningful variable names
            $a = $tax_jurisdiction['jurisdiction_id'];
            $b = $tax_jurisdiction['jurisdiction_name'];
            $tax_jurisdiction_options[$a] = $b;
        }

        return $tax_jurisdiction_options;
    }

    /**
     * @return array
     */
    public function get_tax_category_options(): array
    {
        $tax_categories = $this->tax_category->get_all()->getResultArray();
        $tax_category_options = [];
        $tax_category_options[0] = '';

        foreach ($tax_categories as $tax_category) {
            $a = $tax_category['tax_category_id'];    // TODO: Need to refactor single-letter variables into meaningful variable names
            $b = $tax_category['tax_category'];

            $tax_category_options[$a] = $b;
        }

        return $tax_category_options;
    }

    /**
     * @param string $selectedTaxType
     * @return string
     */
    public function get_tax_type_options(string $selectedTaxType): string
    {
        $selected = 'selected="selected" ';

        $s1 = '';    // TODO: Need to refactor short variables into meaningful variable names
        $s2 = '';

        if ($selectedTaxType === Tax_lib::TAX_TYPE_EXCLUDED) {
            $s1 = $selected;
        } elseif ($selectedTaxType === Tax_lib::TAX_TYPE_INCLUDED) {
            $s2 = $selected;
        }

        return '<option value="' . Tax_lib::TAX_TYPE_EXCLUDED . '" ' . $s1 . '> ' . lang('Taxes.sales_tax') . '</option>'
            . '<option value="' . Tax_lib::TAX_TYPE_INCLUDED . '" ' . $s2 . '> ' . lang('Taxes.vat_tax') . '</option>';
    }
}
