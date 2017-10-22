<?php

/**
 * Phalcon configuration extension
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Core;

class Config
{

    /**
     * Singleton object
     * @var
     */
    private static $_instance;

    /**
     * Configure the array
     * @var array
     */
    private static $configArray;

    /**
     * Configuration file
     * @var string
     */
    private $path;

    /**
     * Singleton
     * @param string $path
     * @param string $file
     * @return array
     */
    public static function getInstance($path, $file = null)
    {
        if (!isset(self::$_instance[$path]) || !(self::$_instance[$path] instanceof self)) {
            self::$_instance[$path] = new self($path, $file);
        }
        return self::$_instance[$path];
    }

    /**
     * Constructor
     * @access private
     * @param string $path
     * @param string $file
     */
    private function __construct($path, $file = null)
    {
        if (!isset(self::$configArray[$path]) || !is_array(self::$configArray[$path]) || count(self::$configArray[$path]) == 0) {
            $configArray = $this->_load_config($path, $file);
            if (is_array($configArray) && count($configArray) > 0) {
                self::$configArray[$path] = $configArray;
            }
        }
        $this->path = $path;
    }

    /**
     * Prevent cloning of singleton objects
     */
    public function __clone()
    {
        trigger_error('Clone is not allow!', E_USER_ERROR);
    }

    /**
     * Get configuration (auto-match runtime)
     * @access public
     * @param String to receive dynamic parameters, the order of the array subscript delivery
     * Eg: get_api_config ('CART_CENTER', 'URL', ...);
     * Do not pass any parameters, that is, to obtain the entire configuration array
     * @return string $result
     */
    public function get()
    {
        $result    = self::$configArray[$this->path];
        $argsArray = func_get_args();
        foreach ($argsArray as $key => $value) {
            // Press the index index
            if (isset($result[$value])) {
                $result = $result[$value];
            } else {
                $result = '';
            }
        }
        return $result;
    }

    /**
     * Load the configuration file
     * @access protected
     * @param string $path
     * @param string $file
     * @return object
     */
    protected function _load_config($path, $file = null)
    {
        empty($file) && $file = $path;
        $configFile           = dirname(__DIR__) . "/config/{$path}/{$file}_" . RUNTIME . ".php";
        if (!file_exists($configFile)) {
            throw new \Exception("Configuration file：{$configFile}does not exist!");
        }
        $result = new \Phalcon\Config\Adapter\Php($configFile);
        $result = $result->toArray();
        return $result;
    }
}
