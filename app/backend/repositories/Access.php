<?php

/**
 * Article business logic
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Repositories;

use \Lcd\App\Backend\Repositories\BaseRepository;

class Access extends BaseRepository
{
    protected $class = 'Access';
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
    public function get_access_list($profileID)
    {
        $data = $this->get_model('AccessModel')->get_access_list($profileID);
        return $data;
    }

    /**
     * @method save
     * @auth: ledung
     * @param  $data
     * @return mix
     */
    public function save(array $data)
    {
        try {
            $db = $this->getDI()->get('db');
            // Note
            $db->begin();
            // Note
            $this->delete($data['profileID']);
            if (!empty($data['module'])) {
                $this->create_access($data);
            }
            // Note
            $db->commit();
        } catch (\Exception $e) {
            // Note
            $db->rollback();

            throw new \Exception($e->getMessage(), intval($e->getCode()));
        }
    }

    /**
     * @method delete
     * @auth: ledung
     * @param  $profileID
     * @return mix
     */
    public function delete($profileID)
    {
        $affectedRows = $this->get_model('AccessModel')->delete_record($profileID);
        return true;
    }

    /**
     * @method create_access
     * @auth: ledung
     * @param  $data
     * @return bool|int
     */
    protected function create_access(array $data)
    {
        $accessModel = $this->get_model('AccessModel');
        foreach ($data['module'] as $id_module => $item) {
            // init variable
            $is_view   = (isset($item['view'])) ? 1 : 0;
            $is_add    = (isset($item['add'])) ? 1 : 0;
            $is_edit   = (isset($item['edit'])) ? 1 : 0;
            $is_delete = (isset($item['delete'])) ? 1 : 0;

            // Make -> input DB
            $insertData = array(
                'id_profile' => $data['profileID'],
                'id_module'  => $id_module,
                'is_view'    => $is_view,
                'is_add'     => $is_add,
                'is_edit'    => $is_edit,
                'is_delete'  => $is_delete,
            );
            $accessModel->insert_record($insertData);
        }
        return true;
    }

    public function permission_check($controllerName, $actionName, $user)
    {
        $id_module = 0;
        // Allow data not support
        if (empty($controllerName) || empty($actionName) || empty($user)) {
            return true;
        }
        // Allow special profile
        if ($user['id_profile'] == 1) {
            return true;
        }
        // Check data controller and data user
        $conditions = array(
            'name' => $controllerName,
            'active' => 1
        );
        $moduleData = $this->get_model('ModulesModel')->detailBy($conditions);
        if ($moduleData) {
            $moduleData = $moduleData->toArray();
            $id_module = $moduleData['id'];
        }
        if (!empty($user['access'][$id_module])) {
            return true;
        }
        return false;
    }

}
