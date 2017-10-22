<?php

/**
 * MenuController
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Controllers;

use \Lcd\App\Backend\Controllers\BaseController;

class MenuController extends BaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->access_check('MenuController');
    }

    /**
     * @method indexAction
     * @auth: ledung
     * @return mix
     */
    public function indexAction()
    {
        try {
            // Note
            $menuList = $this->get_repository('Menu')->get_menu_list();
            $this->view->setVars(array(
                'menuList' => $menuList,
            ));
            $this->view->pick('pages/menu/index');
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());

            return $this->redirect();
        }
    }

    /**
     * @method writeAction
     * @auth: ledung
     * @return mix
     */
    public function writeAction()
    {
        try {
            $id       = intval($this->request->get('id', 'trim'));
            $parentid = intval($this->request->get('parentid', 'trim'));
            $menu      = array();
            if ($id > 0) {
                // Note
                $menu = $this->get_repository('Menu')->detail($id);
            }
            // Note
            $menuList = $this->get_repository('Menu')->get_menu_list();
            
            $categoryList = $this->get_repository('Categories')->get_category_list();

            $this->view->setVars(array(
                'menu'      => $menu,
                'parentid' => $parentid,
                'menuList'  => $menuList,
                'categoryList' => $categoryList
            ));
            $this->view->pick('pages/menu/write');
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());

            return $this->redirect();
        }
    }

    /**
     * @method saveAction
     * @auth: ledung
     * @return mix
     */
    public function saveAction()
    {
        try {
            if ($this->request->isAjax() || !$this->request->isPost()) {
                throw new \Exception('Illegal request');
            }
            $id = intval($this->request->getPost('id', 'trim'));
            $dataPost = $this->request->getPost();
        
            // Note
            $this->validator->add_rule('name', 'required', $this->_('validate'));
            // Note
            if ($error = $this->validator->run($dataPost)) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            // Note
            $this->get_repository('Menu')->save($dataPost, $id);
            $this->flashSession->success($this->_('success.save'));

            return $this->redirect('menu/index');
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());

            $url = 'menu/write';
            !empty($id) && $url .= "?id={$id}";
            return $this->redirect($url);
        }
    }

    /**
     * @method refreshAction
     * @auth: ledung
     * @return mix
     */
    public function refreshAction()
    {
        if ($this->get_repository('Menu')->delete_menu_list_cache()) {
            $this->flashSession->success($this->_('success.clear_cache'));
        } else {
            $this->flashSession->error($this->_('fail.clear_cache'));
        }
        return $this->redirect();
    }

    /**
     * @method savesortAction
     * @auth: ledung
     * @return mix
     */
    public function savesortAction()
    {
        try {
            $id  = intval($this->request->get('id', 'trim'));
            $sort = intval($this->request->get('sort', 'trim'));

            $affectedRows = $this->get_repository('Menu')->update_record(array(
                'sort' => $sort,
            ), $id);
            if (!$affectedRows) {
                throw new \Exception($this->_('fail.update') . ': savesortAction');
            }

            $this->ajax_return($this->_('success.update'));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->ajax_return($e->getMessage());
        }
        $this->view->disable();
    }

    /**
     * @method deleteAction
     * @auth: ledung
     * @return mix
     */
    public function deleteAction()
    {
        try {
            $id = intval($this->request->get('id', 'trim'));
            $this->get_repository('Menu')->delete($id);

            $this->flashSession->success($this->_('success.delete'));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect();
    }
}
