<?php

/**
 * Article business logic
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Repositories;

use \Lcd\App\Backend\Repositories\BaseRepository;

class Profile extends BaseRepository
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
    public function get_modules_list($profileID)
    {
        $modules = $this->get_model('ModulesModel')->get_modules_list();

        $access = $this->get_model('AccessModel')->get_access_list($profileID);

        $modulesAcess = $modules->toArray();
        foreach ($modulesAcess as $key => $module) {
            foreach ($access->toArray() as $item) {
                if ($module['id'] == $item['id_module']) {
                    $modulesAcess[$key]['is_view'] = $item['is_view'];
                    $modulesAcess[$key]['is_add'] = $item['is_add'];
                    $modulesAcess[$key]['is_edit'] = $item['is_edit'];
                    $modulesAcess[$key]['is_delete'] = $item['is_delete'];
                }
            }
        }

        return $modulesAcess;
    }

    /**
     * @method get_list
     * @auth: ledung
     * @param  $page
     * @param  $pagesize
     * @param  $ext
     * @return mix
     */
    public function get_list($page, $pagesize = 10, array $ext = array())
    {
        $paginator = $this->get_model('ProfileModel')->get_list($page, $pagesize, $ext);
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
    public function get_profile_list()
    {
        $data = $this->get_model('ProfileModel')->get_profile_list();
        return $data;
    }

    /**
     * @method detail
     * @auth: ledung
     * @param  $profileID
     * @return mix
     */
    public function detail($profileID)
    {
        $profile = $this->get_model('ProfileModel')->detail($profileID);
        $profile = $profile->toArray()[0];
        return $profile;
    }

    /**
     * @method update_record
     * @auth: ledung
     * @param  $data
     * @param  $profileID
     * @return int
     */
    public function update_record(array $data, $profileID)
    {
        $affectedRows = $this->get_model('ProfileModel')->update_record($data, $profileID);
        $affectedRows = intval($affectedRows);
        return $affectedRows;
    }

    /**
     * @method save
     * @auth: ledung
     * @param  $data
     * @param  $profileID
     * @return mix
     */
    public function save(array $data, $profileID = null)
    {
        if (empty($profileID)) {
            // Note
            $this->create($data);
        } else {
            // Note
            $this->update($data, $profileID);
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
            $profileID = $this->create_profile($data);
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
     * @param  $profileID
     * @return mix
     */
    protected function update(array $data, $profileID)
    {
        try {
            $db = $this->getDI()->get('db');
            // Note
            $db->begin();
            // Note
            $this->update_profile($data, $profileID);
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
    public function get_count()
    {
        $count = $this->get_model('ProfileModel')->get_count();
        return $count;
    }

    /**
     * @method delete
     * @auth: ledung
     * @param  $profileID
     * @return mix
     */
    public function delete($profileID)
    {
        $affectedRows = $this->get_model('ProfileModel')->delete_record($profileID);
        $affectedRows = intval($affectedRows);
        return $affectedRows;
    }

    /**
     * @method create_profile
     * @auth: ledung
     * @param  $data
     * @return bool|int
     */
    protected function create_profile(array $data)
    {
        $profileID = $this->get_model('ProfileModel')->insert_record(array(
            'name'   => $data['name'],
            'status' => $data['status'],
        ));
        return $profileID;
    }

    /**
     * @method update_profile
     * @auth: ledung
     * @param  $data
     * @param  $profileID
     * @return int
     */
    protected function update_profile(array $data, $profileID)
    {
        $affectedRows = $this->get_model('ProfileModel')->update_record(array(
            'name'   => $data['name'],
            'status' => $data['status'],
        ), $profileID);
        return $affectedRows;
    }

}
