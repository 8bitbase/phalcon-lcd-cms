<?php

/**
 * BaseController
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Frontend\Controllers;

use \Lcd\App\Core\PhalBaseController;
use \Lcd\App\Frontend\Repositories\RepositoryFactory;
use \Lcd\App\Helpers\WidgetHelper;

class BaseController extends PhalBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->set_common_vars();
    }

    /**
     * @method set_common_vars
     * @auth: ledung
     */
    public function set_common_vars()
    {
        $option = $this->get_repository('Options');
        $domain = rtrim($option->get_option('site_url'), '/');
        $url = $domain . '/' . $this->router->getRewriteUri();
        $urlCDN = $option->get_option('cdn_url');
        $this->url->setStaticBaseUri($urlCDN);

        $this->view->setVars(array(
            'siteName' => $option->get_option('site_name'),
            'siteTitle' => $option->get_option('site_title'),
            'siteLogo' => $option->get_option('site_logo'),
            'siteIcon' => $option->get_option('site_icon'),
            'siteUrl' => $domain,
            'fullUrl' => $url,
            'siteScriptHead' => $option->get_option('site_script_head'),
            'siteScriptBody' => $option->get_option('site_script_body'),
            'siteDescription' => $option->get_option('site_description'),
            'siteKeywords' => $option->get_option('site_keywords'),
            'assetsVersion' => strtotime(date('Y-m-d H:i:s', time())),
            'layout' => 'layouts/template',
        ));
    }

    /**
     * @method get_repository
     * @auth: ledung
     * @param  $repositoryName
     * @return mix
     */
    protected function get_repository($repositoryName)
    {
        return RepositoryFactory::get_repository($repositoryName);
    }

    /**
     * @method redirect
     * @auth: ledung
     * @param  $url
     * @return mix
     */
    protected function redirect($url = null)
    {
        empty($url) && $url = $this->request->getHeader('HTTP_REFERER');
        $this->response->redirect($url);
    }

}
