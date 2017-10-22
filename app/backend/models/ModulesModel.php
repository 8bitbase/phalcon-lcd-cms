<?php

/**
 * ModulesModel
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Models;

use \Lcd\App\Backend\Models\BaseModel;
use \Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class ModulesModel extends BaseModel
{
    const TABLE_NAME = 'modules';

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
        $builder->columns(array('a.id', 'a.display', 'a.active', 'a.version'));
        if (isset($ext['keyword']) && !empty($ext['keyword'])) {
            $builder->andWhere("a.display like :display:", array('display' => "%{$ext['keyword']}%"));
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
     * @param  $modulesID
     * @return mix
     */
    public function detail($modulesID, array $ext = array())
    {
        $modulesID = intval($modulesID);
        if ($modulesID <= 0) {
            throw new Exception('Parameter error');
        }
        // $builder = $this->getModelsManager()->createBuilder();
        // $builder->from(array('a' => __CLASS__));
        // $builder->columns(array('a.id', 'a.name', 'a.active', 'a.version'));
        // $result = $builder->where("a.id = :modulesID:", array('modulesID' => $modulesID))
        //     ->limit(1)
        //     ->getQuery()
        //     ->execute();

        $params = array(
            'conditions' => "id = :modulesID:",
            'bind'       => array('modulesID' => $modulesID),
        );
        if (isset($ext['columns']) && !empty($ext['columns'])) {
            $params['columns'] = $ext['columns'];
        }
        $result = $this->findFirst($params);

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

        if (isset($ext['columns']) && !empty($ext['columns'])) {
            $params['columns'] = $ext['columns'];
        }

        $result = $this->findFirst($params);
        
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
        $modulesID = $clone->id;
        return $modulesID;
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
     * @param  int           $modulesID
     * @return int
     */
    public function update_record(array $data, $modulesID)
    {
        $modulesID = intval($modulesID);
        if ($modulesID <= 0 || !is_array($data) || count($data) == 0) {
            throw new \Exception('Parameter error');
        }
        $data = $this->before_update($data);

        $this->id = $modulesID;
        $result   = $this->iupdate($data);
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
     * @method get_count_by_name
     * @auth: ledung
     * @param  string   $name
     * @return int
     */
    public function get_count_by_name($name)
    {
        $count  = $this->count(array(
            'conditions' => 'name = :name:',
            'bind'       => array(
                'name' => $name,
            ),
        ));
        return $count;
    }

    /**
     * @method delete_record
     * @auth: ledung
     * @param  int $modulesID
     * @return bool
     */
    public function delete_record($modulesID)
    {
        $modulesID = intval($modulesID);
        if ($modulesID <= 0) {
            throw new \Exception('Parameter error');
        }
        $phql   = "DELETE FROM " . __CLASS__ . " WHERE id = :modulesID: ";
        $result = $this->getModelsManager()->executeQuery($phql, array(
            'modulesID' => $modulesID,
        ));
        if (!$result->success()) {
            throw new \Exception(implode(',', $result->getMessages()));
        }
        return $result;
    }

    /**
     * @method get_modules_list
     * @auth: ledung
     * @param  array   $page
     * @param  integer  $pagesize
     * @param  array    $ext
     * @return mix
     */
    public function get_modules_list()
    {
        $builder = $this->getModelsManager()->createBuilder();
        $builder->from(array('a' => __CLASS__));
        $builder->columns(array('a.id', 'a.name', 'a.display'));
        $result = $builder->where("a.active = 1")
            ->orderBy('a.id DESC')
            ->getQuery()
            ->execute();
        if (!$result) {
            throw new \Exception('The modules failed');
        }
        return $result;
    }
}
