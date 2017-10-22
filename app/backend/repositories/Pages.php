<?php

/**
 * Pages business logic
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Repositories;

use \Lcd\App\Backend\Repositories\BaseRepository;

class Pages extends BaseRepository
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @method import_pages
     * @auth:  hltphat
     * @param  array $data
     * @return bool/array
     */
    public function import_pages(array $data)
    {
        $insertData = array(
            'code' => $data['code'],
            'name' => $data['name'],
        );
        $kt = $this->get_model('PagesModel')->insert_record($insertData);
        return $kt;
    }

    /**
     * @method get_pages_list
     * @auth   ttdat
     * @return array
     */
    public function get_list()
    {
        $pages = $this->get_model('PagesModel')->get_list();
        return $pages;
    }
}
