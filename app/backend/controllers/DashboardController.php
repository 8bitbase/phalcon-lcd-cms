<?php

/**
 * DashboardController
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Controllers;

use \Lcd\App\Backend\Controllers\BaseController;
use \Lcd\App\Libs\ServerNeedle;

class DashboardController extends BaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->access_check('DashboardController');
    }

    /**
     * @method indexAction
     * @auth: ledung
     * @return mix
     */
    public function indexAction()
    {
        $articlesCount = $this->get_repository('Articles')->get_count();
        $categorysCount = $this->get_repository('Categories')->get_count();
        $tagsCount = $this->get_repository('Tags')->get_count();
        $usersCount = $this->get_repository('Users')->get_count();

        $this->view->setVars(array(
            'articlesCount'  => $articlesCount,
            'categorysCount' => $categorysCount,
            'tagsCount'      => $tagsCount,
            'usersCount'      => $usersCount,
        ));
        $this->view->pick('pages/dashboard/index');
    }

    /**
     * @method systemAction
     * @auth: ledung
     * @return mix
     */
    public function systemAction()
    {
        $systemInfo = array(
            'osName'         => ServerNeedle::os_name(),
            'osVersion'      => ServerNeedle::os_version(),
            'serverName'     => ServerNeedle::server_host(),
            'serverIp'       => ServerNeedle::server_ip(),
            'serverSoftware' => ServerNeedle::server_software(),
            'serverLanguage' => ServerNeedle::accept_language(),
            'serverPort'     => ServerNeedle::server_port(),
            'phpVersion'     => ServerNeedle::php_version(),
            'phpSapi'        => ServerNeedle::php_sapi_name(),
        );

        $this->view->setVars(array(
            'appVersion'     => $this->systemConfig->app->version,
            'systemInfo'     => $systemInfo,
        ));
        $this->view->pick('pages/dashboard/system');
    }
}
