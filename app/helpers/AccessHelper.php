<?php 
/**
 * AccessHelper
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Helpers;

use \Lcd\App\Backend\Repositories\RepositoryFactory;

class AccessHelper
{

    /**
     * @method check access feature backend
     * @auth: ledung
     * @param Strng $controllerName / String $actionName
     * @return bool
     */
    public static function CheckAccess($controller, $action, $user)
    {   
        if (empty($controller) && empty($action) && empty($user)) {
            return false;
        }

        $check = RepositoryFactory::get_repository('Access')->permission_check($controller, $action, $user);

        if (!empty($check)) {
            return true;
        }

        return false;
    }
}
