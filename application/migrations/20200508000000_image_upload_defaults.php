<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_image_upload_defaults extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		$image_values = array(
			array('key' => 'allowed_types', 'value' => 'gif|jpg|png'),
			array('key' => 'max_height', 'value' => '480'),
			array('key' => 'max_size', 'value' => '100'),
			array('key' => 'max_width', 'value' => '640'));

		$this->db->insert_batch('app_config', $image_values);
	}

	public function down()
	{
		$this->db->where_in('key', array('allowed_types','max_height','max_size','max_width'));
		$this->db->delete('app_config');
	}
}
?>
