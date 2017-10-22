<?php

/**
 * ModulesController
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Controllers;

use \Lcd\App\Backend\Controllers\BaseController;
use \Lcd\App\Helpers\PaginatorHelper;

class ModulesController extends BaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->access_check('ModulesController');
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
        $paginator = $this->get_repository('Modules')->get_list($page, $pagesize, array(
            'keyword' => $keyword,
        ));
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);

        $modules = $paginator->items->toArray();
        $this->view->setVars(array(
            'paginator' => $paginator,
            'pageNum'   => $pageNum,
            'keyword'   => $keyword,
            'modules'   => $modules,
        ));
        $this->view->pick('pages/modules/index');
    }

    /**
     * @method writeAction
     * @auth: ledung
     */
    public function writeAction()
    {
        $modulesID = intval($this->request->get('modulesID', 'trim'));
        // Note
        $modules = array();
        if ($modulesID > 0) {
            $modules = $this->get_repository('Modules')->detail($modulesID);
        }

        $this->view->setVars(array(
            'modules' => $modules,
        ));

        $this->view->pick('pages/modules/write');
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
            $modulesID = intval($this->request->getPost('modulesID', 'trim'));
            $data['id']      = intval($this->request->getPost('id', 'trim'));
            $data['display']      = $this->request->getPost('display', 'trim');
            $data['version']      = $this->request->getPost('version', 'trim');
            $data['active']    = intval($this->request->getPost('active', 'trim'));

            // Note
            $this->validator->add_rule('display', 'required', $this->_('validate'));

            // Note
            if ($error = $this->validator->run($data)) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            // Note
            $this->get_repository('Modules')->save($data, $modulesID);

            $this->flashSession->success($this->_('success.publish'));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect('modules/index');
    }

    /**
     * @method resetAction
     * @auth: ledung
     * @return array
     */
    public function resetAction()
    {
        try {
            $controllers = $this->getAllControllers();
            foreach ($controllers as $key => $controller) {
                // Check exist module
                $conditions = array('name' => $controller);
                $module = $this->get_repository('Modules')->detail(null,$conditions);
                if ($module == false) {
                    // Import module if not exist
                    $data['name']      = $controller;
                    $data['version']   = 1;
                    $data['active']    = 1;
                    $this->get_repository('Modules')->save($data);
                }
            }
            $this->flashSession->success($this->_('success.publish'));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect('modules/index');
    }

    /**
     * @method deleteAction
     * @auth: ledung
     */
    public function deleteAction()
    {
        try {
            $modulesID    = intval($this->request->get('modulesID', 'trim'));
            $affectedRows = $this->get_repository('Modules')->delete($modulesID);
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
