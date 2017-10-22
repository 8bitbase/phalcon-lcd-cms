<?php

/**
 * MenuModel
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Models;

use \Lcd\App\Backend\Models\BaseModel;

class MenuModel extends BaseModel
{
    const TABLE_NAME = 'menu';

    public function initialize()
    {
        parent::initialize();
        $this->set_table_source(self::TABLE_NAME);
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
     * @return \Phalcon\Mvc\Model\Resultset|\Phalcon\Mvc\Phalcon\Mvc\Model
     */
    public function insert_record(array $data)
    {
        if (!is_array($data) || count($data) == 0) {
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
     * @param  $id
     * @return int
     */
    public function update_record(array $data, $id)
    {
        $id = intval($id);
        if (count($data) == 0 || $id <= 0) {
            throw new \Exception('Parameter error');
        }
        $data = $this->before_update($data);

        $this->id = $id;
        $result    = $this->iupdate($data);
        if (!$result) {
            throw new \Exception(implode(',', $this->getMessages()));
        }
        $affectedRows = $this->db->affectedRows();
        $affectedRows = intval($affectedRows);
        return $affectedRows;
    }

    /**
     * @method detail
     * @auth: ledung
     * @param  $id
     * @return array
     */
    public function detail($id)
    {
        $id = intval($id);
        if ($id <= 0) {
            throw new \Exception('Parameter error');
        }
        $result = $this->findFirst(array(
            'conditions' => 'id = :id: AND status = :status:',
            'bind'       => array(
                'id'    => $id,
                'status' => 1,
            ),
        ));
        $menu = array();
        if ($result) {
            $menu = $result->toArray();
        }
        return $menu;
    }

    /**
     * @method get_menu_for_tree
     * @auth: ledung
     * @param  integer           $status
     * @return array
     */
    public function get_menu_for_tree($is_deleted = 0)
    {
        $menuList = array();
        $is_deleted   = intval($is_deleted);
        $result   = $this->find(array(
            'columns'    => 'id, name, url, parent, path, sort, modify_time, id_category',
            'conditions' => 'is_deleted = :is_deleted:',
            'bind'       => array(
                'is_deleted' => $is_deleted,
            ),
            'order'      => 'LENGTH(path) DESC, parent DESC, sort asc',
        ));
        if ($result) {
            $menuList = $result->toArray();
        }
        return $menuList;
    }

    /**
     * @method update_path
     * Update the menu path (using the native PDO processing, so the placeholders are not consistent with the placements of the phalcon package, please note)
     * @auth: ledung
     * @param  $newPath
     * @param  $oldPath
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
            throw new \Exception('Update menu path failed');
        }
        $affectedRows = $stmt->rowCount();
        return $affectedRows;
    }

    /**
     * @method update_parentid
     * @auth: ledung
     * @param  $newParentid
     * @param  $oldParentid
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
            throw new \Exception('Update parent menu ID failed');
        }
        $affectedRows = $this->db->affectedRows();
        return $affectedRows;
    }
}
