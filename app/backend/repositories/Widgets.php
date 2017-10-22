<?php

/**
 * Widgets business logic
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Repositories;

use \Lcd\App\Backend\Repositories\BaseRepository;

class Widgets extends BaseRepository
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @method get_list
     * @auth   ttdat
     * @return array
     */
    public function get_list()
    {
        $widgets = $this->get_model('WidgetsModel')->get_list();
        return $widgets;
    }

    /**
     * @method import_widgets
     * @auth:  hltphat
     * @param  array $data
     * @return bool/array
     */
    public function import_widgets(array $data)
    {
        $insertData = array(
            'code' => $data['code'],
            'name' => $data['name'],
        );
        $kt = $this->get_model('WidgetsModel')->insert_record($insertData);
        return $kt;
    }
}
