<?php

/**
 * Config Routers
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball

 *
 * Example: support regular
 * $key => array("controller" => "", "action" => "")
 */

return array(
    // Backend routes rule
    '/backend/:controller/:action/:params' => array(
        'module' => 'backend',
        'controller'=>1,
        'action'=>2
    ),

    // Frontend routes rule
    '/frontend/:controller/:action/:params' => array(
        'module' => 'frontend',
        'controller'=>1,
        'action'=>2
    ),

    // Home
    '/' => array(
        'module' => 'frontend',
        'controller' => 'home',
        'action' => 'index'
    ),

    // Page not found - 404
    '/404' => array(
        'module' => 'frontend',
        'controller' => 'index',
        'action' => 'notfound',
    )
);
