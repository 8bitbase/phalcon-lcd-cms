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

class ArticlesController extends BaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->access_check('ArticlesController');
    }

    /**
     * @method indexAction
     * @auth: ledung
     */
    public function indexAction()
    {
        $page        = intval($this->request->get('page', 'trim'));
        $id_category = intval($this->request->get('id_category', 'trim'));
        $keyword     = $this->request->get('keyword', 'trim');
        // Note
        // $pagesize  = $this->_cons['page_default_number'];
        $paginator = $this->get_repository('Articles')->get_list(1, 20000, array(
            'id_category' => $id_category,
            'keyword'     => $keyword,
        ));

        // $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);

        $articles = $paginator->items->toArray();
        if (is_array($articles) && count($articles) > 0) {
            $ids        = array_column($articles, 'id');
            $categories = $this->get_repository('Articles')->get_categories_by_ids($ids);
            foreach ($categories as $category) {
                foreach ($articles as &$article) {
                    if ($category['id_article'] == $article['id']) {
                        $article['categories'][] = array(
                            'id'   => $category['id_category'],
                            'name' => $category['name'],
                        );
                    }
                }
            }
        }

        $categoryList = $this->get_repository('Categories')->get_category_list();
        $this->view->setVars(array(
            // 'paginator'    => $paginator,
            // 'pageNum'      => $pageNum,
            'id_category'  => $id_category,
            'keyword'      => $keyword,
            'articles'     => $articles,
            'categoryList' => $categoryList,

        ));
        $this->view->pick('pages/articles/index');
    }

    /**
     * @method writeAction
     * @auth: ledung
     */
    public function writeAction()
    {
        $id  = intval($this->request->get('id', 'trim'));
        $copy = 0;
        if (intval($this->request->get('copy', 'trim'))) {
            $copy = 1;
        }

        $ext = array(
            'link' => $this->cons->link->articles,
        );
        // Note
        $categoryList = $this->get_repository('Categories')->get_category_list();
        // Note
        $article = array();
        if ($id > 0) {
            $article = $this->get_repository('Articles')->detail($id, $ext);
        }
        $this->view->setVars(array(
            'copy' => $copy,
            'categoryList' => $categoryList,
            'article'      => $article,
        ));
        $this->view->pick('pages/articles/write');
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
            $id       = intval($this->request->getPost('id', 'trim'));
            $dataPost = $this->request->getPost();

            if ($this->request->hasFiles() == true) {
                $dataPost['image']['file'] = $this->request->getUploadedFiles();
                $dataPost['image']['path'] = $this->cons->path->articles;
                $dataPost['image']['size'] = $this->cons->imageSize;
            }
            // Note
            $this->validator->add_rule('title', 'required', $this->_('validate'));
            $this->validator->add_rule('category', 'required', $this->_('validate'));
            $this->validator->add_rule('tags', 'required', $this->_('validate'));
            $this->validator->add_rule('create_time', 'check_time', $this->_('validate.check_time'));
            // Note
            if ($error = $this->validator->run($dataPost)) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            // Note
            $this->get_repository('Articles')->save($dataPost, $id);
            $this->flashSession->success($this->_('success.publish'));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect('articles/index');
    }

    /**
     * @method deleteAction
     * @auth: ledung
     */
    public function deleteAction()
    {
        try {
            $id           = intval($this->request->get('id', 'trim'));
            $affectedRows = $this->get_repository('Articles')->delete($id);
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

    /**
     * @method topAction
     * @auth: ledung
     */
    public function topAction()
    {
        try {
            $id   = intval($this->request->get('id', 'trim'));
            $type = intval($this->request->get('type', 'trim'));

            $affectedRows = $this->get_repository('Articles')->update_record(array(
                'is_top' => $type,
            ), $id);
            $message = $type == 1 ? 'Set to top' : 'Cancel to top';
            if (!$affectedRows) {
                throw new \Exception("{$message} failure");
            }
            $this->flashSession->success("{$message} success");
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect();
    }

    /**
     * @method recommendAction
     * @auth: ledung
     */
    public function recommendAction()
    {
        try {
            $id   = intval($this->request->get('id', 'trim'));
            $type = intval($this->request->get('type', 'trim'));

            $affectedRows = $this->get_repository('Articles')->update_record(array(
                'is_recommend' => $type,
            ), $id);
            $message = $type == 1 ? 'Set recommended' : 'Cancel recommended';
            if (!$affectedRows) {
                throw new \Exception("{$message} fail");
            }
            $this->flashSession->success("{$message} success");
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect();
    }
}
