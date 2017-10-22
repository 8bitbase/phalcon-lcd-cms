<?php

/**
 * Option business logic
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Repositories;

use \Lcd\App\Backend\Repositories\BaseRepository;

class Options extends BaseRepository
{
    protected $class = 'Options';
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
        // Note
        $keyCache = $this->class . 'get_options_list';
        $result = $this->getCacheByKey($keyCache);
        if (empty($result)) {
            $result = $this->get_list();
            // Note
            $this->saveCacheByKey($keyCache, $result);
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
        $keyCache = $this->class . 'get_list';
        $result = $this->getCacheByKey($keyCache);
        if (empty($result)) {
            $result   = array();
            $options = $this->get_model('OptionsModel')->get_list();
            if (is_array($options) && count($options) > 0) {
                foreach ($options as $ok => $ov) {
                    $result[$ov['op_key']] = $ov;
                }
            }
            // Note
            $this->saveCacheByKey($keyCache, $result);
        }

        return $result;
    }

    /**
     * @method get_list
     * @auth: ledung
     * @return array
     */
    public function get_list_value()
    {
        $keyCache = $this->class . 'get_list_value';
        $result = $this->getCacheByKey($keyCache);
        if (empty($result)) {
            $result   = array();
            $options = $this->get_model('OptionsModel')->get_list();
            if (is_array($options) && count($options) > 0) {
                foreach ($options as $ok => $ov) {
                    $result[$ov['op_key']] = $ov['op_value'];
                }
            }
            // Note
            $this->saveCacheByKey($keyCache, $result);
        }

        return $result;
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

    /**
     * @method update
     * @auth: ledung
     * @param  $data
     * @return bool
     */
    public function update(array $data)
    {
        if (count($data) == 0) {
            throw new \Exception('No configuration needs to be updated');
        }
        // Note
        foreach ($data as $k => $v) {
            $this->get_model('OptionsModel')->update_record(array(
                "op_value" => $v,
            ), "{$k}");
        }
        // Note
        $this->delete_options_cache();
        return true;
    }

    /**
     * @method delete_options_cache
     * @auth: ledung
     * @return bool
     */
    public function delete_options_cache()
    {
        $this->deleteCacheByKey($this->class);
    }
}
