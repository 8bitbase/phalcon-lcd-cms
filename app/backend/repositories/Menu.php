<?php

/**
 * Menu business logic
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Repositories;

use \Lcd\App\Backend\Repositories\BaseRepository;
use \Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;

class Menu extends BaseRepository
{

    /**
     * Class menu key
     */
    const MENU_TREE_CACHE_KEY = 'menu_tree';

    /**
     * Menu cache (s). a month
     */
    const MENU_CACHE_TTL = 2592000;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @method get_menu_list
     * @auth: ledung
     * @return array
     */
    public function get_menu_list()
    {
        // Note
        $cache = $this->getDI()->get('cache');
        if ($cache->exists(self::MENU_TREE_CACHE_KEY, self::MENU_CACHE_TTL)) {
            $menuList = $cache->get(self::MENU_TREE_CACHE_KEY, self::MENU_CACHE_TTL);
            $menuList = json_decode($menuList, true);
            if (is_array($menuList) && count($menuList) > 0) {
                return $menuList;
            }
        }
        // Note
        $menuList = $this->get_menu_tree_list();
        // Note
        $cache->save(self::MENU_TREE_CACHE_KEY, json_encode($menuList), self::MENU_CACHE_TTL);
        return $menuList;
    }

    /**
     * @method delete_menu_list_cache
     * @auth: ledung
     * @return array
     */
    public function delete_menu_list_cache()
    {
        $cache = $this->getDI()->get('cache');
        if ($cache->exists(self::MENU_TREE_CACHE_KEY, self::MENU_CACHE_TTL)) {
            return $cache->delete(self::MENU_TREE_CACHE_KEY);
        }
        return true;
    }

    /**
     * @method detail
     * @auth: ledung
     * @param  $id
     * @return mix
     */
    public function detail($id)
    {
        $menu = $this->get_model('MenuModel')->detail($id);
        return $menu;
    }

    /**
     * @method update_record
     * @auth: ledung
     * @param  $data
     * @param  $id
     * @return mix
     */
    public function update_record(array $data, $id)
    {
        $affectedRows = $this->get_model('MenuModel')->update_record($data, $id);
        if ($affectedRows) {
            // Note
            $this->delete_menu_list_cache();
        }
        return $affectedRows;
    }

    /**
     * @method delete
     * @auth: ledung
     * @param  $id
     * @return bool
     */
    public function delete($id)
    {
        $id = intval($id);
        if ($id <= 0) {
            throw new \Exception('Please select the menu you want to delete');
        }
        $menuModel = $this->get_model('MenuModel');
        $menuArray = $this->get_menu_list();
        try {
            // Note
            $transactionManager = new TransactionManager();
            $transaction        = $transactionManager->get();

            $menuModel->setTransaction($transaction);
            $affectedRows = $menuModel->update_record(array(
                'is_deleted' => 1,
            ), $id);
            if ($affectedRows <= 0) {
                $transaction->rollback('Delete menu failed');
            }
            // Note
            if (isset($menuArray[$id]) && $menuArray[$id]['leaf_node'] == 0) {
                $affectedRowsPath = $menuModel->update_path($menuArray[$id]['path'], "{$menuArray[$id]['path']}{$id}/");
                if ($affectedRowsPath <= 0) {
                    $transaction->rollback('The update menu path failed, affecting the number of rows to 0');
                }
                $affectedRowsParentcid = $menuModel->update_parentid($menuArray[$id]['parent'], $id);
                if ($affectedRowsParentcid <= 0) {
                    $transaction->rollback('Update submenu parent failed with affecting number of rows 0');
                }
            }
            // Note
            $transaction->commit();
            // Note
            $this->delete_menu_list_cache();
        } catch (\Phalcon\Mvc\Model\TransportException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @method save
     * @auth: ledung
     * @param  $data
     * @param  $id
     * @return mix
     */
    public function save(array $data, $id)
    {
        $id = intval($id);
        if ($id > 0) {
            // Note
            $this->update($data, $id);
        } else {
            // Note
            $this->create($data);
        }
    }

    /**
     * @method create
     * @auth: ledung
     * @param  $data
     * @return int
     */
    protected function create(array $data)
    {
        // Note
        $path = '/0/';
        if (isset($data['parent']) && !empty($data['parent'])) {
            // Note
            $path = $this->get_path_by_parentid($data['parent']);
            if (empty($path)) {
                throw new \Exception('Failed to get menu path');
            }
            $path .= "{$data['parent']}/";
        }
        $data['path'] = $path;
        // Note
        $id = $this->get_model('MenuModel')->insert_record($data);
        $id = intval($id);
        if ($id <= 0) {
            throw new \Exception('Failed to get new menu ID');
        }
        // Note
        $this->delete_menu_list_cache();

        return $id;
    }

    /**
     * @method update
     * @auth: ledung
     * @param  $data
     * @param  $id
     * @return mix
     */
    protected function update(array $data, $id)
    {
        $id = intval($id);
        if (count($data) == 0 || $id <= 0) {
            throw new \Exception('Parameter error');
        }
        if (isset($data['parent']) && $data['parent'] == $id) {
            throw new \Exception('You can not select this menu as the parent menu');
        }
        // Note
        $path = '/0/';
        if (isset($data['parent']) && !empty($data['parent'])) {
            // Note
            $path = $this->get_path_by_parentid($data['parent']);
            if (empty($path)) {
                throw new \Exception('Failed to get menu path');
            }
            $path .= "{$data['parent']}/";
        }
        $data['path'] = $path;
        // Note
        $affectedRows = $this->get_model('MenuModel')->update_record($data, $id);
        if (!$affectedRows) {
            throw new \Exception('Update menu data failed');
        }
        // Note
        $this->delete_menu_list_cache();

        return $affectedRows;
    }

    /**
     * @method get_path_by_parentid
     * @auth: ledung
     * @param  $parentid
     * @return string
     */
    protected function get_path_by_parentid($parentid)
    {
        $path = '';
        $menu = $this->get_model('MenuModel')->detail($parentid);
        if (isset($menu['path']) && !empty($menu['path'])) {
            $path = $menu['path'];
        }
        return $path;
    }

    /**
     * @method get_menu_tree_list
     * @auth: ledung
     * @return array|mix
     */
    protected function get_menu_tree_list()
    {
        $menuArray = array();
        // Note
        $menuList = $this->get_model('MenuModel')->get_menu_for_tree();
        if (!is_array($menuList) || count($menuList) == 0) {
            return $menuArray;
        }
        // Note
        foreach ($menuList as $mk => $mv) {
            if (intval($mv['id_category']) > 0) {
                $mv['category'] = $this->get_repository('Categories')->detail($mv['id_category']);
            }
            $menuArray[$mv['id']] = $mv;
        }
        // Note
        foreach ($menuArray as $k => &$v) {
            if (isset($menuArray[$v['parent']])) {
                $menuArray[$v['parent']]['son'][$v['id']] = $v;
                unset($menuArray[$k]);
            }
        }
        // Note
        $menuArray = $this->recursive_menu_tree($menuArray);
        return $menuArray;
    }

    /**
     * @method recursive_menu_tree
     * @auth: ledung
     * @param  $menuArray
     * @return mix
     */
    protected function recursive_menu_tree(array $menuArray)
    {
        static $menuList = array();
        foreach ($menuArray as $k => $v) {
            if (isset($v['son']) && is_array($v['son']) && count($v['son']) > 0) {
                $temp = $v;
                unset($temp['son']);
                $menuList[$v['id']]              = $temp;
                $menuList[$v['id']]['leaf_node'] = 0; // Note
                $this->recursive_menu_tree($v['son']);
            } else {
                $menuList[$v['id']]              = $v;
                $menuList[$v['id']]['leaf_node'] = 1; // Note
            }
        }
        return $menuList;
    }
}
