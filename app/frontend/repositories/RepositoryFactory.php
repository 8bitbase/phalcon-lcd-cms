<?php

/**
 * Factory business logic
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Frontend\Repositories;

class RepositoryFactory
{

    /**
     * Container obj
     * @var array
     */
    private static $_repositories = array();

    /**
     * @method get_repository
     * @auth: ledung
     * @param  $repositoryName
     * @return Object
     */
    public static function get_repository($repositoryName)
    {
        $repositoryName = __NAMESPACE__ . "\\" . ucfirst($repositoryName);
        if (!class_exists($repositoryName)) {
            throw new \Exception("{$repositoryName} Class doesn't exist");
        }
        if (!isset(self::$_repositories[$repositoryName]) || empty(self::$_repositories[$repositoryName])) {
            self::$_repositories[$repositoryName] = new $repositoryName();
        }
        return self::$_repositories[$repositoryName];
    }
}
