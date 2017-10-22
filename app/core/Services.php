<?php

/**
 * DI Register the service profile
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

use Phalcon\Db\Profiler as DbProfiler;
use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\Model\Manager as ModelsManager;
use Phalcon\Session\Adapter\Files as Session;

$di = new FactoryDefault();

/**
 * Setup router
 */
$di->set('router', function () {
    $router = new \Phalcon\Mvc\Router();
    $router->setDefaultModule('frontend');

    $routerRules = new \Phalcon\Config\Adapter\Php(ROOT_PATH . "/app/config/routers.php");
    foreach ($routerRules->toArray() as $key => $value) {
        $router->add($key, $value);
    }

    return $router;
});

/**
 * DI registration session service
 */
$di->setShared('session', function () {
    $session = new Session();
    $session->start();
    return $session;
});

/**
 * DI registration cookies service
 */
$di->set('cookies', function () {
    $cookies = new \Phalcon\Http\Response\Cookies();
    $cookies->useEncryption(false);
    return $cookies;
});

/**
 * DI Register DB configuration
 */
$di->setShared('db', function () use ($config) {
    $dbconfig = $config->database->db;
    $dbconfig = $dbconfig->toArray();
    if (!is_array($dbconfig) || count($dbconfig) == 0) {
        throw new \Exception("the database config is error");
    }

    if (RUNTIME != 'pro') {
        $eventsManager = new \Phalcon\Events\Manager();
        // Analyze the underlying sql performance and log the log
        $profiler = new DbProfiler();
        $eventsManager->attach('db', function ($event, $connection) use ($profiler) {
            if ($event->getType() == 'beforeQuery') {
                // Sql sent to the database before the start analysis
                $profiler->startProfile($connection->getSQLStatement());
            }
            if ($event->getType() == 'afterQuery') {
                // Stop the analysis after sql has finished executing
                $profiler->stopProfile();
                // Get the analysis results
                $profile     = $profiler->getLastProfile();
                $sql         = $profile->getSQLStatement();
                $executeTime = $profile->getTotalElapsedSeconds();
                // Logging
                $logger = \Lcd\App\Core\PhalBaseLogger::getInstance();
                $logger->write_log("{$sql} {$executeTime}", 'debug');
            }
        });
    }

    $connection = new \Phalcon\Db\Adapter\Pdo\Mysql(array(
        "host"     => $dbconfig['host'], "port" => $dbconfig['port'],
        "username" => $dbconfig['username'],
        "password" => $dbconfig['password'],
        "dbname"   => $dbconfig['dbname'],
        "charset"  => $dbconfig['charset'])
    );

    if (RUNTIME != 'pro') {
        /* Register the listening event */
        $connection->setEventsManager($eventsManager);
    }

    return $connection;
});

/**
 * DI Register models Manager service
 */
$di->setShared('modelsManager', function () use ($di) {
    return new ModelsManager();
});

/**
 * DI registration cache service
 */
$di->setShared('cache', function () use ($config) {
    return new \Phalcon\Cache\Backend\File(new \Phalcon\Cache\Frontend\Output(), array(
        'cacheDir' => $config->app->cache_path,
    ));
});

/**
 * DI registration log service
 */
$di->setShared('logger', function () use ($di) {
    $logger = \Lcd\App\Core\PhalBaseLogger::getInstance();
    return $logger;
});

/**
 * DI registration api configuration
 */
$di->setShared('apiConfig', function () use ($di) {
    $config = \Phalcon\Config\Adapter\Php(ROOT_PATH . '/app/config/api/api_' . RUNTIME . '.php');
    return $config;
});

/**
 * DI registration system configuration
 */
$di->setShared('systemConfig', function () use ($config) {
    return $config;
});

/**
 * DI Register Custom Validator
 */
$di->setShared('validator', function () use ($di) {
    $validator = new \Lcd\App\Libs\Validator($di);
    return $validator;
});

/**
 * DI registration filter
 */
$di->setShared('filter', function () use ($di) {
    $filter = new \Lcd\App\Core\PhalBaseFilter($di);
    $filter->init();
    return $filter;
});

/**
 * DI registration constant
 */
$di->setShared('cons', function () use ($di) {
    $config = new \Phalcon\Config\Adapter\Php(ROOT_PATH . '/app/config/constant.php');
    return $config;
});
