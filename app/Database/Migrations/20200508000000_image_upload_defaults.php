<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_image_upload_defaults extends CI_Migration
{
	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		$image_values = [
			['key' => 'image_allowed_types', 'value' => 'gif|jpg|png'],
			['key' => 'image_max_height', 'value' => '480'],
			['key' => 'image_max_size', 'value' => '128'],
			['key' => 'image_max_width', 'value' => '640']
		];

		$this->db->insert_batch('app_config', $image_values);
	}

	public function down()
	{
		$builder->whereIn('key', ['image_allowed_types','image_max_height','image_max_size','image_max_width']);
		$builder->delete('app_config');
	}
}
?>
