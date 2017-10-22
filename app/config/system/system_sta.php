<?php

/**
 * System Configuration - Staging Environment
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

return array(
    'app'      => array(
        // project name
        'app_name'       => 'LCD-CMS',

        // version number
        'version'        => '0.1',

        // Root namespace
        'root_namespace' => 'Lcd',

        // Foreground configuration
        'frontend'       => array(
            // The pathname of the module in the URL
            'module_pathinfo' => '/',

            // Controller path
            'controllers'     => ROOT_PATH . '/app/frontend/controllers/',

            // View path
            'views'           => ROOT_PATH . '/app/frontend/views/',

            // Whether to compile the template in real time
            'is_compiled'     => false,

            // Template path
            'compiled_path'   => ROOT_PATH . '/app/cache/compiled/frontend/',
        ),

        // Background configuration
        'backend'        => array(
            // The pathname of the module in the URL
            'module_pathinfo' => '/backend/',

            // Controller path
            'controllers'     => ROOT_PATH . '/app/backend/controllers/',

            // View path
            'views'           => ROOT_PATH . '/app/backend/views/',

            // Whether to compile the template in real time
            'is_compiled'     => false,

            // Template path
            'compiled_path'   => ROOT_PATH . '/app/cache/compiled/backend/',

            // Background static resource URL
            'assets_url'      => '/backend/',
        ),

        // Class library path
        'libs'           => ROOT_PATH . '/app/libs/',

        // Log root directory
        'log_path'       => ROOT_PATH . '/app/cache/logs/',

        // Cache path
        'cache_path'     => ROOT_PATH . '/app/cache/data/',

        'migrations'  => ROOT_PATH . '/app/migrations/',
    ),

    // Database configuration
    'database' => array(
        // Database connection information
        'db'     => array(
            'host'     => 'localhost',
            'port'     => 3306,
            'username' => 'root',
            'password' => '',
            'dbname'   => 'lcd-cms-sta',
            'charset'  => 'utf8',
        ),

        // Table prefix
        'prefix' => '',
    ),
);
