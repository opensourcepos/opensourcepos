<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AccountingMexico extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // 1. Update existing default accounts to SAT codes
        $db->table('ospos_accounts')->where('code', '1000')->update(['code' => '101.01', 'name' => 'Caja y Efectivo']);
        $db->table('ospos_accounts')->where('code', '1200')->update(['code' => '115.01', 'name' => 'Inventario']);
        $db->table('ospos_accounts')->where('code', '2000')->update(['code' => '201.01', 'name' => 'Proveedores']);
        $db->table('ospos_accounts')->where('code', '3000')->update(['code' => '301.01', 'name' => 'Capital Social']);
        $db->table('ospos_accounts')->where('code', '4000')->update(['code' => '401.01', 'name' => 'Ventas / Ingresos']);
        $db->table('ospos_accounts')->where('code', '5000')->update(['code' => '501.01', 'name' => 'Costo de Ventas']);
        $db->table('ospos_accounts')->where('code', '6000')->update(['code' => '601.01', 'name' => 'Gastos Generales']);

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
            $existing = $db->table('ospos_accounts')->where('code', $acc['code'])->get()->getRow();
            if (!$existing) {
                $db->table('ospos_accounts')->insert($acc);
            }
        }
    }

    public function down()
    {
        // Revert SAT codes back to generic codes
        $db = \Config\Database::connect();
        $db->table('ospos_accounts')->where('code', '101.01')->update(['code' => '1000', 'name' => 'Cash']);
        $db->table('ospos_accounts')->where('code', '115.01')->update(['code' => '1200', 'name' => 'Inventory']);
        $db->table('ospos_accounts')->where('code', '201.01')->update(['code' => '2000', 'name' => 'Accounts Payable']);
        $db->table('ospos_accounts')->where('code', '301.01')->update(['code' => '3000', 'name' => 'Owner Equity']);
        $db->table('ospos_accounts')->where('code', '401.01')->update(['code' => '4000', 'name' => 'Sales Revenue']);
        $db->table('ospos_accounts')->where('code', '501.01')->update(['code' => '5000', 'name' => 'Cost of Goods Sold']);
        $db->table('ospos_accounts')->where('code', '601.01')->update(['code' => '6000', 'name' => 'Operating Expenses']);

        $db->table('ospos_accounts')->whereIn('code', ['118.01', '208.01'])->delete();
    }
}
