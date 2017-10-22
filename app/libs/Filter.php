<?php

/**
 * Filter
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Libs;

class Filter
{

    /**
     * Remove xss special characters
     * @param $str
     * @return mixed
     */
    public static function remove_xss($str)
    {
        $str = filter_var(trim($str), FILTER_SANITIZE_STRING);
        return $str;
    }
}
