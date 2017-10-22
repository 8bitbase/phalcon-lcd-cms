<?php

/**
 * PhalBaseLogger
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Core;

use \Phalcon\DI;

class PhalBaseLogger
{
    private static $_instance;

    private static $_logger;

    /**
     * @method __clone
     * @auth: ledung
     */
    public function __clone()
    {
        trigger_error('Clone is not allow!', E_USER_ERROR);
    }

    /**
     * @method getInstance Get a single instance of logs
     * @auth: ledung
     * @param  $file config
     * @return log
     */
    public static function getInstance($file = null)
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($file);
        }
        return self::$_instance;
    }

    public function __construct($file = null)
    {
        if (!empty($file)) {
            $logFile = $file;
        } else {
            $fileName     = date('YmdH', time());
            $systemConfig = DI::getDefault()->get('systemConfig');
            $logPath      = $systemConfig->app->log_path;
            $logFile      = "{$logPath}/{$fileName}.log";
        }

        $logger        = new \Phalcon\Logger\Adapter\File($logFile);
        self::$_logger = $logger;
    }

    /**
     * @method write_log
     * @auth: ledung
     * @param  [type]    $log
     * @param  string    $level
     * @link https://docs.phalconphp.com/zh/latest/reference/logging.html
     */
    public function write_log($log, $level = '')
    {
        if (is_array($log)) {
            $log = json_encode($log);
        }
        empty($level) && $level = 'error';
        $level                  = strtolower($level);
        self::$_logger->$level($log);
    }
}
