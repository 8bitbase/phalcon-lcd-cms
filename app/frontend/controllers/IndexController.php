<?php

/**
 * IndexController
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Frontend\Controllers;

use \Lcd\App\Frontend\Controllers\BaseController;

class IndexController extends BaseController
{

    /**
     * @method indexAction
     * @auth: ledung
     * @return mix
     */
    public function indexAction()
    {
        $this->dispatcher->forward(array(
            'controller' => 'home',
            'action' => 'index',
        ));
    }

    /**
     * @method notfoundAction
     * @auth: ledung
     * @return mix
     */
    public function notfoundAction()
    {
        $this->view->setVars(array(
            'layout' => 'layouts/blank',
        ));

        $this->view->pick('pages/errors/404');
    }
}
