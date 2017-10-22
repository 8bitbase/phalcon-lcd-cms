<?php

/**
 * Background base class controller
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Controllers;

use \Lcd\App\Backend\Repositories\RepositoryFactory;
use \Lcd\App\Core\PhalBaseController;
use \Phalcon\Mvc\Dispatcher;

class BaseController extends PhalBaseController
{
    protected $_cons;

    public function initialize()
    {
        parent::initialize();
        $this->login_check();
        $this->set_common_vars();
    }

    /**
     * @method set_common_vars Set the module public variable
     * @auth: ledung
     */
    public function set_common_vars()
    {
        $options = $this->get_repository('Options')->get_list_value();
        $this->_cons = $options;

        $this->view->setVars(array(
            'title'         => $this->systemConfig->app->app_name,
            'userinfo'      => $this->session->get('user'),
            'assetsVersion' => strtotime(date('Y-m-d H', time()) . ":00:00"),
            // 'assetsVersion' => time(),
            'trans'         => $this->trans,
            'layout' => 'layouts/management',
        ));
    }

    /**
     * @method login_check Login detection processing
     * @auth: ledung
     * @return boolean
     */
    public function login_check()
    {
        if (!$this->get_repository('Users')->login_check()) {
            return $this->redirect("auth/index");
        }
        return true;
    }

    /**
     * @method get_repository Get business objects
     * @auth: ledung
     * @param  $repositoryName
     * @return
     */
    protected function get_repository($repositoryName)
    {
        return RepositoryFactory::get_repository($repositoryName);
    }

    /**
     * @method redirect Page jump to anywhere ^^
     * @auth: ledung
     * @param  $url
     * @return
     */
    protected function redirect($url = null)
    {
        empty($url) && $url = $this->request->getHeader('HTTP_REFERER');
        $this->response->redirect($url);
    }

    /**
     * @method access_check
     * @auth: ledung
     * @param  string       $controllerName
     * @return boolean
     */
    public function access_check($controllerName = null)
    {
        if (empty($controllerName)) {
            return true;
        }
        // Init
        $actionName = $this->router->getActionName();
        $user = $this->session->get('user');
        // Check DB with user
        $resultCheck = $this->get_repository('Access')->permission_check($controllerName, $actionName, $user);

        if ($resultCheck == false) {
            return $this->redirect('index/nopermission');
        }
    }

    /**
     * @method getAllControllers
     * Return array controller on system backend
     * @auth: ledung
     * 
     * @return array
     */
    public function getAllControllers()
    {
        $files = scandir(ROOT_PATH . '/app/backend/controllers/');
        $controllers = array();
        foreach ($files as $file) {
            if ($controller = $this->extractController($file)) {
                $controllers[] = $controller;
            }
        }

        return $controllers;
    }

    /**
     * @method getAllActions
     * Return array action of controller
     * 
     * @auth: ledung
     * 
     * @param  array $controller
     * 
     * @return array
     */
    public function getAllActions($controller)
    {
        $functions = get_class_methods($controller);
        $actions = array();
        foreach ($functions as $name) {
            $actions[] = $this->extractAction($name);
        }

        return array_filter($actions);
    }   

    /**
     * @method extractAction
     * @auth: ledung
     * @param  string $name
     * @return string
     */
    protected function extractAction($name)
    {
        $action = explode('Action', $name);
        if ((count($action) > 1)) {
            return $action[0];
        }
    }

    /**
     * @method extractController
     * @auth: ledung
     * @param  string $name
     * @return string
     */
    protected function extractController($name)
    {
        $filename = explode('.php', $name);
        if (count(explode('Controller.php', $name)) > 1) {
            # code...
            if (count($filename) > 1) {
                return $filename[0];
            }
        }

        return false;
    }
    
}
