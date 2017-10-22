<?php

/**
 * Article business logic
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Repositories;

use \Lcd\App\Backend\Repositories\BaseRepository;

class Modules extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @method get_list
     * @auth: ledung
     * @param  $page
     * @param  $pagesize
     * @param  $ext
     * @return mix
     */
    public function get_list($page, $pagesize, array $ext = array())
    {
        $paginator = $this->get_model('ModulesModel')->get_list($page, $pagesize, $ext);
        return $paginator;
    }

    /**
     * @method get_list
     * @auth: ledung
     * @param  $page
     * @param  $pagesize
     * @param  $ext
     * @return mix
     */
    public function get_modules_list()
    {
        $data = $this->get_model('ModulesModel')->get_modules_list();
        return $data;
    }

    /**
     * @method detail
     * @auth: ledung
     * @param  $modulesID
     * @return mix
     */
    public function detail($modulesID = null, $conditions = null)
    {
        $modules = array();
        if ($modulesID != null) {
            $modules = $this->get_model('ModulesModel')
                ->detail($modulesID);
        } elseif ($conditions !=null ) {
            $modules = $this->get_model('ModulesModel')
                ->detailBy($conditions);
        }

        if ($modules) {
            $modules = $modules->toArray();
        }
        return $modules;
    }

    /**
     * @method update_record
     * @auth: ledung
     * @param  $data
     * @param  $modulesID
     * @return int
     */
    public function update_record(array $data, $modulesID)
    {
        $affectedRows = $this->get_model('ModulesModel')->update_record($data, $modulesID);
        $affectedRows = intval($affectedRows);
        return $affectedRows;
    }

    /**
     * @method save
     * @auth: ledung
     * @param  $data
     * @param  $modulesID
     * @return mix
     */
    public function save(array $data, $modulesID = null)
    {
        if (empty($modulesID)) {
            // Note
            $this->create($data);
        } else {
            // Note
            $this->update($data, $modulesID);
        }
    }

    /**
     * @method create
     * @auth: ledung
     * @param  $data
     * @return mix
     */
    protected function create(array $data)
    {
        try {
            $db = $this->getDI()->get('db');
            // Note
            $db->begin();
            // Note
            $modulesID = $this->create_modules($data);
            // Note
            $db->commit();
        } catch (\Exception $e) {
            // Note
            $db->rollback();

            throw new \Exception($e->getMessage(), intval($e->getCode()));
        }
    }

    /**
     * @method update
     * @auth: ledung
     * @param  $data
     * @param  $modulesID
     * @return mix
     */
    protected function update(array $data, $modulesID)
    {
        try {
            $db = $this->getDI()->get('db');
            // Note
            $db->begin();
            // Note
            // $this->update_modules($data, $modulesID);
            $affectedRows = $this->get_model('ModulesModel')->update_record(array(
                'display'   => $data['display'],
                'active' => $data['active'],
                'version' => $data['version']
            ), $modulesID);
            // Note
            $db->commit();
        } catch (\Exception $e) {
            // Note
            $db->rollback();

            throw new \Exception($e->getMessage(), intval($e->getCode()));
        }
    }

    /**
     * @method get_count
     * @auth: ledung
     * @return mix
     */
    public function get_count($condition)
    {
        if ($condition) {
            $count = $this->get_model('ModulesModel')->get_count_by_name($condition);
        } else {
            $count = $this->get_model('ModulesModel')->get_count();
        }
        return $count;
    }

    /**
     * @method delete
     * @auth: ledung
     * @param  $modulesID
     * @return mix
     */
    public function delete($modulesID)
    {
        $affectedRows = $this->get_model('ModulesModel')->delete_record($modulesID);
        $affectedRows = intval($affectedRows);
        return $affectedRows;
    }

    /**
     * @method create_modules
     * @auth: ledung
     * @param  $data
     * @return bool|int
     */
    protected function create_modules(array $data)
    {
        $insertData = array(
            'name'   => $data['name'],
            'active' => $data['active'],
            'version' => $data['version']
        );
        $modulesID = $this->get_model('ModulesModel')->insert_record($insertData);
        return $modulesID;
    }

    /**
     * @method update_modules
     * @auth: ledung
     * @param  $data
     * @param  $modulesID
     * @return int
     */
    protected function update_modules(array $data, $modulesID)
    {
        $affectedRows = $this->get_model('ModulesModel')->update_record(array(
            'name'   => $data['name'],
            'active' => $data['active'],
            'version' => $data['version']
        ), $modulesID);
        return $affectedRows;
    }

}
