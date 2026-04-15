<?php

namespace App\Traits\Database;

trait SalesTaxMigration
{
    protected function cleanIdentifier(string $string): string
    {
        $string = str_replace(' ', '-', $string);
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    }

    protected function applyInvoiceTaxing(array &$salesTaxes): void
    {
        if (!empty($salesTaxes)) {
            $sort = [];
            foreach ($salesTaxes as $k => $v) {
                $sort['print_sequence'][$k] = $v['print_sequence'];
            }
            array_multisort($sort['print_sequence'], SORT_ASC, $salesTaxes);
        }

        $decimals = totals_decimals();

        foreach ($salesTaxes as $rowNumber => $salesTax) {
            $salesTaxes[$rowNumber]['sale_tax_amount'] = $this->getSalesTaxForAmount(
                $salesTax['sale_tax_basis'],
                $salesTax['tax_rate'],
                $salesTax['rounding_code'],
                $decimals
            );
        }
    }

    protected function roundSalesTaxes(array &$salesTaxes, int $halfUp = 1, int $roundUp = 5, int $roundDown = 6, int $halfFive = 7): void
    {
        if (!empty($salesTaxes)) {
            $sort = [];
            foreach ($salesTaxes as $k => $v) {
                $sort['print_sequence'][$k] = $v['print_sequence'];
            }
            array_multisort($sort['print_sequence'], SORT_ASC, $salesTaxes);
        }

        $decimals = totals_decimals();

        foreach ($salesTaxes as $rowNumber => $salesTax) {
            $saleTaxAmount = (float)$salesTax['sale_tax_amount'];
            $roundingCode = $salesTax['rounding_code'];
            $roundedSaleTaxAmount = $saleTaxAmount;

            if (
                $roundingCode == PHP_ROUND_HALF_UP
                || $roundingCode == PHP_ROUND_HALF_DOWN
                || $roundingCode == PHP_ROUND_HALF_EVEN
                || $roundingCode == PHP_ROUND_HALF_ODD
            ) {
                $roundedSaleTaxAmount = round($saleTaxAmount, $decimals, $roundingCode);
            } elseif ($roundingCode == $roundUp) {
                $fig = (int) str_pad('1', $decimals, '0');
                $roundedSaleTaxAmount = (ceil($saleTaxAmount * $fig) / $fig);
            } elseif ($roundingCode == $roundDown) {
                $fig = (int) str_pad('1', $decimals, '0');
                $roundedSaleTaxAmount = (floor($saleTaxAmount * $fig) / $fig);
            } elseif ($roundingCode == $halfFive) {
                $roundedSaleTaxAmount = round($saleTaxAmount / 5) * 5;
            }

            $salesTaxes[$rowNumber]['sale_tax_amount'] = $roundedSaleTaxAmount;
        }
    }
}