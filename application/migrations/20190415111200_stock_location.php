<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_stock_location extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		$stock_locations = $this->Stock_location->get_all()->result_array();

		//Add stock locations to the import file
		foreach($stock_locations as $location)
		{
			add_import_file_column('location_' .$location['location_name'],'../import_items.csv', TRUE);
		}
	}

	public function down()
	{

	}
}
?>
