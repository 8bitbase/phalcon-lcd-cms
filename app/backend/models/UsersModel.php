<?php

/**
 * UsersModel
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Models;

use \Lcd\App\Backend\Models\BaseModel;
use \Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class UsersModel extends BaseModel
{
    const TABLE_NAME = 'users';

    public function initialize()
    {
        parent::initialize();
        $this->set_table_source(self::TABLE_NAME);
    }

    /**
     * @method detail
     * @auth: ledung
     * @param  $username
     * @param  array  $ext
     * @return \Phalcon\Mvc\Model
     */
    public function detail($username, array $ext = array())
    {
        if (empty($username)) {
            throw new \Exception('Parameter error');
        }
        $params = array(
            'conditions' => 'username = :username:',
            'bind'       => array(
                'username' => $username,
            ),
        );
        if (isset($ext['columns']) && !empty($ext['columns'])) {
            $params['columns'] = $ext['columns'];
        }
        $result = $this->findFirst($params);
        if (!$result) {
            throw new \Exception('Failed to get user information');
        }
        return $result;
    }

    /**
     * @method before_update
     * @auth: ledung
     * @param  array         $data
     * @return array
     */
    protected function before_update(array $data)
    {
        if (empty($data['modify_time'])) {
            $data['modify_time'] = date('Y-m-d H:i:s');
        }
        return $data;
    }

    /**
     * @method update_record
     * @auth: ledung
     * @param  array         $data
     * @param  $uid
     * @return int
     */
    public function update_record(array $data, $uid)
    {
        $uid = intval($uid);
        if (count($data) == 0 || $uid <= 0) {
            throw new \Exception('Parameter error');
        }
        $data = $this->before_update($data);

        $this->uid = $uid;
        $result    = $this->update($data);
        if (!$result) {
            throw new \Exception('Update failed');
        }
        $affectedRows = $this->db->affectedRows();
        return $affectedRows;
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
        $builder->columns(array('a.uid', 'a.username', 'a.realname', 'a.status', 'a.create_by', 'a.create_time', 'a.modify_time'));
        $builder->andWhere("a.id_profile != 1");
        $builder->andWhere("a.username != :current_username:", array('current_username' => "%{$ext['current_username']}%"));
        if (isset($ext['keyword']) && !empty($ext['keyword'])) {
            $builder->andWhere("a.username like :username:", array('username' => "%{$ext['keyword']}%"));
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
        $usersID = $this->usersID;
        return $usersID;
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
     * @param  int $usersID
     * @return bool
     */
    public function delete_record($usersID)
    {
        $usersID = intval($usersID);
        if ($usersID <= 0) {
            throw new \Exception('Parameter error');
        }
        $phql   = "DELETE FROM " . __CLASS__ . " WHERE uid = :usersID: ";
        $result = $this->getModelsManager()->executeQuery($phql, array(
            'usersID' => $usersID,
        ));
        if (!$result->success()) {
            throw new \Exception(implode(',', $result->getMessages()));
        }
        return $result;
    }
}
