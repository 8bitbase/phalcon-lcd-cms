<?php

/**
 * ModelFactory
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Frontend\Models;

class ModelFactory
{

    /**
     * Model object container
     * @var array
     */
    private static $_models = array();

    /**
     * @method get_model
     * @auth: ledung
     * @param  $modelName
     * @return object
     */
    public static function get_model($modelName)
    {
        $modelName = __NAMESPACE__ . "\\" . ucfirst($modelName);
        if (!class_exists($modelName)) {
            throw new \Exception("{$modelName} class does not exist");
        }
        if (!isset(self::$_models[$modelName]) || empty(self::$_models[$modelName])) {
            self::$_models[$modelName] = new $modelName();
        }
        return self::$_models[$modelName];
    }
}
