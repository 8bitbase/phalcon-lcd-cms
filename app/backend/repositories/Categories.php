<?php

/**
 * Category business logic
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Repositories;

use \Lcd\App\Backend\Repositories\BaseRepository;
use \Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;

class Categories extends BaseRepository
{

    /**
     * Category class key
     */
    const CATEGORY_TREE_CACHE_KEY = 'categories';

    /**
     * Category cache (s). a week
     */
    const CATEGORY_CACHE_TTL = 86400;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @method get_category_list
     * @auth: ledung
     * @return array
     */
    public function get_category_list()
    {
        // Note
        $result = $this->getCacheByKey(self::CATEGORY_TREE_CACHE_KEY);
        if (empty($result)) {
            $result = $this->get_category_tree_list();
            // Note
            $this->saveCacheByKey(self::CATEGORY_TREE_CACHE_KEY, $result);
        }

        return $result;
    }

    /**
     * @method delete_category_list_cache
     * @auth: ledung
     * @return array
     */
    public function delete_category_list_cache()
    {
        $this->deleteCacheByKey(self::CATEGORY_TREE_CACHE_KEY);
        return true;
    }

    /**
     * @method detail
     * @auth: ledung
     * @param  $id
     * @return array
     */
    public function detail($id)
    {
        $category = $this->get_model('CategoriesModel')->detail($id);
        return $category;
    }

    /**
     * @method get_count
     * @auth: ledung
     * @return mix
     */
    public function get_count()
    {
        $count = $this->get_model('CategoriesModel')->get_count();
        return $count;
    }

    /**
     * @method save
     * @auth: ledung
     * @param  $data
     * @param  $id
     * @return int|mix
     */
    public function save(array $data, $id)
    {
        $id = intval($id);
        if ($id <= 0) {
            // Note
            $this->create($data);
        } else {
            // Note
            $this->update($data, $id);
        }
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
            throw new \Exception('Please select the category you want to delete');
        }
        $categorysModel = $this->get_model('CategoriesModel');
        $categoryArray  = $this->get_category_list();
        try {
            // Note
            $transactionManager = new TransactionManager();
            $transaction        = $transactionManager->get();

            $categorysModel->setTransaction($transaction);
            $affectedRows = $categorysModel->update_record(array(
                'is_deleted' => 1,
            ), $id);
            if ($affectedRows <= 0) {
                $transaction->rollback('Delete category failed');
            }
            // Note
            if (isset($categoryArray[$id]) && $categoryArray[$id]['leaf_node'] == 0) {
                $affectedRowsPath = $categorysModel->update_path($categoryArray[$id]['path'], "{$categoryArray[$id]['path']}{$id}/");
                if ($affectedRowsPath <= 0) {
                    $transaction->rollback('Update the sort path failed, affecting the number of rows to 0');
                }
                $affectedRowsParentid = $categorysModel->update_parentid($categoryArray[$id]['parent'], $id);
                if ($affectedRowsParentid <= 0) {
                    $transaction->rollback('Update child classification parent failed, affecting behavior is 0');
                }
            }
            // Note
            $transaction->commit();
            // Note
            $this->delete_category_list_cache();
        } catch (\Phalcon\Mvc\Model\TransportException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @method update_sort
     * @auth: ledung
     * @param  $sort
     * @param  $id
     * @return mix
     */
    public function update_sort($sort, $id)
    {
        $sort = intval($sort);
        $id   = intval($id);
        if ($id <= 0) {
            throw new \Exception('Parameter error');
        }
        // Note
        $affectedRows = $this->get_model('CategoriesModel')->update_record(array(
            'sort' => $sort,
        ), $id);
        if (!$affectedRows) {
            throw new \Exception('Update sort sort failed');
        }
        // Note
        $this->delete_category_list_cache();

        return $affectedRows;
    }

    /**
     * @method get_category_tree_list
     * @auth: ledung
     * @return array
     */
    protected function get_category_tree_list()
    {
        $categoryArray = array();
        // Note
        $categoryList = $this->get_model('CategoriesModel')->get_category_for_tree();
        if (!is_array($categoryList) || count($categoryList) == 0) {
            return $categoryArray;
        }
        // Note
        foreach ($categoryList as $clk => $clv) {
            $categoryArray[$clv['id']] = $clv;
        }
        unset($categoryList);
        // Note
        foreach ($categoryArray as $cak => &$cav) {
            if (isset($categoryArray[$cav['parent']])) {
                $categoryArray[$cav['parent']]['son'][$cav['id']] = $cav;
                unset($categoryArray[$cak]);
            }
        }

        // Note
        $categoryArray = $this->recursive_category_tree($categoryArray);
        return $categoryArray;
    }

    /**
     * @method recursive_category_tree
     * @auth: ledung
     * @param  $categoryTree
     * @return mix
     */
    protected function recursive_category_tree(array $categoryTree)
    {
        static $categoryList = array();
        foreach ($categoryTree as $ck => $cv) {
            if (isset($cv['son']) && is_array($cv['son']) && count($cv['son']) > 0) {
                $temp = $cv;
                unset($temp['son']);
                $categoryList[$cv['id']]              = $temp;
                $categoryList[$cv['id']]['leaf_node'] = 0; // Note
                $this->recursive_category_tree($cv['son']);
            } else {
                $categoryList[$cv['id']]              = $cv;
                $categoryList[$cv['id']]['leaf_node'] = 1; // Note
            }
        }
        return $categoryList;
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
        $isExist = $this->get_model('CategoriesModel')->category_is_exist($data['category_name'], $data['slug']);
        if ($isExist && $isExist->count() > 0) {
            throw new \Exception('Category name or abbreviation already exists');
        }
        // Note
        $path = '/0/';
        if (isset($data['parent']) && !empty($data['parent'])) {
            $path = $this->get_path_by_parentid($data['parent']);
            if (empty($path)) {
                throw new \Exception('Failed to get the sort path');
            }
            $path .= "{$data['parent']}/";
        }
        $data['path'] = $path;
        // Note
        $id = $this->get_model('CategoriesModel')->insert_record($data);
        $id = intval($id);
        if ($id <= 0) {
            throw new \Exception('Failed to get new category ID failed');
        }
        // Note
        $this->delete_category_list_cache();

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
        if ($id <= 0 || count($data) == 0) {
            throw new \Exception('Parameter error');
        }
        if (isset($data['parent']) && $data['parent'] == $id) {
            throw new \Exception('Can not choose this category as parent classification');
        }
        // Note
        $isExist = $this->get_model('CategoriesModel')->category_is_exist($data['category_name'], $data['slug'], $id);
        if ($isExist && $isExist->count() > 0) {
            throw new \Exception('Category name or abbreviation already exists');
        }
        // Note
        $path = '/0/';
        if (isset($data['parent']) && $data['parent'] > 0) {
            $path = $this->get_path_by_parentid($data['parent']);
            if (empty($path)) {
                throw new \Exception('Failed to get the sort path');
            }
            $path .= "{$data['parent']}/";
        }
        $data['path'] = $path;
        // Note
        $affectedRows = $this->get_model('CategoriesModel')->update_record($data, $id);
        if (!$affectedRows) {
            throw new \Exception('Update failed');
        }
        // Note
        $this->delete_category_list_cache();

        return $affectedRows;
    }

    /**
     * @method get_path_by_parentid
     * @auth: ledung
     * @param  $id
     * @return string
     */
    protected function get_path_by_parentid($id)
    {
        $path     = '';
        $category = $this->get_model('CategoriesModel')->detail($id);
        if (isset($category['path']) && !empty($category['path'])) {
            $path = $category['path'];
        }
        return $path;
    }
}
