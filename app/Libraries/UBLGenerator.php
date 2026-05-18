<?php

namespace App\Libraries;

use DateTime;
use NumNum\UBL\AccountingParty;
use NumNum\UBL\Address;
use NumNum\UBL\AllowanceCharge;
use NumNum\UBL\Contact;
use NumNum\UBL\Country;
use NumNum\UBL\Generator;
use NumNum\UBL\Invoice;
use NumNum\UBL\InvoiceLine;
use NumNum\UBL\Item;
use NumNum\UBL\LegalMonetaryTotal;
use NumNum\UBL\Party;
use NumNum\UBL\PartyTaxScheme;
use NumNum\UBL\Price;
use NumNum\UBL\TaxCategory;
use NumNum\UBL\TaxScheme;
use NumNum\UBL\TaxSubTotal;
use NumNum\UBL\TaxTotal;
use NumNum\UBL\UnitCode;

helper(['country']);

class UBLGenerator
{
    /**
     * Generate UBL invoice XML from sale data
     *
     * @param array $saleData Sale data from _load_sale_data()
     *
     * @return string UBL XML string
     */
    public function generateUblInvoice(array $saleData): string
    {
        $taxScheme     = (new TaxScheme())->setId('VAT');
        $isTaxIncluded = ! empty($saleData['tax_included']);

        $supplierParty = $this->buildSupplierParty($saleData, $taxScheme);
        $customerParty = $this->buildCustomerParty($saleData['customer_object'] ?? null, $taxScheme);
        $invoiceLines  = $this->buildInvoiceLines($saleData, $taxScheme, $isTaxIncluded);
        $taxTotal      = $this->buildTaxTotal($saleData['taxes'] ?? [], $taxScheme);
        $monetaryTotal = $this->buildMonetaryTotal($saleData);

        $invoice = (new Invoice())
            ->setUBLVersionId('2.1')
            ->setCustomizationId('urn:cen.eu:en16931:2017')
            ->setProfileId('urn:fdc:peppol.eu:2017:poacc:billing:01:1.0')
            ->setId($saleData['invoice_number'] ?? '')
            ->setIssueDate(new DateTime($saleData['transaction_date'] ?? 'now'))
            ->setInvoiceTypeCode(380)
            ->setAccountingSupplierParty($supplierParty)
            ->setAccountingCustomerParty($customerParty)
            ->setInvoiceLines($invoiceLines)
            ->setTaxTotal($taxTotal)
            ->setLegalMonetaryTotal($monetaryTotal);

        // Set currency if available
        if (! empty($saleData['currency_code'])) {
            Generator::$currencyID = $saleData['currency_code'];
        }

        $generator = new Generator();

        return $generator->invoice($invoice);
    }

    /**
     * Build supplier (seller) party
     */
    protected function buildSupplierParty(array $saleData, TaxScheme $taxScheme): AccountingParty
    {
        $config = $saleData['config'] ?? [];

        $addressParts = $this->parseAddress($config['address'] ?? '');
        $countryCode  = getCountryCode($config['country'] ?? '');

        $country = (new Country())->setIdentificationCode($countryCode);
        $address = (new Address())
            ->setStreetName($addressParts['street'] ?? '')
            ->setBuildingNumber($addressParts['number'] ?? '')
            ->setCityName($addressParts['city'] ?? '')
            ->setPostalZone($addressParts['zip'] ?? '')
            ->setCountrySubentity($config['state'] ?? '')
            ->setCountry($country);

        $party = (new Party())
            ->setName($config['company'] ?? '')
            ->setPostalAddress($address);

        $partyTaxScheme = null;
        if (! empty($config['account_number'])) {
            $partyTaxScheme = (new PartyTaxScheme())
                ->setCompanyId($config['account_number'])
                ->setTaxScheme($taxScheme);
            $party->setPartyTaxScheme($partyTaxScheme);
        } elseif (! empty($config['tax_id'])) {
            // Use tax_id if account_number is not set
            $partyTaxScheme = (new PartyTaxScheme())
                ->setCompanyId($config['tax_id'])
                ->setTaxScheme($taxScheme);
            $party->setPartyTaxScheme($partyTaxScheme);
        }

        return (new AccountingParty())->setParty($party);
    }

