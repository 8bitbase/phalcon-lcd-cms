<?php

/**
 * ActiclesController
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Controllers;

use \Lcd\App\Backend\Controllers\BaseController;
use \Lcd\App\Helpers\PaginatorHelper;

class UsersController extends BaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->access_check('UsersController');
    }

    /**
     * @method indexAction
     * @auth: ledung
     */
    public function indexAction()
    {
        $page    = intval($this->request->get('page', 'trim'));
        $keyword = $this->request->get('keyword', 'trim');
        // Note
        $pagesize  = $this->_cons['page_default_number'];
        $paginator = $this->get_repository('Users')->get_list($page, $pagesize, array(
            'keyword' => $keyword,
            'current_username' => $this->session->get('user')['username']
        ));

        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);

        $users = $paginator->items->toArray();

        $this->view->setVars(array(
            'paginator' => $paginator,
            'pageNum'   => $pageNum,
            'keyword'   => $keyword,
            'users'   => $users,
        ));
        $this->view->pick('pages/users/index');
    }

    /**
     * @method writeAction
     * @auth: ledung
     */
    public function writeAction()
    {
        $username = $this->request->get('username', 'trim');
        $profile = $this->get_repository('profile')->get_profile_list();
        // Note
        $users = array();
        if ($username != '') {
            $users = $this->get_repository('Users')->detail($username);
        }

        $this->view->setVars(array(
            'users' => $users,
            'profile' => $profile
        ));

        $this->view->pick('pages/users/write');
    }

    /**
     * @method publishAction
     * @auth: ledung
     */
    public function publishAction()
    {
        try {
            if ($this->request->isAjax() || !$this->request->isPost()) {
                throw new \Exception('Illegal request');
            }

            $data = array();
            $data['uid'] = intval($this->request->getPost('usersID', 'trim'));
            $data['username']      = $this->request->getPost('username', 'trim');
            $data['realname']      = $this->request->getPost('realname', 'trim');
            $data['phone']      = $this->request->getPost('phone', 'trim');
            $data['email']      = $this->request->getPost('email', 'trim');
            $data['status']    = intval($this->request->getPost('status', 'trim'));
            $data['id_profile']    = intval($this->request->getPost('id_profile', 'trim'));
            // $data['password']    = $this->getDI()->get('security')->hash('123456789');

            // Note
            $this->validator->add_rule('username', 'required', $this->_('validate'));

            // Note
            if ($error = $this->validator->run($data)) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            // Note
            $this->get_repository('Users')->save($data, $data['uid']);

            $this->flashSession->success($this->_('success.publish'));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect('users/index');
    }

    /**
     * @method savepwdAction
     * @auth: ledung
     */
    public function savepwdAction()
    {
        try {
            if ($this->request->isAjax() || !$this->request->isPost()) {
                throw new \Exception('Illegal request');
            }

            $input['newpwd']     = $this->request->getPost('newpwd', 'trim');
            $input['confirmpwd'] = $this->request->getPost('confirmpwd', 'trim');
            // Validate -> show message
            $this->validator->add_rule('newpwd', 'required', $this->_('validate'))
                ->add_rule('newpwd', 'min_length', $this->_('validate.min', 6), 6)
                ->add_rule('newpwd', 'max_length', $this->_('validate.max', 20), 20)
                ->add_rule('newpwd', 'equals', $this->_('validate.equals'), $input['confirmpwd']);
            if ($error = $this->validator->run($input)) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }

            $username = $this->request->getPost('username', 'trim');
            $data = $this->get_repository('Users')
                        ->detail($username)
                        ->toArray();
            $data['password_hint'] = $input['newpwd'];
            $data['password'] = $this->getDI()->get('security')->hash($input['newpwd']);

            $this->get_repository('Users')->save($data, $data['uid']);

            $this->flashSession->success($this->_('success.change-pass'));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect('users/index');
    }

    /**
     * @method deleteAction
     * @auth: ledung
     */
    public function deleteAction()
    {
        try {
            $username = $this->session->get('user')['username'];
            $user     = $this->get_repository('Users')->detail($username);
            // If permission is supper admin is delete account
            if ($user->id_profile == 1) {
                $usersID    = intval($this->request->get('usersID', 'trim'));
                $affectedRows = $this->get_repository('Users')->delete($usersID);
                if (!$affectedRows) {
                    throw new \Exception($this->_('fail.delete'));
                }
                $this->flashSession->success($this->_('success.delete'));
            } else {
                $this->flashSession->success($this->_('fail.delete'));
            }
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect();
    }

}
