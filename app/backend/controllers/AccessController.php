<?php

/**
 * AccessController
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Controllers;

use \Lcd\App\Backend\Controllers\BaseController;
use \Lcd\App\Helpers\PaginatorHelper;

class AccessController extends BaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->access_check('AccessController');
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
        $paginator = $this->get_repository('Access')->get_list($page, $pagesize, array(
            'keyword' => $keyword,
        ));
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);

        $access = $paginator->items->toArray();
        $this->view->setVars(array(
            'paginator' => $paginator,
            'pageNum'   => $pageNum,
            'keyword'   => $keyword,
            'access'   => $access,
        ));
        $this->view->pick('access/index');
    }

    /**
     * @method writeAction
     * @auth: ledung
     */
    public function writeAction()
    {
        $accessID = intval($this->request->get('accessID', 'trim'));
        // Note
        $access = array();
        if ($accessID > 0) {
            $access = $this->get_repository('Access')->detail($accessID);
        }

        $this->view->setVars(array(
            'access' => $access,
        ));

        $this->view->pick('pages/access/write');
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
            $accessID = intval($this->request->getPost('accessID', 'trim'));
            $data['id']      = intval($this->request->getPost('id', 'trim'));
            $data['name']      = $this->request->getPost('name', 'trim');
            $data['version']      = $this->request->getPost('version', 'trim');
            $data['active']    = intval($this->request->getPost('status', 'trim'));

            // Note
            $this->validator->add_rule('name', 'required', $this->_('validate'));

            // Note
            if ($error = $this->validator->run($data)) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            // Note
            $this->get_repository('Access')->save($data, $accessID);

            $this->flashSession->success($this->_('success.publish'));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect('pages/access/index');
    }

    /**
     * @method deleteAction
     * @auth: ledung
     */
    public function deleteAction()
    {
        try {
            $accessID    = intval($this->request->get('accessID', 'trim'));
            $affectedRows = $this->get_repository('Access')->delete($accessID);
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
