<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Pager extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Templates
     * --------------------------------------------------------------------------
     *
     * Pagination links are rendered out using views to configure their
     * appearance. This array contains aliases and the view names to
     * use when rendering the links.
     *
     * Within each view, the Pager object will be available as $pager,
     * and the desired group as $pagerGroup;
     *
     * @var array<string, string>
     */
    public array $templates = [
        'default_full'   => 'CodeIgniter\Pager\Views\default_full',
        'default_simple' => 'CodeIgniter\Pager\Views\default_simple',
        'default_head'   => 'CodeIgniter\Pager\Views\default_head',
    ];

    /**
     * --------------------------------------------------------------------------
     * Items Per Page
     * --------------------------------------------------------------------------
     *
     * The default number of results shown in a single page.
     */
    public int $perPage = 20;

    /**
     * --------------------------------------------------------------------------
     * Bootstrap 3 pagination links styling
     * --------------------------------------------------------------------------
     *
     * Source code from http://stackoverflow.com/questions/20088779/bootstrap-3-pagination-with-codeigniter
     */
    public $config = [
        'full_tag_open'    => '<ul class="pagination pagination-sm">',
        'full_tag_close'   => '</ul>',
        'num_tag_open'     => '<li>',
        'num_tag_close'    => '</li>',
        'cur_tag_open'     => '<li class="disabled"><li class="active"><a href="#">',
        'cur_tag_close'    => '<span class="sr-only"></span></a></li>',
        'next_tag_open'    => '<li>',
        'next_tagl_close'  => '</li>',
        'prev_tag_open'    => '<li>',
        'prev_tagl_close'  => '</li>',
        'first_tag_open'   => '<li>',
        'first_tagl_close' => '</li>',
        'last_tag_open'    => '<li>',
        'last_tagl_close'  => '</li>'
    ];
}
