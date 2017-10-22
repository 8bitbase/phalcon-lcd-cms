<?php

/**
 *
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */
namespace Lcd\App\Backend\Controllers;

use \Lcd\App\Backend\Controllers\BaseController;

class IndexController extends BaseController
{
    /**
     * @method indexAction
     * @auth: ledung
     * @return mix
     */
    public function indexAction()
    {
        return $this->redirect('dashboard/index');
    }

    /**
     * @method notfoundAction
     * @auth: ledung
     * @return mix
     */
    public function notfoundAction()
    {
        return $this->response->setHeader('status', '404 Not Found');
    }

    /**
     * @method nopermissionAction
     * @auth: ledung
     * @return mix
     */
    public function nopermissionAction()
    {
        $this->view->setVars(array(
            'message' => 'NO PERMISSION',
        ));
        return $this->view->pick('pages/errors/500');
    }
}
