<?php

/**
 * System Configuration - Migration to database development, staging, production,...
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

return new \Phalcon\Config([
    'database'    => [
        'adapter'  => 'Mysql',
        'host'     => 'localhost',
        'port'     => 3306,
        'username' => 'root',
        'password' => 'froze1928',
        'dbname'   => 'lcd-cms',
        'charset'  => 'utf8',
    ]
]);
