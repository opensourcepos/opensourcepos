<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AccountingMexico extends Migration
{
    public function up(): void
    {
        $db = $this->db;
        
        // 1. Update existing default accounts to SAT codes
        $db->table('accounts')->where('code', '1000')->update(['code' => '101.01', 'name' => 'Caja y Efectivo']);
        $db->table('accounts')->where('code', '1200')->update(['code' => '115.01', 'name' => 'Inventario']);
        $db->table('accounts')->where('code', '2000')->update(['code' => '201.01', 'name' => 'Proveedores']);
        $db->table('accounts')->where('code', '3000')->update(['code' => '301.01', 'name' => 'Capital Social']);
        $db->table('accounts')->where('code', '4000')->update(['code' => '401.01', 'name' => 'Ventas / Ingresos']);
        $db->table('accounts')->where('code', '5000')->update(['code' => '501.01', 'name' => 'Costo de Ventas']);
        $db->table('accounts')->where('code', '6000')->update(['code' => '601.01', 'name' => 'Gastos Generales']);

        // 2. Insert new IVA accounts
        $iva_accounts = [
            [
                'code' => '118.01',
                'name' => 'IVA Acreditable (Compras)',
                'type' => 'Asset',
                'deleted' => 0
            ],
            [
                'code' => '208.01',
                'name' => 'IVA Trasladado (Ventas)',
                'type' => 'Liability',
                'deleted' => 0
            ]
        ];

        // Only insert if they don't exist
        foreach ($iva_accounts as $acc) {
            $existing = $db->table('accounts')->where('code', $acc['code'])->get()->getRow();
            if (!$existing) {
                $db->table('accounts')->insert($acc);
            }
        }
    }

    public function down(): void
    {
        // Revert SAT codes back to generic codes
        $db = $this->db;
        $db->table('accounts')->where('code', '101.01')->update(['code' => '1000', 'name' => 'Cash and Cash Equivalents']);
        $db->table('accounts')->where('code', '115.01')->update(['code' => '1200', 'name' => 'Inventory']);
        $db->table('accounts')->where('code', '201.01')->update(['code' => '2000', 'name' => 'Accounts Payable']);
        $db->table('accounts')->where('code', '301.01')->update(['code' => '3000', 'name' => 'Owner Equity']);
        $db->table('accounts')->where('code', '401.01')->update(['code' => '4000', 'name' => 'Sales Revenue']);
        $db->table('accounts')->where('code', '501.01')->update(['code' => '5000', 'name' => 'Cost of Goods Sold']);
        $db->table('accounts')->where('code', '601.01')->update(['code' => '6000', 'name' => 'Operating Expenses']);

        // Only delete accounts if they don't have any accounting items linked (to avoid data loss)
        foreach (['118.01', '208.01'] as $code) {
            $account = $db->table('accounts')->where('code', $code)->get()->getRow();
            if ($account) {
                $count = $db->table('accounting_items')->where('account_id', $account->account_id)->countAllResults();
                if ($count === 0) {
                    $db->table('accounts')->where('account_id', $account->account_id)->delete();
                }
            }
        }
    }
}
