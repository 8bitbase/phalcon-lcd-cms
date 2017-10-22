<?php

/**
 * TagsModel
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Models;

use \Lcd\App\Backend\Models\BaseModel;

class TagsModel extends BaseModel
{
    const TABLE_NAME = 'tags';

    public function initialize()
    {
        parent::initialize();
        $this->set_table_source(self::TABLE_NAME);
    }

    /**
     * @method get_list
     * @auth: ledung
     * @param  array    $ext
     * @return array
     */
    public function get_list(array $ext = array())
    {
        $result = $this->find(array(
            'conditions' => 'is_deleted = :is_deleted:',
            'bind'       => array(
                'is_deleted' => 0,
            ),
        ));
        if (!$result) {
            throw new \Exception('Query data failed');
        }
        $tagsList = $result->toArray();
        return $tagsList;
    }

    /**
     * @method get_count
     * @auth: ledung
     * @return mix
     */
    public function get_count()
    {
        $count = $this->count(array(
            'conditions' => 'is_deleted = :is_deleted:',
            'bind'       => array(
                'is_deleted' => 0,
            ),
        ));
        return $count;
    }

    /**
     * @method detail
     * @auth: ledung
     * @param  $id
     * @return array
     */
    public function detail($id)
    {
        $tag = array();
        $id  = intval($id);
        if ($id <= 0) {
            throw new \Exception('Parameter error');
        }
        $result = $this->findFirst(array(
            'conditions' => 'id = :id: AND is_deleted = :is_deleted:',
            'bind'       => array(
                'id'         => $id,
                'is_deleted' => 0,
            ),
        ));
        if ($result) {
            $tag = $result->toArray();
        }
        return $tag;
    }

    /**
     *     Is executed before the fields are validated for not nulls/empty strings
     *  or foreign keys when an insertion operation is being made
     */
    public function beforeValidationOnCreate()
    {
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
        $clone  = clone $this;
        $result = $clone->create($data);
        if (!$result) {
            throw new \Exception(implode(',', $this->getMessages()));
        }
        $id = $clone->id;
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
        $result   = $this->iupdate($data);
        if (!$result) {
            throw new \Exception(implode(',', $this->getMessages()));
        }
        $affectedRows = $this->db->affectedRows();
        return $affectedRows;
    }

    /**
     * @method get_id_by_name
     * @auth: ledung
     * @param  $name
     * @return int
     */
    public function get_id_by_name($name)
    {
        if (empty($name)) {
            throw new \Exception('Parameter error');
        }
        $params = array(
            'columns'    => 'id',
            'conditions' => 'name = :name: AND is_deleted = :is_deleted:',
            'bind'       => array(
                'name'       => "{$name}",
                'is_deleted' => 0,
            ),
        );
        $result = $this->findFirst($params);
        if ($result) {
            $id = $result->id;
            $id = intval($id);
            if ($id > 0) {
                return $id;
            }
        }
        return false;
    }

    /**
     * @method tag_is_exist
     * @auth: ledung
     * @param  $tagName
     * @param  $slug
     * @param  $id
     * @return \Phalcon\Mvc\Model\ResultsetInterface
     */
    public function tag_is_exist($tagName = null, $slug = null, $id = null)
    {
        if (empty($tagName) && empty($slug)) {
            throw new \Exception('Parameter error');
        }
        $params = array();
        if (!empty($tagName) && !empty($slug)) {
            $params['conditions']      = " (name = :tagName: OR slug = :slug:) AND is_deleted = 0 ";
            $params['bind']['tagName'] = $tagName;
            $params['bind']['slug']    = $slug;
        } elseif (!empty($tagName)) {
            $params['conditions']      = " name = :tagName: AND is_deleted = 0 ";
            $params['bind']['tagName'] = $tagName;
        } elseif (!empty($slug)) {
            $params['conditions']   = " slug = :slug: AND is_deleted = 0 ";
            $params['bind']['slug'] = $slug;
        }
        $id = intval($id);
        $id > 0 && $params['conditions'] .= " AND id != {$id} ";

        $result = $this->find($params);
        return $result;
    }
}