    /**
     * Build customer (buyer) party
     */
    protected function buildCustomerParty(?object $customerInfo, TaxScheme $taxScheme): AccountingParty
    {
        if ($customerInfo === null) {
            return (new AccountingParty())->setParty(new Party());
        }

        $countryCode = getCountryCode($customerInfo->country ?? '');

        $country = (new Country())->setIdentificationCode($countryCode);
        $address = (new Address())
            ->setStreetName($customerInfo->address_1 ?? '')
            ->setAddressLine([$customerInfo->address_2 ?? ''])
            ->setCityName($customerInfo->city ?? '')
            ->setPostalZone($customerInfo->zip ?? '')
            ->setCountrySubentity($customerInfo->state ?? '')
            ->setCountry($country);

        $partyName = ! empty($customerInfo->company_name)
            ? $customerInfo->company_name
            : trim(($customerInfo->first_name ?? '') . ' ' . ($customerInfo->last_name ?? ''));

        $party = (new Party())
            ->setName($partyName)
            ->setPostalAddress($address);

        if (! empty($customerInfo->email)) {
            $contact = (new Contact())
                ->setElectronicMail($customerInfo->email)
                ->setTelephone($customerInfo->phone_number ?? '');
            $party->setContact($contact);
        }

        $accountingParty = (new AccountingParty())->setParty($party);

        if (! empty($customerInfo->account_number)) {
            $accountingParty->setSupplierAssignedAccountId($customerInfo->account_number);
        }

        if (! empty($customerInfo->tax_id)) {
            $partyTaxScheme = (new PartyTaxScheme())
                ->setCompanyId($customerInfo->tax_id)
                ->setTaxScheme($taxScheme);
            $party->setPartyTaxScheme($partyTaxScheme);
        }

        return $accountingParty;
    }

    /**
     * Build invoice lines
     */
    protected function buildInvoiceLines(array $saleData, TaxScheme $taxScheme, bool $isTaxIncluded): array
    {
        $lines     = [];
        $itemTaxes = $saleData['item_taxes'] ?? [];
        $cart      = $saleData['cart'] ?? [];

        foreach ($cart as $item) {
            $itemId       = $item['item_id'] ?? 0;
            $quantity     = (float) ($item['quantity'] ?? 0);
            $unitPrice    = (float) ($item['price'] ?? 0);
            $discount     = (float) ($item['discount'] ?? 0);
            $discountType = (int) ($item['discount_type'] ?? 0);

            // Calculate discount amount per unit
            if ($discountType === PERCENT && $discount > 0) {
                // Percentage discount
                $discountAmountPerUnit = round($unitPrice * $discount / 100, 4);
            } else {
                // Fixed discount (discount is total for the line, divide by quantity)
                $discountAmountPerUnit = $quantity > 0 ? round($discount / $quantity, 4) : 0;
            }

            // Net price per unit (after discount)
            $netPricePerUnit = round($unitPrice - $discountAmountPerUnit, 4);
            if ($netPricePerUnit < 0) {
                $netPricePerUnit = 0;
            }

            // Get tax rate for this item
            $taxRate     = 0.0;
            $taxCategory = (new TaxCategory())
                ->setId('S')
                ->setPercent(0)
                ->setTaxScheme($taxScheme);

            if (isset($itemTaxes[$itemId]) && ! empty($itemTaxes[$itemId])) {
                // Use the first (primary) tax for this item
                $itemTax = $itemTaxes[$itemId][0];
                $taxRate = (float) ($itemTax['percent'] ?? 0);

                if (abs($taxRate) < 0.001) {
                    $taxCategory->setId('Z'); // Zero rated
                } elseif ($taxRate < 0) {
                    $taxCategory->setId('E'); // Exempt
                } else {
                    $taxCategory->setId('S'); // Standard
                }
                $taxCategory->setPercent(round($taxRate, 2));
            }

            // Calculate line extension amount (net line total)
            $lineExtensionAmount = round($netPricePerUnit * $quantity, 2);

            // Build Price - PriceAmount MUST be the net price excluding VAT per Peppol EN16931 (BR-27)
            // "The price of an item, exclusive of VAT, after subtracting discount"
            $price = (new Price())
                ->setBaseQuantity(1.0)
                ->setUnitCode(UnitCode::UNIT);

            if ($isTaxIncluded && $taxRate > 0) {
                // Tax-inclusive: cart price includes VAT, so extract the net price
                // net_price = gross_price / (1 + tax_rate/100)
                $taxExclusivePricePerUnit = round($netPricePerUnit / (1 + $taxRate / 100), 4);
                $price->setPriceAmount($taxExclusivePricePerUnit);

                // Recalculate line extension amount with tax-exclusive price
                $lineExtensionAmount = round($taxExclusivePricePerUnit * $quantity, 2);
            } else {
                // Tax-exclusive: cart price is already the net price
                $price->setPriceAmount(round($netPricePerUnit, 4));
            }

            // Add AllowanceCharge if there's a discount (gross-to-net price reduction)
            if ($discountAmountPerUnit > 0) {
                $allowanceCharge = (new AllowanceCharge())
                    ->setChargeIndicator(false) // false = allowance/discount
                    ->setAllowanceChargeReason('Discount')
                    ->setAmount(round($discountAmountPerUnit, 4))
                    ->setBaseAmount(round((float) ($item['price'] ?? 0), 4));

                $price->setAllowanceCharge($allowanceCharge);
            }

            // Build Item
            $itemObj = (new Item())
                ->setName($item['name'] ?? '')
                ->setDescription($item['description'] ?? '')
                ->setClassifiedTaxCategory($taxCategory);

            // Add SellersItemIdentification if item_number exists (BR-25)
            if (! empty($item['item_number'])) {
                $itemObj->setSellersItemIdentification((string) $item['item_number']);
            }

            // Build InvoiceLine
            $line = (new InvoiceLine())
                ->setId(isset($item['line']) ? (string) $item['line'] : '1')
                ->setInvoicedQuantity($quantity)
                ->setLineExtensionAmount($lineExtensionAmount)
                ->setItem($itemObj)
                ->setPrice($price);

            $lines[] = $line;
        }

        return $lines;
    }

