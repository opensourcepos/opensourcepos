<?php

namespace App\Libraries;

use NumNum\UBL\Invoice;
use NumNum\UBL\Generator;
use NumNum\UBL\Party;
use NumNum\UBL\Address;
use NumNum\UBL\Country;
use NumNum\UBL\AccountingParty;
use NumNum\UBL\PartyTaxScheme;
use NumNum\UBL\InvoiceLine;
use NumNum\UBL\Item;
use NumNum\UBL\Price;
use NumNum\UBL\UnitCode;
use NumNum\UBL\TaxTotal;
use NumNum\UBL\TaxSubTotal;
use NumNum\UBL\TaxCategory;
use NumNum\UBL\TaxScheme;
use NumNum\UBL\LegalMonetaryTotal;
use NumNum\UBL\Contact;

helper(['country']);

class UBLGenerator
{
    public function generateUblInvoice(array $saleData): string
    {
        $taxScheme = (new TaxScheme())->setId('VAT');
        $supplierParty = $this->buildSupplierParty($saleData, $taxScheme);
        $customerParty = $this->buildCustomerParty($saleData['customer_info'], $taxScheme);
        $invoiceLines = $this->buildInvoiceLines($saleData['cart'], $taxScheme);
        $taxTotal = $this->buildTaxTotal($saleData['taxes'], $taxScheme);
        $monetaryTotal = $this->buildMonetaryTotal($saleData);
        
        $invoice = (new Invoice())
            ->setUBLVersionId('2.1')
            ->setCustomizationId('urn:cen.eu:en16931:2017')
            ->setProfileId('urn:fdc:peppol.eu:2017:poacc:billing:01:1.0')
            ->setId($saleData['invoice_number'])
            ->setIssueDate(new \DateTime($saleData['transaction_date']))
            ->setInvoiceTypeCode(380)
            ->setAccountingSupplierParty($supplierParty)
            ->setAccountingCustomerParty($customerParty)
            ->setInvoiceLines($invoiceLines)
            ->setTaxTotal($taxTotal)
            ->setLegalMonetaryTotal($monetaryTotal);
        
        $generator = new Generator();
        return $generator->invoice($invoice);
    }
    
    protected function buildSupplierParty(array $saleData, TaxScheme $taxScheme): AccountingParty
    {
        $config = $saleData['config'];
        
        $addressParts = $this->parseAddress($config['address'] ?? '');
        $countryCode = 'BE'; // Default
        
        $country = (new Country())->setIdentificationCode($countryCode);
        $address = (new Address())
            ->setStreetName($addressParts['street'] ?? '')
            ->setBuildingNumber($addressParts['number'] ?? '')
            ->setCityName($addressParts['city'] ?? '')
            ->setPostalZone($addressParts['zip'] ?? '')
            ->setCountrySubentity($config['state'] ?? '')
            ->setCountry($country);
        
        $party = (new Party())
            ->setName($config['company'])
            ->setPostalAddress($address);
        
        if (!empty($config['account_number'])) {
            $partyTaxScheme = (new PartyTaxScheme())
                ->setCompanyId($config['account_number'])
                ->setTaxScheme($taxScheme);
            $party->setPartyTaxScheme($partyTaxScheme);
        }
        
        $accountingParty = (new AccountingParty())->setParty($party);
        
        return $accountingParty;
    }
    
    protected function buildCustomerParty(object $customerInfo, TaxScheme $taxScheme): AccountingParty
    {
        $countryCode = getCountryCode($customerInfo->country ?? '');
        
        $country = (new Country())->setIdentificationCode($countryCode);
        $address = (new Address())
            ->setStreetName($customerInfo->address_1 ?? '')
            ->setAddressLine([$customerInfo->address_2 ?? ''])
            ->setCityName($customerInfo->city ?? '')
            ->setPostalZone($customerInfo->zip ?? '')
            ->setCountrySubentity($customerInfo->state ?? '')
            ->setCountry($country);
        
        $partyName = !empty($customerInfo->company_name) 
            ? $customerInfo->company_name 
            : trim($customerInfo->first_name . ' ' . $customerInfo->last_name);
        
        $party = (new Party())
            ->setName($partyName)
            ->setPostalAddress($address);
        
        if (!empty($customerInfo->email)) {
            $contact = (new Contact())
                ->setElectronicMail($customerInfo->email)
                ->setTelephone($customerInfo->phone_number ?? '');
            $party->setContact($contact);
        }
        
        if (!empty($customerInfo->account_number)) {
            $accountingParty = (new AccountingParty())
                ->setParty($party)
                ->setSupplierAssignedAccountId($customerInfo->account_number);
        } else {
            $accountingParty = (new AccountingParty())->setParty($party);
        }
        
        if (!empty($customerInfo->tax_id)) {
            $partyTaxScheme = (new PartyTaxScheme())
                ->setCompanyId($customerInfo->tax_id)
                ->setTaxScheme($taxScheme);
            $party->setPartyTaxScheme($partyTaxScheme);
        }
        
        return $accountingParty;
    }
    
