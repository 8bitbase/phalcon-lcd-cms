<?php

/**
 * AccessModel
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Models;

use \Lcd\App\Backend\Models\BaseModel;
use \Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class AccessModel extends BaseModel
{
    const TABLE_NAME = 'modules_access';

    public function initialize()
    {
        parent::initialize();
        $this->set_table_source(self::TABLE_NAME);
    }

    /**
     * @method get_list
     * @auth: ledung
     * @param  array   $page
     * @param  integer  $pagesize
     * @param  array    $ext
     * @return mix
     */
    public function get_list($page, $pagesize = 10, array $ext = array())
    {
        $page               = intval($page);
        $page <= 0 && $page = 1;
        $pagesize           = intval($pagesize);

        

        $builder = $this->getModelsManager()->createBuilder();
        $builder->from(array('a' => __CLASS__));
        $builder->columns(array('a.id', 'a.name', 'a.active', 'a.version'));
        if (isset($ext['keyword']) && !empty($ext['keyword'])) {
            $builder->andWhere("a.name like :name:", array('name' => "%{$ext['keyword']}%"));
        }
        $builder->orderBy('a.id DESC');

        $paginator = new PaginatorQueryBuilder(array(
            'builder' => $builder,
            'limit'   => $pagesize,
            'page'    => $page,
        ));
        $result = $paginator->getPaginate();
        return $result;
    }

    /**
     * @method detail
     * @auth: ledung
     * @param  $accessID
     * @return mix
     */
    public function detail($accessID)
    {
        $accessID = intval($accessID);
        if ($accessID <= 0) {
            throw new Exception('Parameter error');
        }
        $builder = $this->getModelsManager()->createBuilder();
        $builder->from(array('a' => __CLASS__));
        $builder->columns(array('a.id', 'a.name', 'a.active', 'a.version'));
        $result = $builder->where("a.id = :accessID:", array('accessID' => $accessID))
            ->limit(1)
            ->getQuery()
            ->execute();
        if (!$result) {
            throw new \Exception('Get data failed');
        }
        return $result;
    }

    /**
     * @method detailBy
     * @auth: ledung
     * @param  array $conditions
     * ['name'=>value, 'id'=> 1]
     * @return array
     */
    public function detailBy($conditions, array $ext = array())
    {
        $whereStr = '1 = 1';
        $whereArr = array();
        foreach ($conditions as $key => $value) {
            $whereStr .= " AND {$key} = :{$key}:";
            $whereArr[$key] = $value;
        }

        $params = array(
            'conditions' => $whereStr,
            'bind'       => $whereArr,
        );

        print_r($params);

        if (isset($ext['columns']) && !empty($ext['columns'])) {
            $params['columns'] = $ext['columns'];
        }

        $result = $this->findFirst($params);
        var_dump($result);
        return $result;
    }

    /**
     *     Is executed before the fields are validated for not nulls/empty strings
     *  or foreign keys when an insertion operation is being made
     */
    public function beforeValidationOnCreate()
    {
        $this->create_by = $this->_user['uid'];
        if (empty($this->create_time) || !strtotime($this->create_time)) {
            $this->create_time = date('Y-m-d H:i:s');
        }
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
        if (!is_array($data) || count($data) == 0) {
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
     * @param  int           $accessID
     * @return int
     */
    public function update_record(array $data, $accessID)
    {
        $accessID = intval($accessID);
        if ($accessID <= 0 || !is_array($data) || count($data) == 0) {
            throw new \Exception('Parameter error');
        }
        $data = $this->before_update($data);

        $this->id = $accessID;
        $result   = $this->update($data);
        if (!$result) {
            throw new \Exception('Update failed');
        }
        $affectedRows = $this->db->affectedRows();
        return $affectedRows;
    }

    /**
     * @method get_count
     * @auth: ledung
     * @param  integer   $status
     * @return int
     */
    public function get_count($status = 1)
    {
        $status = intval($status);
        $count  = $this->count(array(
            'conditions' => 'active = :status:',
            'bind'       => array(
                'active' => $status,
            ),
        ));
        return $count;
    }

    /**
     * @method delete_record
     * @auth: ledung
     * @param  int $profileID
     * @return bool
     */
    public function delete_record($profileID)
    {
        echo $profileID = intval($profileID);
        if ($profileID <= 0) {
            throw new \Exception('Parameter error');
        }
        $phql   = "DELETE FROM " . __CLASS__ . " WHERE id_profile = :profileID: ";
        $result = $this->getModelsManager()->executeQuery($phql, array(
            'profileID' => $profileID
        ));
        if (!$result->success()) {
            throw new \Exception(implode(',', $result->getMessages()));
        }

        return $result;
    }

    /**
     * @method get_access_list
     * @auth: ledung
     * @param  array   $page
     * @param  integer  $pagesize
     * @param  array    $ext
     * @return mix
     */
    public function get_access_list($profileID)
    {
        $builder = $this->getModelsManager()->createBuilder();
        $builder->from(array('a' => __CLASS__));
        $builder->columns(array('a.id_module', 'a.is_view', 'a.is_add', 'a.is_edit', 'a.is_delete'));
        $result = $builder->where("a.id_profile = $profileID")
            ->orderBy('a.id ASC')
            ->getQuery()
            ->execute();
        if (!$result) {
            throw new \Exception('The access failed');
        }
        return $result;
    }
}
