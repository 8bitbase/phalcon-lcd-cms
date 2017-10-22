<?php

/**
 * TagsController
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Controllers;

use \Lcd\App\Backend\Controllers\BaseController;

class TagsController extends BaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->access_check('TagsController');
    }

    /**
     * @method indexAction
     * @auth: ledung
     * @return mix
     */
    public function indexAction()
    {
        $id      = intval($this->request->get('id', 'trim'));
        $taginfo = null;
        if ($id > 0) {
            $taginfo = $this->get_repository('Tags')->detail($id);
        }
        $tagsList = $this->get_repository('Tags')->get_list();

        $this->view->setVars(array(
            'taginfo'  => $taginfo,
            'tagsList' => $tagsList,
        ));
        $this->view->pick('pages/tags/index');
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
            $id               = intval($this->request->getPost('id', 'trim'));
            $dataPost         = array();
            $dataPost['name'] = $this->request->getPost('name', 'trim');
            $dataPost['slug'] = $this->request->getPost('slug', 'trim');
            // Note
            $this->validator->add_rule('name', 'required', $this->_('validate'));
            $this->validator->add_rule('slug', 'alpha_dash', $this->_('validate.alpha_dash'));
            // Note
            if ($error = $this->validator->run($dataPost)) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            // Note
            $result = $this->get_repository('Tags')->save($dataPost, $id);

            $this->flashSession->success($this->_('success.save'));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect('tags/index');
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

            $this->get_repository('Tags')->delete($id);

            $this->flashSession->success($this->_('success.save'));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect();
    }
}
