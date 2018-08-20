<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Expenses_Supplier_Id extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		$this->add_supplier_id();
		$this->link_suppliers();
		if($this->there_are_unknown_suppliers()) {
			$this->save_name_in_description();
			$unknown_supplier_id = $this->create_unknown_supplier();
			$this->link_unknown_supplier($unknown_supplier_id);
		}
		$this->create_foreign_key();
		$this->delete_supplier_name();
	}

	public function down()
	{

	}

	private function add_supplier_id()
	{
		$query	= "ALTER TABLE `ospos_expenses` ";
		$query .= "ADD COLUMN `supplier_id` int(10) NOT NULL;";
		$this->db->query($query);
	}

	private function link_suppliers()
	{
		$query	= "UPDATE `ospos_expenses` ";
		$query .= "INNER JOIN `ospos_suppliers` ";
		$query .= "ON `ospos_expenses`.`supplier_name` = `ospos_suppliers`.`company_name` ";
		$query .= "SET `ospos_expenses`.`supplier_id` = `ospos_suppliers`.`person_id`;";
		$this->db->query($query);
	}

	private function there_are_unknown_suppliers()
	{
		$query	= "SELECT COUNT(*) AS amount ";
		$query .= "FROM `ospos_expenses` ";
		$query .= "WHERE `supplier_id` = 0;";
		$result = $this->db->query($query);
		$amount = $result->row()->amount;
		return $amount > 0;
	}

	private function save_name_in_description()
	{
		$query	= "UPDATE `ospos_expenses` ";
		$query .= "SET `description` = CONCAT(`description`, CONCAT('\nSupplier name: ', `supplier_name`)) ";
		$query .= "WHERE `supplier_id` = 0;";
		$this->db->query($query);
	}

	private function create_unknown_supplier()
	{
		$query	= "INSERT INTO `ospos_people` (`first_name`, `last_name`, `comments`) ";
		$query .= "VALUES ('Unknown', 'Supplier', 'This supplier was added automatically by OSPOS. It is associated with expenses whose supplier is unknown. Before deleting this supplier, correct the expenses associated with it by heading to the Expenses section and selecting the appropriate supplier for each expense. In order to determine the original supplier, you can look at the supplier\'s name in each expense\'s description field.');";
		$this->db->query($query);
		$id = $this->db->insert_id();
		$query	= "INSERT INTO `ospos_suppliers` (`person_id`, `company_name`) ";
		$query .= "VALUES (" . $id . ", 'Unknown Supplier');";
		$this->db->query($query);
		return $id;
	}

	private function link_unknown_supplier($unknown_supplier_id)
	{
		$query	= "UPDATE `ospos_expenses` ";
		$query .= "SET `supplier_id` = " . $unknown_supplier_id . " ";
		$query .= "WHERE `supplier_id`= 0;";
		$this->db->query($query);
	}

	private function create_foreign_key()
	{
		$query	= "ALTER TABLE `ospos_expenses` ";
		$query .= "ADD CONSTRAINT `ospos_expenses_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `ospos_suppliers` (`person_id`);";
		$this->db->query($query);
	}

	private function delete_supplier_name()
	{
		$query	= "ALTER TABLE `ospos_expenses` ";
		$query .= "DROP COLUMN `supplier_name`;";
		$this->db->query($query);
	}

}
?>
