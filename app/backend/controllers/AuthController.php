<?php

/**
 * authController
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Controllers;

use \Lcd\App\Backend\Repositories\RepositoryFactory;
use \Lcd\App\Core\PhalBaseController;

class AuthController extends PhalBaseController
{
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * @method indexAction
     * @auth: ledung
     * @return mix
     */
    public function indexAction()
    {
        $this->login_check();
        $this->view->setVars(array(
            'title'         => $this->systemConfig->app->app_name,
            'assetsVersion' => strtotime(date('Y-m-d H', time()) . ":00:00"),
        ));
        $this->view->setMainView('auth/login');
    }

    /**
     * @method loginAction
     * @auth: ledung
     * @return mix
     */
    public function loginAction()
    {
        $this->login_check();
        try {
            if ($this->request->isAjax() || !$this->request->isPost()) {
                throw new \Exception('Illegal request');
            }
            $username = $this->request->getPost('username', 'trim');
            $password = $this->request->getPost('password', 'trim');

            // Note
            $this->validator->add_rule('username', 'required', $this->_('validate.username'))
                ->add_rule('username', 'alpha_dash', $this->_('validate.alpha_dash'))
                ->add_rule('username', 'min_length', $this->_('validate.min_length').'4', 4)
                ->add_rule('username', 'max_length', $this->_('validate.max_length').'20', 20);
            $this->validator->add_rule('password', 'required', $this->_('validate'))
                ->add_rule('password', 'min_length', $this->_('validate.min_length').'6', 6)
                ->add_rule('password', 'max_length', $this->_('validate.max_length').'32', 32);
            // Note
            if ($error = $this->validator->run(array('username' => $username, 'password' => $password))) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            // Note
            RepositoryFactory::get_repository('Users')->login($username, $password);
            
            return $this->response->redirect('dashboard/index');
        } catch (\Exception $e) {
            $this->write_exception_log($e);

            $this->flashSession->error($e->getMessage());

            return $this->response->redirect('auth/index');
        }
    }

    /**
     * @method logoutAction
     * @auth: ledung
     * @return mix
     */
    public function logoutAction()
    {
        if ($this->session->destroy()) {
            return $this->response->redirect('auth/index');
        }
    }

    /**
     * @method login_check
     * @auth: ledung
     * @return bool
     */
    protected function login_check()
    {
        if (RepositoryFactory::get_repository('Users')->login_check()) {
            return $this->response->redirect("auth/index");
        }
        return false;
    }
}
