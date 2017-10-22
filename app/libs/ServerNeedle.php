<?php

/**
 * Server probe
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Libs;

class ServerNeedle
{

    /**
     * Server operating system name
     * @return string
     */
    public static function os_name()
    {
        return PHP_OS;
    }

    /**
     * Server version name
     * @return string
     */
    public static function os_version()
    {
        return php_uname('r');
    }

    /**
     * Server domain name
     * @return mixed
     */
    public static function server_host()
    {
        return $_SERVER['SERVER_NAME'];
    }

    /**
     * Server IP
     * @return mixed
     */
    public static function server_ip()
    {
        return $_SERVER['SERVER_ADDR'];
    }

    /**
     * Web server information
     * @return mixed
     */
    public static function server_software()
    {
        return $_SERVER['SERVER_SOFTWARE'];
    }

    /**
     * Server language
     * @return string
     */
    public static function accept_language()
    {
        return getenv("HTTP_ACCEPT_LANGUAGE");
    }

    /**
     * Server port
     * @return string
     */
    public static function server_port()
    {
        return $_SERVER['SERVER_PORT'];
    }

    /**
     * PHP version
     * @return string
     */
    public static function php_version()
    {
        return PHP_VERSION;
    }

    /**
     * PHP running mode
     * @return string
     */
    public static function php_sapi_name()
    {
        return strtoupper(php_sapi_name());
    }
}
