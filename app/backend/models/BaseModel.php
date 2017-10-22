<?php

/**
 * BaseModel
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Models;

use \Lcd\App\Core\PhalBaseModel;

class BaseModel extends PhalBaseModel
{

    /**
     * session user
     */
    protected $_user;

    public function initialize()
    {
        parent::initialize();
        $this->_user = $this->getDI()->get('session')->get('user');
    }
}
