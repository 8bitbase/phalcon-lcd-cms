<?php

/**
 * CategoriesModel
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Models;

use \Lcd\App\Backend\Models\BaseModel;

class CategoriesModel extends BaseModel
{
    const TABLE_NAME = 'categories';

    public function initialize()
    {
        parent::initialize();
        $this->set_table_source(self::TABLE_NAME);
    }

    /**
     * @method get_count
     * @auth: ledung
     * @param  integer   $status
     * @return mix
     */
    public function get_count($status = 1)
    {
        $status = intval($status);
        $count  = $this->count(array(
            'conditions' => 'status = :status:',
            'bind'       => array(
                'status' => $status,
            ),
        ));
        return $count;
    }

    /**
     *     Is executed before the fields are validated for not nulls/empty strings
     *  or foreign keys when an insertion operation is being made
     */
    public function beforeValidationOnCreate()
    {
        if ($this->sort <= 0 || $this->sort > 999) {
            $this->sort = 999;
        }
        $this->create_by   = $this->_user['uid'];
        $this->create_time = date('Y-m-d H:i:s');
        $this->modify_by   = $this->_user['uid'];
        $this->modify_time = date('Y-m-d H:i:s');
    }

    /**
     * @method insert_record
     * @auth: ledung
     * @param  array         $data
     * @return bool|int
     */
    public function insert_record(array $data)
    {
        if (count($data) == 0) {
            throw new \Exception('Parameter error');
        }
        $result = $this->create($data);
        if (!$result) {
            throw new \Exception(implode(',', $this->getMessages()));
        }
        $id = $this->id;
        return $id;
    }

    /**
     * @method before_update
     * @auth: ledung
     * @param  array         $data
     * @return array
     */
    protected function before_update(array $data)
    {
        if (isset($data['sort']) && ($data['sort'] <= 0 || $data['sort'] > 999)) {
            $data['sort'] = 999;
        }
        $data['modify_by']   = $this->_user['uid'];
        $data['modify_time'] = date('Y-m-d H:i:s');
        return $data;
    }

    /**
     * @method update_record
     * @auth: ledung
     * @param  array         $data
     * @param  int        $id
     * @return int
     */
    public function update_record(array $data, $id)
    {
        $id   = intval($id);
        $data = $this->before_update($data);
        if (count($data) == 0 || $id <= 0) {
            throw new \Exception('Parameter error');
        }

        $this->id = $id;
        $result   = $this->iupdate($data);
        if (!$result) {
            throw new \Exception(implode(',', $this->getMessages()));
        }
        $affectedRows = $this->db->affectedRows();
        return $affectedRows;
    }

    /**
     * @method update_path
     * Update the sorting path (using the native PDO handle, so the placeholder is not consistent with the placeholder for the phalcon package, please note)
     * @auth: ledung
     * @param  string      $newPath
     * @param  string      $oldPath
     * @return int
     */
    public function update_path($newPath, $oldPath)
    {
        if (empty($newPath) || empty($oldPath)) {
            throw new \Exception('Parameter error');
        }
        $sql = "UPDATE " . $this->getSource() . " SET path=REPLACE(path, :oldPath, :newPath) ";
        $sql .= ' WHERE `path` like :path AND `status` = :status ';
        $stmt = $this->db->prepare($sql);
        $bind = array(
            'oldPath' => "{$oldPath}",
            'newPath' => "{$newPath}",
            'path'    => "{$oldPath}%",
            'status'  => 1,
        );
        $result = $stmt->execute($bind);
        if (!$result) {
            throw new \Exception('Update the sort path failed');
        }
        $affectedRows = $stmt->rowCount();
        return $affectedRows;
    }

    /**
     * @method update_parentid
     * @auth: ledung
     * @param  string           $newParentid
     * @param  string           $oldParentid
     * @return int
     */
    public function update_parentid($newParentid, $oldParentid)
    {
        $newParentid = intval($newParentid);
        $oldParentid = intval($oldParentid);
        if ($oldParentid <= 0) {
            throw new \Exception('Parameter error');
        }

        $result = $this->db->update(
            $this->getSource(),
            array('parent'),
            array($newParentid),
            array(
                'conditions' => 'parent = ? AND `status` = ? ',
                'bind'       => array($oldParentid, 1),
            )
        );
        if (!$result) {
            throw new \Exception('Failed to update parent class ID');
        }
        $affectedRows = $this->db->affectedRows();
        return $affectedRows;
    }

    /**
     * @method detail
     * @auth: ledung
     * @param  int $id
     * @return array
     */
    public function detail($id)
    {
        $category = array();
        $id       = intval($id);
        if ($id < 0) {
            throw new \Exception('Parameter error');
        }

        $result = $this->findFirst(array(
            'conditions' => 'id = :id:',
            'bind'       => array(
                'id' => $id,
            ),
        ));
        if ($result) {
            $category = $result->toArray();
        }
        return $category;
    }

    /**
     * @method get_category_for_tree
     * @auth: ledung
     * @param  int               $status
     * @return array
     */
    public function get_category_for_tree($is_deleted = 0)
    {
        $categoryList = array();
        $is_deleted   = intval($is_deleted);
        $result       = $this->find(array(
            'columns'    => 'id, name, slug, parent, path, sort, modify_time, status',
            'conditions' => 'is_deleted = :is_deleted:',
            'bind'       => array(
                'is_deleted' => $is_deleted,
            ),
            'order'      => 'LENGTH(path) DESC, parent DESC, sort asc',
        ));
        if ($result) {
            $categoryList = $result->toArray();
        }
        return $categoryList;
    }

    /**
     * @method category_is_exist
     * Determine whether a classification exists based on name or slug
     * @auth: ledung
     * @param  $categoryName
     * @param  $slug
     * @param  $id
     * @return int
     */
    public function category_is_exist($categoryName = null, $slug = null, $id = null)
    {
        if (empty($categoryName) && empty($slug)) {
            throw new \Exception('Parameter error');
        }
        $params = array();
        if (!empty($categoryName) && !empty($slug)) {
            $params['conditions']           = " (name = :categoryName: OR slug = :slug:) AND is_deleted = 1 ";
            $params['bind']['categoryName'] = $categoryName;
            $params['bind']['slug']         = $slug;
        } elseif (!empty($categoryName)) {
            $params['conditions']           = " name = :categoryName: AND is_deleted = 1 ";
            $params['bind']['categoryName'] = $categoryName;
        } elseif (!empty($slug)) {
            $params['conditions']   = " slug = :slug: AND is_deleted = 1 ";
            $params['bind']['slug'] = $slug;
        }
        $id = intval($id);
        $id > 0 && $params['conditions'] .= " AND id != {$id} ";

        $result = $this->find($params);
        return $result;
    }
}
