<?php

/**
 * OptionsController
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Controllers;

use \Lcd\App\Backend\Controllers\BaseController;

class OptionsController extends BaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->access_check('OptionsController');
    }

    /**
     * @method apiAction
     * @auth: ledung
     * @return mix
     */
    public function apiAction()
    {
        try {
            $options = $this->get_repository('Options')->get_options_list();
            $this->view->setVars(array(
                'options' => $options,
            ));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        $this->view->pick('pages/options/api');
    }

    /**
     * @method saveapiAction
     * @auth: ledung
     * @return mix
     */
    public function saveapiAction()
    {
        try {
            if ($this->request->isAjax() || !$this->request->isPost()) {
                throw new \Exception('Illegal request');
            }
            $dataDefault = array(
                'Google_API_Enable' => 0,
                'Google_Map' => 0,
                'Google_Login' => 0,
            );
            $dataPost = $this->request->getPost();
            $data = array_merge($dataDefault, $dataPost);
            // Validate
            $this->validator->add_rule('Google_Client_ID', 'required', $this->_('validate'));
            $this->validator->add_rule('Google_Client_Secret', 'required', $this->_('validate'));
            // Note
            if ($error = $this->validator->run($data)) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            $this->get_repository('Options')->update($data);

            $this->flashSession->success($this->_('success.update'));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect();
    }

    /**
     * @method mailAction
     * @auth: ledung
     * @return mix
     */
    public function mailAction()
    {
        try {
            $options = $this->get_repository('Options')->get_options_list();

            $this->view->setVars(array(
                'options' => $options,
            ));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        $this->view->pick('pages/options/mail');
    }

    /**
     * @method savemailAction
     * @auth: ledung
     * @return mix
     */
    public function savemailAction()
    {
        try {
            if ($this->request->isAjax() || !$this->request->isPost()) {
                throw new \Exception('Illegal request');
            }
            $data = $this->request->getPost();
            // Validate
            $this->validator->add_rule('mail_server', 'required', $this->_('validate'));
            $this->validator->add_rule('mail_user', 'required', $this->_('validate'));
            $this->validator->add_rule('mail_passwd', 'required', $this->_('validate'));
            $this->validator->add_rule('mail_smtp_encryption', 'required', $this->_('validate'));
            $this->validator->add_rule('mail_smtp_port', 'required', $this->_('validate'));
            // Note
            if ($error = $this->validator->run($data)) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            $this->get_repository('Options')->update($data);

            $this->flashSession->success($this->_('success.update'));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect();
    }

    /**
     * @method baseAction
     * @auth: ledung
     * @return mix
     */
    public function baseAction()
    {
        try {
            $options = $this->get_repository('Options')->get_options_list();

            $this->view->setVars(array(
                'options' => $options,
            ));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        $this->view->pick('pages/options/base');
    }

    /**
     * @method savebaseAction
     * @auth: ledung
     * @return mix
     */
    public function savebaseAction()
    {
        try {
            if ($this->request->isAjax() || !$this->request->isPost()) {
                throw new \Exception('Illegal request');
            }
            $siteName = $this->request->getPost('siteName', 'trim');
            $siteTitle = $this->request->getPost('siteTitle', 'remove_xss');
            $siteUrl = $this->request->getPost('siteUrl', 'trim');
            $cdnUrl = $this->request->getPost('cdnUrl', 'trim');
            $keywords = $this->request->getPost('keywords', 'remove_xss');
            $description = $this->request->getPost('description', 'remove_xss');
            $scriptHead = $this->request->getPost('site_script_head');
            $scriptBody = $this->request->getPost('site_script_body');
            $logo = $this->request->getPost('site_logo');
            $icon = $this->request->getPost('site_icon');
            // Note
            $this->validator->add_rule('siteName', 'required', $this->_('validate'))
                ->add_rule('siteName', 'chinese_alpha_numeric_dash', $this->_('validate.alpha_numeric_dash'));
            $this->validator->add_rule('siteTitle', 'required', $this->_('validate'));
            !empty($siteUrl) && $this->validator->add_rule('siteUrl', 'url', $this->_('validate.correct'));
            !empty($cdnUrl) && $this->validator->add_rule('cdnUrl', 'url', $this->_('validate.correct'));
            // Note
            if ($error = $this->validator->run(array(
                'siteName' => $siteName,
                'siteTitle' => $siteTitle,
                'siteUrl' => $siteUrl,
                'cdnUrl' => $cdnUrl,
            ))) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            // Note
            $data = array(
                'site_name' => $siteName,
                'site_title' => $siteTitle,
                'site_url' => $siteUrl,
                'cdn_url' => $cdnUrl,
                'site_description' => $description,
                'site_keywords' => $keywords,
                'site_script_head' => $scriptHead,
                'site_script_body' => $scriptBody,
                'site_logo' => $logo,
                'site_icon' => $icon,
            );
            $this->get_repository('Options')->update($data);

            $this->flashSession->success($this->_('success.update'));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect();
    }

    /**
     * @method readAction
     * @auth: ledung
     * @return mix
     */
    public function readAction()
    {
        try {
            $options = $this->get_repository('Options')->get_options_list();

            $this->view->setVars(array(
                'options' => $options,
            ));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        $this->view->pick('pages/options/read');
    }

    /**
     * @method savereadAction
     * @auth: ledung
     * @return mix
     */
    public function savereadAction()
    {
        try {
            if ($this->request->isAjax() || !$this->request->isPost()) {
                throw new \Exception('Illegal request');
            }
            $dataPost = $this->request->getPost();
            // Note
            $this->validator->add_rule('page_default_number', 'required', $this->_('validate'));
            if ($error = $this->validator->run($dataPost)) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            // Note
            $this->get_repository('Options')->update($dataPost);

            $this->flashSession->success($this->_('success.save'));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect();
    }
    
    /**
     * @method savereadAction
     * @auth: ledung
     * @return mix
     */
    public function savehomeconfigAction()
    {
        try {
            if ($this->request->isAjax() || !$this->request->isPost()) {
                throw new \Exception('Illegal request');
            }
            $dataPost = $this->request->getPost();
            // Note
            $this->get_repository('Options')->update($dataPost);

            $this->flashSession->success($this->_('success.save'));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect();
    }
}
