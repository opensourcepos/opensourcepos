<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_IndiaGST extends Migration
{
    /**
     * Perform a migration step.
     */
    public function up(): void
    {
        if (!$this->db->fieldExists('sales_tax_code', 'customers')) {
            return;
        }

        // If number of entries is greater than zero then the tax data needs to be migrated
        helper('migration');
        execute_script(APPPATH . 'Database/Migrations/sqlscripts/3.3.0_indiagst.sql');

        error_log('Migrating tax configuration');

        $count_of_tax_codes = $this->get_count_of_tax_code_entries();

        if ($count_of_tax_codes > 0) {
            $this->migrate_tax_code_data();
        }

        $this->migrate_customer_tax_codes();

        $count_of_rate_entries = $this->get_count_of_rate_entries();

        if ($count_of_rate_entries > 0) {
            $this->migrate_tax_rates();
        }

        $count_of_sales_taxes_entries = $this->get_count_of_sales_taxes_entries();

        if ($count_of_sales_taxes_entries > 0) {
            $this->migrate_sales_taxes_data();
        }

        $this->drop_backups();

        error_log('Migrating tax configuration completed');
    }

    /**
     * Revert a migration step.
     */
    public function down(): void {}

    /**
     * @return int
     */
    private function get_count_of_tax_code_entries(): int
    {
        $builder = $this->db->table('tax_codes_backup');
        $builder->select('COUNT(*) as count');

        return $builder->get()->getRow()->count;
    }

    /**
     * @return int
     */
    private function get_count_of_sales_taxes_entries(): int
    {
        $builder = $this->db->table('sales_taxes_backup');
        $builder->select('COUNT(*) as count');

        return $builder->get()->getRow()->count;
    }

    /**
     * @return int
     */
    private function get_count_of_rate_entries(): int
    {
        $builder = $this->db->table('tax_code_rates_backup');
        $builder->select('COUNT(*) as count');

        return $builder->get()->getRow()->count;
    }

    /**
     *  This copies the old tax code configuration into the new tax code configuration
     *  assigning a tax_code_id id to the entry  This only needs to be done if there are
     *  tax codes in the table.
     *
     * @return void
     */
    private function migrate_tax_code_data(): void
    {
        $this->db->query('INSERT INTO ' . $this->db->prefixTable('tax_codes') . ' (tax_code, tax_code_name, city, state)
            SELECT tax_code, tax_code_name, city, state FROM ' . $this->db->prefixTable('tax_codes_backup'));
    }

    /**
     *  The previous upgrade script added the new column to the customers table.
     *  This will assign a tax code id using the tax code field that was left in place on the customer table.
     *  After it is complete then it will drop the old customer tax code.
     *  This MUST run so that the old tax code is dropped
     *
     * @return void
     */
    private function migrate_customer_tax_codes(): void
    {
        $this->db->query('UPDATE ' . $this->db->prefixTable('customers') . ' AS  fa SET fa.sales_tax_code_id = (
            SELECT tax_code_id FROM ' . $this->db->prefixTable('tax_codes') . ' AS fb where fa.sales_tax_code = fb.tax_code)');

        $this->db->query('ALTER TABLE ' . $this->db->prefixTable('customers') . ' DROP COLUMN sales_tax_code');
    }

    /**
     * The sales taxes table is undergoing a significant primary key change
     * The new table assumes that sales taxes are associated with a jurisdiction
     * For base taxes and the older tax system the tax jurisdiction code table will be
     * initialized with an entry that is used to represent a dummy or consolidated jurisdiction.
     * If there is only one tax jurisdiction then it can be renamed and life moves on.
     * If the user wants to start reporting taxes by jurisdiction then the new jurisdictions need
     * to be created and defined manually AFTER the upgrade.
     * CONVERTING OLD TAX DATA TO BE SPLIT OUT BY JURISDICTION IS BEYOND THE SCOPE OF THIS EFFORT
     */
    private function migrate_sales_taxes_data(): void
    {
        $this->db->query('INSERT INTO ' . $this->db->prefixTable('sales_taxes')
            . ' (sale_id, jurisdiction_id, tax_category_id, tax_type, tax_group, sale_tax_basis, sale_tax_amount, print_sequence, '
            . '`name`, tax_rate, sales_tax_code_id, rounding_code) '
            . 'select sale_id, rate_jurisdiction_id, rate_tax_category_id, tax_type, tax_group, sale_tax_basis, sale_tax_amount, '
            . 'print_sequence, `name`, A.tax_rate, tax_code_id, rounding_code '
            . 'from ' . $this->db->prefixTable('sales_taxes_backup') . ' AS A '
            . 'left outer join ' . $this->db->prefixTable('tax_codes') . ' AS B on sales_tax_code = tax_code '
            . 'left outer join ' . $this->db->prefixTable('tax_rates') . ' AS C on tax_code_id = rate_tax_code_id and A.tax_rate = C.tax_rate '
            . 'order by sale_id');
    }

    /**
     * @return void
     */
    private function migrate_tax_rates(): void
    {
        // Create a dummy jurisdiction record and retrieve the jurisdiction rate id

        $this->db->query('INSERT INTO ' . $this->db->prefixTable('tax_jurisdictions') . ' (jurisdiction_name, tax_group, tax_type, reporting_authority, '
            . "tax_group_sequence, cascade_sequence, deleted)  VALUES ('Jurisdiction1', 'TaxGroup1', '1', 'Authority1', 1, 0, '0')");

        $jurisdiction_id = $this->db->query('SELECT jurisdiction_id FROM ' . $this->db->prefixTable('tax_jurisdictions') . " WHERE jurisdiction_name = 'Jurisdiction1'")->getRow()->jurisdiction_id;

        // Insert old tax_code rates data into the new tax rates table
        $this->db->query('INSERT INTO ' . $this->db->prefixTable('tax_rates')
            . ' (rate_tax_category_id, rate_jurisdiction_id, rate_tax_code_id, tax_rate, tax_rounding_code) '
            . 'SELECT rate_tax_category_id, ' . $jurisdiction_id . ', tax_code_id, tax_rate, rounding_code FROM '
            . $this->db->prefixTable('tax_code_rates_backup') . ' JOIN ' . $this->db->prefixTable('tax_codes')
            . ' ON tax_code = rate_tax_code');
    }

    /**
     * @return void
     */
    private function drop_backups(): void
    {
        $this->db->query('DROP TABLE IF EXISTS ' . $this->db->prefixTable('tax_codes_backup'));
        $this->db->query('DROP TABLE IF EXISTS ' . $this->db->prefixTable('sales_taxes_backup'));
        $this->db->query('DROP TABLE IF EXISTS ' . $this->db->prefixTable('tax_code_rates_backup'));
    }
}
