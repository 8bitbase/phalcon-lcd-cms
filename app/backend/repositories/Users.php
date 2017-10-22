<?php

/**
 * User business logic
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Repositories;

use \Lcd\App\Backend\Repositories\BaseRepository;

class Users extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @method login_check
     * @auth: ledung
     * @return bool
     */
    public function login_check()
    {
        if ($this->getDI()->get('session')->has('user')) {
            if (!empty($this->getDI()->get('session')->get('user')['uid'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * @method login
     * @auth: ledung
     * @param  $username
     * @param  $password
     * @return mix
     */
    public function login($username, $password)
    {
        // Note
        $user = $this->detail($username);
        if (!$user) {
            throw new \Exception('username or password wrong');
        }
        $userinfo = $user->toArray();
        // Note
        if (!$this->getDI()->get('security')->checkHash($password, $userinfo['password'])) {
            throw new \Exception('username or password is wrong, please re-enter');
        }
        // Note
        unset($userinfo['password']);
        $accessData = $this->get_model('AccessModel')->get_access_list($userinfo['id_profile']);
        if ($accessData) {
            $accessData = $accessData->toArray();
            $accessUser = array();

            foreach ($accessData as $value) {
                $key = $value['id_module'];
                $accessUser[$key] = $value;
            }

            $userinfo['access'] = $accessUser;
        }
        $this->getDI()->get('session')->set('user', $userinfo);
    }

    /**
     * @method update_password
     * @auth: ledung
     * @param  $oldpwd
     * @param  $newpwd
     * @return mix
     */
    public function update_password($oldpwd, $newpwd)
    {
        // Note
        $user = $this->detail($this->getDI()->get('session')->get('user')['username']);
        if (!$user) {
            throw new \Exception('wrong password');
        }
        $userinfo = $user->toArray();
        if (!$this->getDI()->get('security')->checkHash($oldpwd, $userinfo['password'])) {
            throw new \Exception('Wrong password, please re-enter
');
        }
        // Note
        $password     = $this->getDI()->get('security')->hash($newpwd);
        $affectedRows = $this->get_model('UsersModel')->update_record(array(
            'password' => $password,
        ), $this->getDI()->get('session')->get('user')['uid']);
        if (!$affectedRows) {
            throw new \Exception('change password failed, please try again');
        }
        return $affectedRows;
    }

    /**
     * @method update
     * @auth: ledung
     * @param  $data
     * @param  $uid
     * @return mix
     */
    public function update(array $data, $uid)
    {
        $uid = intval($uid);
        if ($uid <= 0) {
            throw new \Exception('Parameter error');
        }
        $affectedRows = $this->get_model('UsersModel')->update_record($data, $uid);
        if (!$affectedRows) {
            throw new \Exception('Modify personal settings failed');
        }
        return true;
    }

    /**
     * @method detail
     * @auth: ledung
     * @param  $username
     * @param  $ext
     * @return \Phalcon\Mvc\Model
     */
    public function detail($username, array $ext = array())
    {
        $user = $this->get_model('UsersModel')->detail($username, $ext);
        if (!$user->uid) {
            throw new \Exception('Failed to get user information');
        }
        return $user;
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
        $paginator = $this->get_model('UsersModel')->get_list($page, $pagesize, $ext);
        return $paginator;
    }

    /**
     * @method update_record
     * @auth: ledung
     * @param  $data
     * @param  $usersID
     * @return int
     */
    public function update_record(array $data, $usersID)
    {
        $affectedRows = $this->get_model('UsersModel')->update_record($data, $usersID);
        $affectedRows = intval($affectedRows);
        return $affectedRows;
    }

    /**
     * @method save
     * @auth: ledung
     * @param  $data
     * @param  $usersID
     * @return mix
     */
    public function save(array $data, $usersID = null)
    {
        if (empty($usersID)) {
            // Note
            $this->create($data);
        } else {
            // Note
            $this->update($data, $usersID);
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
            $usersID = $this->create_users($data);
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
        $count = $this->get_model('UsersModel')->get_count();
        return $count;
    }

    /**
     * @method delete
     * @auth: ledung
     * @param  $usersID
     * @return mix
     */
    public function delete($usersID)
    {
        $affectedRows = $this->get_model('UsersModel')->delete_record($usersID);
        $affectedRows = intval($affectedRows);
        return $affectedRows;
    }

    /**
     * @method create_users
     * @auth: ledung
     * @param  $data
     * @return bool|int
     */
    protected function create_users(array $data)
    {
        $usersID = $this->get_model('UsersModel')->insert_record($data);
        return $usersID;
    }

    /**
     * @method update_users
     * @auth: ledung
     * @param  $data
     * @param  $usersID
     * @return int
     */
    protected function update_users(array $data, $usersID)
    {
        $affectedRows = $this->get_model('UsersModel')->update_record(array(
            'name'   => $data['name'],
            'status' => $data['status'],
        ), $usersID);
        return $affectedRows;
    }
}
