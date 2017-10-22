<?php

/**
 * AccountController
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Controllers;

use \Lcd\App\Backend\Controllers\BaseController;

class AccountController extends BaseController
{
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * @method profileAction
     * @auth: ledung
     */
    public function profileAction()
    {
        try {
            $username = $this->session->get('user')['username'];
            $user     = $this->get_repository('Users')->detail($username);

            $this->view->setVars(array(
                'user' => $user,
            ));
            $this->view->pick('pages/account/profile');
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());

            return $this->redirect('dashboard/index');
        }
    }

    /**
     * @method saveprofileAction
     * @auth: ledung
     */
    public function saveprofileAction()
    {
        try {
            if ($this->request->isAjax() || !$this->request->isPost()) {
                throw new \Exception('Illegal request');
            }
            $nickname                     = $this->request->getPost('nickname', 'trim');
            $email                        = $this->request->getPost('email', 'trim');
            empty($nickname) && $nickname = $this->session->get('user')['nickname'];
            // Note
            $this->validator->add_rule('nickname', 'chinese_alpha_numeric_dash', $this->_('validate.alpha_numeric_dash'))
                ->add_rule('nickname', 'min_length', $this->_('validate.alpha_numeric_dash', 2), 2)
                ->add_rule('nickname', 'max_length', $this->_('validate.alpha_numeric_dash', 20), 20);
            $this->validator->add_rule('email', 'required', $this->_('validate.required', array('field' => 'Email')))
                ->add_rule('email', 'email', $this->_('validate.correct'));
            // Note
            if ($error = $this->validator->run(array(
                'nickname' => $nickname,
                'email'    => $email,
            ))) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            // Note
            $data = array(
                'nickname' => $nickname,
                'email'    => $email,
            );
            $this->get_repository('Users')->update($data, $this->session->get('user')['uid']);

            $this->flashSession->success($this->_('success.update'));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect('account/profile');
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
            $oldpwd     = $this->request->getPost('oldpwd', 'trim');
            $newpwd     = $this->request->getPost('newpwd', 'trim');
            $confirmpwd = $this->request->getPost('confirmpwd', 'trim');
            // Validate -> show message
            $this->validator->add_rule('oldpwd', 'required', $this->_('validate'))
                ->add_rule('oldpwd', 'not_equals', $this->_('validate.equals'), $newpwd)
                ->add_rule('oldpwd', 'min_length', $this->_('validate.min', 6), 6)
                ->add_rule('oldpwd', 'max_length', $this->_('validate.max', 20), 20);
            $this->validator->add_rule('newpwd', 'required', $this->_('validate'))
                ->add_rule('newpwd', 'min_length', $this->_('validate.min', 6), 6)
                ->add_rule('newpwd', 'max_length', $this->_('validate.max', 20), 20)
                ->add_rule('newpwd', 'equals', $this->_('validate.equals'), $confirmpwd);
            // Validate new pass -> show error
            if ($error = $this->validator->run(array(
                'oldpwd' => $oldpwd,
                'newpwd' => $newpwd,
            ))) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            // Update DB -> show message
            $this->get_repository('Users')->update_password($oldpwd, $newpwd);

            $this->flashSession->success($this->_('success.change-pass'));
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());
        }
        return $this->redirect('account/profile');
    }
}
