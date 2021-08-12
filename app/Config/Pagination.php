<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

//TODO: this likely needs to be ported to CI4 styling.
/*
|--------------------------------------------------------------------------
| Bootstrap 3 pagination links styling
|--------------------------------------------------------------------------
|
| Source code from http://stackoverflow.com/questions/20088779/bootstrap-3-pagination-with-codeigniter
*/
class Pagination extends BaseConfig
{
	public $config = [
		'full_tag_open' => "<ul class='pagination pagination-sm'>",
		'full_tag_close' => '</ul>',
		'num_tag_open' => '<li>',
		'num_tag_close' => '</li>',
		'cur_tag_open' => "<li class='disabled'><li class='active'><a href='#'>",
		'cur_tag_close' => "<span class='sr-only'></span></a></li>",
		'next_tag_open' => "<li>",
		'next_tagl_close' => "</li>",
		'prev_tag_open' => "<li>",
		'prev_tagl_close' => "</li>",
		'first_tag_open' => "<li>",
		'first_tagl_close' => "</li>",
		'last_tag_open' => "<li>",
		'last_tagl_close' => "</li>"
	];
}
