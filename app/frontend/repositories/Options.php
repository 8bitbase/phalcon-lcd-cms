<?php

/**
 * Option business logic
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Frontend\Repositories;

use \Lcd\App\Frontend\Repositories\BaseRepository;

class Options extends BaseRepository
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @method get_options_list
     * @auth: ledung
     * @return array|mix
     */
    public function get_options_list()
    {
        $key = 'options_list';
        $result = $this->getCacheByKey($key);
        if(empty($result)) {
            $result = $this->get_list();
            $this->saveCacheByKey($key, $result);
        }
        return $result;
    }

    /**
     * @method get_list
     * @auth: ledung
     * @return array
     */
    public function get_list()
    {
        $array   = array();
        $options = $this->get_model('OptionsModel')->get_list();
        if (is_array($options) && count($options) > 0) {
            foreach ($options as $ok => $ov) {
                $array[$ov['op_key']] = $ov;
            }
        }
        return $array;
    }

    /**
     * @method get_option
     * @auth: ledung
     * @param  $key
     * @return bool|mix
     */
    public function get_option($key)
    {
        $options = $this->get_options_list();
        if (is_array($options) && isset($options[$key])) {
            return $options[$key]['op_value'];
        }
        return false;
    }
}
