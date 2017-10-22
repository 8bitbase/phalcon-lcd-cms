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

class ProfileController extends BaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->access_check('ProfileController');
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
        $paginator = $this->get_repository('Profile')->get_list($page, $pagesize, array(
            'keyword' => $keyword,
        ));

        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);

        $profile = $paginator->items->toArray();

        $this->view->setVars(array(
            'paginator' => $paginator,
            'pageNum'   => $pageNum,
            'keyword'   => $keyword,
            'profile'   => $profile,
        ));
        $this->view->pick('pages/profile/index');
    }

    /**
     * @method writeAction
     * @auth: ledung
     */
    public function writeAction()
    {
        $profileID = intval($this->request->get('profileID', 'trim'));
        // Note
        $profile = array();
        if ($profileID > 0) {
            $profile = $this->get_repository('Profile')->detail($profileID);
        }

        $modules = $this->get_repository('Profile')->get_modules_list($profileID);

        $this->view->setVars(array(
            'profile' => $profile,
            'modules' => $modules
        ));

        $this->view->pick('pages/profile/write');
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
            $profileID = intval($this->request->getPost('profileID', 'trim'));
            $name      = $this->request->getPost('name', 'trim');
            $status    = intval($this->request->getPost('status', 'trim'));

            // Note
            $this->validator->add_rule('name', 'required', $this->_('validate'));

            // Note
            if ($error = $this->validator->run(array(
                'name' => $name,
            ))) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            // Note
            $this->get_repository('Profile')->save(array(
                'name'   => $name,
                'status' => $status,
            ), $profileID);

            $this->flashSession->success($this->_('success.publish'));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect('profile/index');
    }

    /**
     * @method accessAction
     * @auth: ledung
     */
    public function accessAction()
    {
        try {
            if ($this->request->isAjax() || !$this->request->isPost()) {
                throw new \Exception('Illegal request');
            }
            $data['profileID'] = intval($this->request->getPost('profileID', 'trim'));
            $data['module'] = $this->request->getPost('module');

            // Note
            $this->get_repository('Access')->save($data);

            $this->flashSession->success($this->_('success.publish'));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect('profile/index');
    }

    /**
     * @method deleteAction
     * @auth: ledung
     */
    public function deleteAction()
    {
        try {
            $profileID    = intval($this->request->get('profileID', 'trim'));
            $affectedRows = $this->get_repository('Profile')->delete($profileID);
            if (!$affectedRows) {
                throw new \Exception($this->_('fail.delete'));
            }
            $this->flashSession->success($this->_('success.delete'));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect();
    }

}
