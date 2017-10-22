<?php

/**
 * IndexController
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Frontend\Controllers;

use \Lcd\App\Frontend\Controllers\BaseController;
use \Lcd\App\Helpers\PaginatorHelper;
use \Lcd\App\Helpers\WidgetHelper;

class HomeController extends BaseController
{
    /**
     * @method indexAction
     * Home / Search Page / Category Page / Tab Page
     * @auth:  ttdat
     */
    public function indexAction()
    {   
        $data = array();
        $this->view->setVars($data);
        $this->view->pick('pages/home/index');
    }
}
