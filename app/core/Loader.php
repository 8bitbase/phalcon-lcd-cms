<?php

/**
 * Register the file to load
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

$loader = new \Phalcon\Loader();

/**
 * Register namespace
 */
$loader->registerNamespaces(array(
    'Lcd' => ROOT_PATH,

    'Lcd\App\Core' => ROOT_PATH . '/app/core',
    'Lcd\App\Helpers' => ROOT_PATH . '/app/helpers',
    'Lcd\App\Libs' => ROOT_PATH . '/app/libs',
    'Lcd\App\Service' => ROOT_PATH . '/app/service',
    'Lcd\App\Tasks' => ROOT_PATH . '/app/tasks',

    'Lcd\App\Frontend\Controllers' => ROOT_PATH . '/app/frontend/controllers',
    'Lcd\App\Frontend\Models' => ROOT_PATH . '/app/frontend/models',
    'Lcd\App\Frontend\Repositories' => ROOT_PATH . '/app/frontend/repositories',

    'Lcd\App\Backend\Controllers' => ROOT_PATH . '/app/backend/controllers',
    'Lcd\App\Backend\Models' => ROOT_PATH . '/app/backend/models',
    'Lcd\App\Backend\Repositories' => ROOT_PATH . '/app/backend/repositories',

    'Lcd\Vendor' => ROOT_PATH . '/vendor',
))->register();
/**
 * Register vendor
 * Reference to use: https://jtreminio.com/2012/10/composer-namespaces-in-5-minutes/
 */
require_once ROOT_PATH.'/vendor/autoload.php';
