<?php

/**
 * Validator
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Libs;

use \Phalcon\DiInterface;

class Validator
{

    /**
     * DI object
     * @var \Phalcon|DI
     */
    private $_di;

    /**
     * Internal data
     *
     * @access private
     * @var array
     */
    private $_data;

    /**
     * Current validation pointer
     *
     * @access private
     * @var string
     */
    private $_key;

    /**
     * Validation rule array
     *
     * @access private
     * @var array
     */
    private $_rules = array();

    /**
     * Interrupt mode, which is thrown without verification error
     *
     * @access private
     * @var boolean
     */
    private $_break = true;

    public function __construct(DiInterface $di)
    {
        $this->setDI($di);
    }

    /**
     * DI object assignment
     * @param DiInterface $di
     */
    public function setDI(DiInterface $di)
    {
        $this->_di = $di;
    }

    /**
     * Get the DI object
     * @return DI|\Phalcon
     */
    public function getDI()
    {
        return $this->_di;
    }

    /**
     * Increase the validation rules
     *
     * @access public
     * @param string $ key numeric key
     * @param string $ rule rule name
     * @param array $ exception = array ('message' => 'error message', 'code' => 'error code')
     * @return $ this
     */
    public function add_rule($key, $rule, $exception)
    {
        is_string($exception) && $exception = array(
            'message' => $exception,
            'code'    => 0,
        );
        if (func_num_args() <= 3) {
            $this->_rules[$key][] = array($rule, $exception);
        } else {
            $params               = func_get_args();
            $params               = array_splice($params, 3);
            $this->_rules[$key][] = array_merge(array($rule, $exception), $params);
        }

        return $this;
    }

    /**
     * Turn on / off interrupt mode
     *
     * @access public
     * @return void
     */
    public function set_break($break = false)
    {
        $break        = boolval($break);
        $this->_break = $break;
    }

    /**
     *
     * @access public
     * @param array $data need to verify the data
     * @param array $rules Rules to verify data compliance
     * @return array
     * @throws Typecho_Validate_Exception
     */
    public function run(array $data, $rules = null)
    {
        $result      = array();
        $this->_data = $data;
        $rules       = empty($rules) ? $this->_rules : $rules;

        // Cycle through the rules and test for errors
        foreach ($rules as $key => $rule) {
            $this->_key = $key;
            $data[$key] = (is_array($data[$key]) ? 0 == count($data[$key]) : 0 == strlen($data[$key])) ? null : $data[$key];
            foreach ($rule as $k => $v) {
                // Noted.
                $method = $v[0];
                // Noted.
                $exception = $v[1];
                // Noted.
                $v[1]   = $data[$key];
                $params = array_slice($v, 1);
                // Noted.
                if (!call_user_func_array(is_array($method) ? $method : array($this, $method), $params)) {
                    $result[$key] = array(
                        'message' => $exception['message'],
                        'code'    => $exception['code'],
                    );
                    break;
                }
            }
            // Noted.
            if ($this->_break && $result) {
                break;
            }
        }

        return $result;
    }

    /**
     * @method validateFileSize
     * @auth   ttdat
     * @param  file
     * @param  integer $maxSize (2097152 byte ~ 2mb)
     * @return bool
     */
    public function validateFileSize($file, $maxSize = 2097152){
        for ($i=0; $i < count($file); $i++) { 
            if ($file[$i]->getSize() > $maxSize || (!empty($file[$i]->getName()) && $file[$i]->getSize() <= 0)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Is empty?
     *
     * @access public
     * @param string $ str The string to be processed
     * @return boolean
     */
    public function required($str)
    {
        return !empty($str);
    }

    /**
     * Verify that the inputs are consistent
     *
     * @access public
     * @param string $str The string to be processed
     * @param string $key Requires consistency check for key values
     * @return boolean
     */
    public function confirm($str, $key)
    {
        return !empty($this->_data[$key]) ? ($str == $this->_data[$key]) : empty($str);
    }

    /**
     * Verify that they are equal
     * @param $ str
     * @param $ cstr
     * @return bool
     */
    public function equals($one, $two)
    {
        return $one == $two;
    }

    /**
     * Verify that they are not equal
     * @param $one
     * @param $two
     * @return bool
     */
    public function not_equals($one, $two)
    {
        return $one != $two;
    }

    /**
     * Check whether the time format is correct
     * @param $str
     * @return bool
     */
    public function check_time($str)
    {
        return strtotime($str) ? true : false;
    }

    /**
     * Enumeration type judgment
     *
     * @access public
     * @param string $str The string to be processed
     * @param array $params enumeration value
     * @return unknown
     */
    public static function enum($str, array $params)
    {
        $keys = array_flip($params);
        return isset($keys[$str]);
    }

    /**
     * The maximum length
     *
     * @param $str
     * @param $length
     * @return bool
     */
    public static function max_length($str, $length)
    {
        return (mb_strlen($str, 'UTF-8') < $length);
    }

    /**
     * Minimum length
     *
     * @access public
     * @param string $str The string to be processed
     * @param integer $length minimum length
     * @return boolean
     */
    public static function min_length($str, $length)
    {
        return (mb_strlen($str, 'UTF-8') >= $length);
    }

    /**
     * E-mail address verification
     *
     * @access public
     * @param string
     * @return boolean
     */
    public static function email($str)
    {
        return preg_match("/^[_a-z0-9-\.]+@([-a-z0-9]+\.)+[a-z]{2,}$/i", $str);
    }

    /**
     * Verify that it is a URL
     *
     * @access public
     * @param string $str
     * @return boolean
     */
    public static function url($str)
    {
        $parts = parse_url($str);
        if (!$parts) {
            return false;
        }

        return isset($parts['scheme']) &&
        in_array($parts['scheme'], array('http', 'https', 'ftp')) &&
        !preg_match('/(\(|\)|\\\|"|<|>|[\x00-\x08]|[\x0b-\x0c]|[\x0e-\x19])/', $str);
    }

    /**
     * English characters
     *
     * @access public
     * @param string
     * @return boolean
     */
    public static function alpha($str)
    {
        return preg_match("/^([a-z])+$/i", $str) ? true : false;
    }

    /**
     * English characters and numbers
     *
     * @access public
     * @param string
     * @return boolean
     */
    public static function alpha_numeric($str)
    {
        return preg_match("/^([a-z0-9])+$/i", $str);
    }

    /**
     * English characters, numbers, underlined
     *
     * @access public
     * @param string
     * @return boolean
     */
    public static function alpha_dash($str)
    {
        return preg_match("/^([_a-z0-9-])+$/i", $str) ? true : false;
    }

    /**
     * Chinese and English characters, data, underlined
     *
     * @param $str
     * @return int
     */
    public function chinese_alpha_numeric_dash($str)
    {
        return preg_match('/^[a-zA-Z0-9_\-\x{4e00}-\x{9fa5}]+$/u', $str);
    }

    /**
     * Vietnamese and English characters, data, underlined
     *
     * @param $str
     * @return int
     */
    public function vietnamese_alpha_numeric_dash($str)
    {
        return preg_match('/^[a-zA-Z0-9_\-\x{4e00}-\x{9fa5}]+$/u', $str);
    }

    /**
     * Detection of xss strings
     *
     * @access public
     * @param string $str
     * @return boolean
     */
    public static function xss_check($str)
    {
        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';

        for ($i = 0; $i < strlen($search); $i++) {
            // ;? matches the ;, which is optional
            // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

            // &#x0040 @ search for the hex values
            $str = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $str); // with a ;
            // &#00064 @ 0{0,7} matches '0' zero to seven times
            $str = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $str); // with a ;
        }

        return !preg_match('/(\(|\)|\\\|"|<|>|[\x00-\x08]|[\x0b-\x0c]|[\x0e-\x19]|' . "\r|\n|\t" . ')/', $str);
    }

    /**
     * Whether it is floating point data
     *
     * @access public
     * @param integer
     * @return boolean
     */
    public static function is_float($str)
    {
        return is_float($str);
    }

    /**
     * Whether it is integer data
     *
     * @access public
     * @param string
     * @return boolean
     */
    public static function is_integer($str)
    {
        return is_numeric($str);
    }
}
