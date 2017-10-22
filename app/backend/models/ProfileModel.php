<?php

/**
 * ProfileModel
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Models;

use \Lcd\App\Backend\Models\BaseModel;
use \Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class ProfileModel extends BaseModel
{
    const TABLE_NAME = 'profile';

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
        $page                                           = intval($page);
        $page <= 0 && $page                             = 1;
        $pagesize                                       = intval($pagesize);
        

        $builder = $this->getModelsManager()->createBuilder();
        $builder->from(array('a' => __CLASS__));
        $builder->columns(array('a.id', 'a.name', 'a.status', 'a.create_by', 'a.create_time', 'a.modify_time'));
        if (isset($ext['keyword']) && !empty($ext['keyword'])) {
            $builder->andWhere("a.name like :name:", array('name' => "%{$ext['keyword']}%"));
        }
        $builder->orderBy('a.create_time DESC');

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
     * @param  $profileID
     * @return mix
     */
    public function detail($profileID)
    {
        $profileID = intval($profileID);
        if ($profileID <= 0) {
            throw new Exception('Parameter error');
        }
        $builder = $this->getModelsManager()->createBuilder();
        $builder->from(array('a' => __CLASS__));
        $builder->columns(array('a.id', 'a.name', 'a.status', 'a.create_by', 'a.create_time'));
        $result = $builder->where("a.id = :profileID:", array('profileID' => $profileID))
            ->limit(1)
            ->getQuery()
            ->execute();
        if (!$result) {
            throw new \Exception('Get data failed');
        }
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
        $result = $this->create($data);
        if (!$result) {
            throw new \Exception(implode(',', $this->getMessages()));
        }
        $profileID = $this->profileID;
        return $profileID;
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
     * @param  int           $profileID
     * @return int
     */
    public function update_record(array $data, $profileID)
    {
        $profileID = intval($profileID);
        if ($profileID <= 0 || !is_array($data) || count($data) == 0) {
            throw new \Exception('Parameter error');
        }
        $data = $this->before_update($data);

        $this->id = $profileID;
        $result          = $this->update($data);
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
            'conditions' => 'status = :status:',
            'bind'       => array(
                'status' => $status,
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
        $profileID = intval($profileID);
        if ($profileID <= 0) {
            throw new \Exception('Parameter error');
        }
        $phql   = "DELETE FROM " . __CLASS__ . " WHERE id = :profileID: ";
        $result = $this->getModelsManager()->executeQuery($phql, array(
            'profileID' => $profileID,
        ));
        if (!$result->success()) {
            throw new \Exception(implode(',', $result->getMessages()));
        }
        return $result;
    }

    /**
     * @method get_profile_list
     * @auth: ledung
     * @param  array   $page
     * @param  integer  $pagesize
     * @param  array    $ext
     * @return mix
     */
    public function get_profile_list()
    {
        $builder = $this->getModelsManager()->createBuilder();
        $builder->from(array('a' => __CLASS__));
        $builder->columns(array('a.id', 'a.name'));
        $result = $builder->where("a.status = 1")
                ->orderBy('a.create_time DESC')
                ->getQuery()
                ->execute();
        if (!$result) {
            throw new \Exception('The profile failed');
        }
        return $result;
    }
}
