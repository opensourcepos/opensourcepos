<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_RefundTracking extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		execute_script(APPPATH . 'migrations/sqlscripts/3.3.0_refundtracking.sql');

		// Add missing cash_refund amounts to payments table

		$decimals = totals_decimals();

		$trans_amount = 'ROUND(SUM(CASE WHEN sales_items.discount_type = ' . PERCENT
			. ' THEN sales_items.item_unit_price * sales_items.quantity_purchased * (1 - sales_items.discount / 100) '
			. 'ELSE sales_items.item_unit_price * sales_items.quantity_purchased - sales_items.discount END), ' . $decimals . ') AS trans_amount';

		$cash_payment = $this->lang->line('sales_cash');

		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('migrate_taxes') .
			' (INDEX(sale_id)) ENGINE=MEMORY
			(
				SELECT sales.sale_id, SUM(sales_taxes.sale_tax_amount) AS total_taxes
				FROM ' . $this->db->dbprefix('sales') . ' AS sales
				LEFT OUTER JOIN ' . $this->db->dbprefix('sales_taxes') . ' AS sales_taxes
					ON sales.sale_id = sales_taxes.sale_id
				WHERE sales.sale_status = \'' . COMPLETED . '\' AND sales_taxes.tax_type = \'1\'
				GROUP BY sale_id
			)'
		);

		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('migrate_sales') .
			' (INDEX(sale_id)) ENGINE=MEMORY
			(
				SELECT sales.sale_id, '. $trans_amount . ', sales.employee_id, sales.sale_time'
				. ' FROM ' . $this->db->dbprefix('sales') . ' AS sales '
				. 'LEFT OUTER JOIN ' . $this->db->dbprefix('sales_items') . ' AS sales_items '
				. 'ON sales.sale_id = sales_items.sale_id '
				. 'LEFT OUTER JOIN ' . $this->db->dbprefix('migrate_taxes') . ' AS sumpay_taxes '
				. 'ON sales.sale_id = sumpay_taxes.sale_id '
				. 'WHERE sales.sale_status = \'' . COMPLETED . '\' GROUP BY sale_id
			)'
		);

		$this->db->query('UPDATE ' . $this->db->dbprefix('migrate_sales') . ' AS sumpay_items '
			. 'SET trans_amount = trans_amount + IFNULL((SELECT total_taxes FROM ' . $this->db->dbprefix('migrate_taxes')
			. ' AS sumpay_taxes WHERE sumpay_items.sale_id = sumpay_taxes.sale_id),0)');

		$this->db->query('CREATE TEMPORARY TABLE IF NOT EXISTS ' . $this->db->dbprefix('migrate_payments') .
			' (INDEX(sale_id)) ENGINE=MEMORY
			(
				SELECT sales.sale_id, COUNT(sales.sale_id) AS number_payments, 
					SUM(sales_payments.payment_amount - sales_payments.cash_refund) AS total_payments
				FROM ' . $this->db->dbprefix('sales') . ' AS sales
				LEFT OUTER JOIN ' . $this->db->dbprefix('sales_payments') . ' AS sales_payments
					ON sales.sale_id = sales_payments.sale_id
				WHERE sales.sale_status = \'' . COMPLETED . '\' GROUP BY sale_id
			)'
		);

		// You may be asking yourself why the following is not creating a temporary table.
		// It should be, it originallly was, but there is a bug in MySQL where temporary tables where some SQL statements fail.
		//  The update statement that follows this CREATE TABLE is one of those statements.

		$this->db->query('CREATE TABLE IF NOT EXISTS ' . $this->db->dbprefix('migrate_refund') .
			' (INDEX(sale_id)) ENGINE=MEMORY
			(
				SELECT a.sale_id, total_payments - trans_amount AS refund_amount
				FROM ' . $this->db->dbprefix('migrate_sales') . ' AS a
				JOIN ' . $this->db->dbprefix('migrate_payments') . ' AS b ON a.sale_id = b.sale_id
				WHERE total_payments > trans_amount AND number_payments = 1
			)'
		);

		// Update existing cash transactions with refund amount
		$this->db->query('UPDATE ' . $this->db->dbprefix('sales_payments') . ' AS a
			SET a.cash_refund =
			(SELECT b.refund_amount 
				FROM ' . $this->db->dbprefix('migrate_refund') . ' AS b 
				WHERE a.sale_id = b.sale_id AND a.payment_type = \'' . $cash_payment . '\')
			WHERE EXISTS
				(SELECT b.refund_amount 
					FROM ' . $this->db->dbprefix('migrate_refund') . ' AS b 
					WHERE a.sale_id = b.sale_id AND a.payment_type = \'' . $cash_payment . ' \')'
		);

		// Insert new cash refund transactions for non-cash payments
		$this->db->query('INSERT INTO ' . $this->db->dbprefix('sales_payments') .
		   ' (sale_id, payment_type, employee_id, payment_time, payment_amount, cash_refund) 
			SELECT r.sale_id, \'' . $cash_payment . '\', s.employee_id, sale_time, 0, r.refund_amount 
			FROM ' . $this->db->dbprefix('migrate_refund') . ' AS r
			JOIN ' . $this->db->dbprefix('sales_payments') . ' AS p ON r.sale_id = p.sale_id
			JOIN ' . $this->db->dbprefix('migrate_sales') . ' AS s ON r.sale_id = s.sale_id
			WHERE p.payment_type != \'' . $cash_payment . '\''
		);

		// Post migration cleanup
		$this->db->query('DROP TABLE IF EXISTS ' . $this->db->dbprefix('migrate_refund'));
	}

	public function down()
	{

	}
}
?>
