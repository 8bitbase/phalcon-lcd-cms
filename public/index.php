<?php

/**
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

try {

    $runtime = 'dev';
    define('RUNTIME', $runtime);
    define('ROOT_PATH', dirname(__DIR__));

    if (RUNTIME == 'dev') {
        ini_set('display_errors', 1);
        error_reporting (E_ALL | E_STRICT);
    }

    $config = new \Phalcon\Config\Adapter\Php(ROOT_PATH . "/app/config/system/system_{$runtime}.php");

    /**
     * Include loader.php
     */
    include ROOT_PATH . '/app/core/Loader.php';

    /**
     * Include services.php
     */
    include ROOT_PATH . '/app/core/Services.php';

    /**
     * Processing request
     */
    $application = new \Phalcon\Mvc\Application($di);

    $application->registerModules(array(
        'frontend' => array(
            'className' => 'Lcd\App\Frontend\FrontendModule',
            'path'      => ROOT_PATH . '/app/frontend/FrontendModule.php',
        ),
        'backend'  => array(
            'className' => 'Lcd\App\Backend\BackendModule',
            'path'      => ROOT_PATH . '/app/backend/BackendModule.php',
        ),
    ));

    echo $application->handle()->getContent();

} catch (\Exception $e) {
    $log = array(
        'file'  => $e->getFile(),
        'line'  => $e->getLine(),
        'code'  => $e->getCode(),
        'msg'   => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    );

    $date   = date('Ymd');
    $logger = new \Phalcon\Logger\Adapter\File(ROOT_PATH . "/app/cache/logs/crash_{$date}.log");
    $logger->error(json_encode($log));
}
