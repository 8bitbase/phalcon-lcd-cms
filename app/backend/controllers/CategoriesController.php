<?php

/**
 * CategoriesController
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Controllers;

use \Lcd\App\Backend\Controllers\BaseController;

class CategoriesController extends BaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->access_check('CategoriesController');
    }

    /**
     * Category List
     */
    public function indexAction()
    {
        $categoryList = $this->get_repository('Categories')->get_category_list();
        $this->view->setVars(array(
            'categoryList' => $categoryList,
        ));
        $this->view->pick('pages/categories/index');
    }

    /**
     * Add a category page
     */
    public function writeAction()
    {
        $id     = intval($this->request->get('id', 'trim'));
        $parent = intval($this->request->get('parent', 'trim'));

        $categoryList = $this->get_repository('Categories')->get_category_list();
        // Note
        $category = array();
        if ($id > 0) {
            $category = $this->get_repository('Categories')->detail($id);
        }

        $this->view->setVars(array(
            'id'           => $id,
            'parent'       => $parent,
            'categoryList' => $categoryList,
            'category'     => $category,
        ));
        $this->view->pick('pages/categories/write');
    }

    /**
     * Save the classification
     */
    public function saveAction()
    {
        try {
            if ($this->request->isAjax() || !$this->request->isPost()) {
                throw new \Exception('Illegal request');
            }
            // print_r($this->request->getPost());
            // die;
            $id                      = intval($this->request->get('id', 'trim'));
            $dataPost                = $this->request->getPost();
            $dataPost['description'] = $this->request->getPost('description', 'remove_xss');
            $dataPost['sort']        = intval($this->request->getPost('sort', 'trim'));
            $dataPost['parent']      = intval($this->request->getPost('parent', 'trim'));
            // Note
            $this->validator->add_rule('name', 'required', $this->_('validate'));
            $this->validator->add_rule('slug', 'alpha_dash', $this->_('validate.alpha_numeric_dash'));
            // Note
            if ($error = $this->validator->run($dataPost)) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            // Note
            $result = $this->get_repository('Categories')->save($dataPost, $id);

            $this->flashSession->success($this->_('success.save'));
            return $this->redirect('categories/index');
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());

            $url = 'categories/write';
            !empty($id) && $url .= "?id={$id}";
            return $this->redirect($url);
        }
    }

    /**
     * Update sorting
     */
    public function savesortAction()
    {
        try {
            $id   = intval($this->request->getPost('id', 'trim'));
            $sort = intval($this->request->getPost('sort', 'trim'));
            $this->get_repository('Categories')->update_sort($sort, $id);

            $this->ajax_return($this->_('success.update'));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->ajax_return($e->getMessage());
        }
        $this->view->disable();
    }

    /**
     * Delete category (soft delete)
     */
    public function deleteAction()
    {
        try {
            $id = $this->request->get('id', 'trim');
            $this->get_repository('Categories')->delete($id);

            $this->flashSession->success($this->_('success.delete'));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect();
    }

    /**
     * Clear the categorization cache
     */
    public function refreshAction()
    {
        if ($this->get_repository('Categories')->delete_category_list_cache()) {
            $this->flashSession->success($this->_('success.clear_cache'));
        } else {
            $this->flashSession->error($this->_('fail.clear_cache'));
        }

        return $this->redirect();
    }
}