    /**
     * Build tax total from sales_taxes table data
     */
    protected function buildTaxTotal(array $taxes, TaxScheme $taxScheme): TaxTotal
    {
        $totalTax     = '0';
        $taxSubTotals = [];

        foreach ($taxes as $tax) {
            if (isset($tax['tax_rate'])) {
                $taxRate   = (string) $tax['tax_rate'];
                $taxAmount = (string) ($tax['sale_tax_amount'] ?? '0');

                // Use sale_tax_basis directly from DB instead of reverse-computing
                $taxableAmount = (string) ($tax['sale_tax_basis'] ?? '0');

                // Determine category ID based on tax rate
                $categoryId = 'S'; // Standard
                $floatRate  = (float) $taxRate;
                if (abs($floatRate) < 0.001) {
                    $categoryId = 'Z'; // Zero rated
                } elseif ($floatRate < 0) {
                    $categoryId = 'E'; // Exempt
                }

                $taxCategory = (new TaxCategory())
                    ->setId($categoryId)
                    ->setPercent(round($floatRate, 2))
                    ->setTaxScheme($taxScheme);

                $taxSubTotal = (new TaxSubTotal())
                    ->setTaxableAmount(round((float) $taxableAmount, 2))
                    ->setTaxAmount(round((float) $taxAmount, 2))
                    ->setTaxCategory($taxCategory);

                $taxSubTotals[] = $taxSubTotal;
                $totalTax       = bcadd($totalTax, $taxAmount);
            }
        }

        $taxTotal = new TaxTotal();
        $taxTotal->setTaxAmount(round((float) $totalTax, 2));

        foreach ($taxSubTotals as $subTotal) {
            $taxTotal->addTaxSubTotal($subTotal);
        }

        return $taxTotal;
    }

    /**
     * Build monetary total
     */
    protected function buildMonetaryTotal(array $saleData): LegalMonetaryTotal
    {
        // In OSPOS, after get_totals(): subtotal is ALWAYS tax-exclusive (net)
        // total is ALWAYS tax-inclusive (gross)
        $subtotal  = (float) ($saleData['subtotal'] ?? 0);
        $total     = (float) ($saleData['total'] ?? 0);
        $amountDue = (float) ($saleData['amount_due'] ?? 0);

        return (new LegalMonetaryTotal())
            ->setLineExtensionAmount(round($subtotal, 2))
            ->setTaxExclusiveAmount(round($subtotal, 2))
            ->setTaxInclusiveAmount(round($total, 2))
            ->setPayableAmount(round($amountDue, 2));
    }

    /**
     * Parse address string into components
     */
    protected function parseAddress(string $address): array
    {
        $parts = array_filter(array_map('trim', explode("\n", $address)));

        $result = [
            'street' => '',
            'number' => '',
            'city'   => '',
            'zip'    => '',
        ];

        if (! empty($parts)) {
            $result['street'] = $parts[0];
            if (isset($parts[1])) {
                // Match 4-5 digit postal codes (e.g., 1234, 12345) followed by city name
                if (preg_match('/(\d{4,5})\s*(.+)/', $parts[1], $matches)) {
                    $result['zip']  = $matches[1];
                    $result['city'] = $matches[2];
                } else {
                    $result['city'] = $parts[1];
                }
            }
        }

        return $result;
    }
}