    protected function buildInvoiceLines(array $cart, TaxScheme $taxScheme): array
    {
        $lines = [];
        foreach ($cart as $item) {
            $price = (new Price())
                ->setBaseQuantity(1.0)
                ->setUnitCode(UnitCode::UNIT)
                ->setPriceAmount($item['price'] ?? 0);
            
            $taxCategory = (new TaxCategory())
                ->setId('S')
                ->setPercent((float)($item['tax_rate'] ?? 0))
                ->setTaxScheme($taxScheme);
            
            $itemObj = (new Item())
                ->setName($item['name'] ?? '')
                ->setDescription($item['description'] ?? '')
                ->setClassifiedTaxCategory($taxCategory);
            
            $line = (new InvoiceLine())
                ->setId(isset($item['line']) ? (string)$item['line'] : '1')
                ->setItem($itemObj)
                ->setPrice($price)
                ->setInvoicedQuantity($item['quantity'] ?? 0);
            
            $lines[] = $line;
        }
        return $lines;
    }
    
    protected function buildTaxTotal(array $taxes, TaxScheme $taxScheme): TaxTotal
    {
        $totalTax = '0';
        $taxSubTotals = [];
        
        foreach ($taxes as $tax) {
            if (isset($tax['tax_rate'])) {
                $taxRate = (string)$tax['tax_rate'];
                $taxAmount = (string)($tax['sale_tax_amount'] ?? 0);
                
                $taxCategory = (new TaxCategory())
                    ->setId('S')
                    ->setPercent((float)$taxRate)
                    ->setTaxScheme($taxScheme);
                
                $taxableAmount = '0';
                if (bccomp($taxRate, '0') > 0) {
                    $taxableAmount = bcdiv($taxAmount, bcdiv($taxRate, '100'), 10);
                }
                
                $taxSubTotal = (new TaxSubTotal())
                    ->setTaxableAmount((float)$taxableAmount)
                    ->setTaxAmount((float)$taxAmount)
                    ->setTaxCategory($taxCategory);
                
                $taxSubTotals[] = $taxSubTotal;
                $totalTax = bcadd($totalTax, $taxAmount);
            }
        }
        
        $taxTotal = new TaxTotal();
        $taxTotal->setTaxAmount((float)$totalTax);
        foreach ($taxSubTotals as $subTotal) {
            $taxTotal->addTaxSubTotal($subTotal);
        }
        
        return $taxTotal;
    }
    
    protected function buildMonetaryTotal(array $saleData): LegalMonetaryTotal
    {
        $subtotal = (string)($saleData['subtotal'] ?? 0);
        $total = (string)($saleData['total'] ?? 0);
        $amountDue = (string)($saleData['amount_due'] ?? 0);
        
        return (new LegalMonetaryTotal())
            ->setLineExtensionAmount((float)$subtotal)
            ->setTaxExclusiveAmount((float)$subtotal)
            ->setTaxInclusiveAmount((float)$total)
            ->setPayableAmount((float)$amountDue);
    }
    
    protected function parseAddress(string $address): array
    {
        $parts = array_filter(array_map('trim', explode("\n", $address)));
        
        $result = [
            'street' => '',
            'number' => '',
            'city' => '',
            'zip' => ''
        ];
        
        if (!empty($parts)) {
            $result['street'] = $parts[0];
            if (isset($parts[1])) {
                // Match 4-5 digit postal codes (e.g., 1234, 12345) followed by city name
                // Note: This handles common European formats. International formats
                // like UK postcodes (e.g., "SW1A 2AA") may need additional handling.
                if (preg_match('/(\d{4,5})\s*(.+)/', $parts[1], $matches)) {
                    $result['zip'] = $matches[1];
                    $result['city'] = $matches[2];
                } else {
                    $result['city'] = $parts[1];
                }
            }
        }
        
        return $result;
    }
}