<?php

/**
 * Base business logic
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Repositories;

use \Lcd\App\Backend\Models\ModelFactory;
use \Phalcon\Di;
use \Phalcon\DiInterface;
use \Lcd\App\Backend\Repositories\RepositoryFactory;

class BaseRepository
{

    const CACHE_TLL = 0;
    /**
     * DI
     * @var \Phalcon\Di
     */
    private $_di;
    
    private $cache;

    public function __construct(DiInterface $di = null)
    {
        $this->setDI($di);
        $this->cache = $this->getDI()->get('cache');
    }

    /**
     * Set DI
     * @param DiInterface|null $di
     */
    public function setDI(DiInterface $di = null)
    {
        empty($di) && $di = Di::getDefault();
        $this->_di        = $di;
    }

    /**
     * Get DI
     * @return Di
     */
    public function getDI()
    {
        return $this->_di;
    }

    /**
     * @method get_model
     * @auth: ledung
     * @param  $modelName
     * @return mix
     */
    protected function get_model($modelName)
    {
        return ModelFactory::get_model($modelName);
    }

	/**
     * @method get_repository Get business objects
     * @auth: ledung
     * @param  $repositoryName
     * @return
     */
    protected function get_repository($repositoryName)
    {
        return RepositoryFactory::get_repository($repositoryName);
    }

    /**
     * @method getCacheByKey
     * @auth   lcdung
     * @param  string $key
     * @return array/null
     */
    protected function getCacheByKey($key, $page = 0)
    {
        if ($page > 0) {
                $key .= "_page_" . $page;
        }
        if ($this->cache->exists($key, self::CACHE_TLL)) {
            $result = $this->cache->get($key, self::CACHE_TLL);
            $result = json_decode($result, true);
            if (is_array($result) && count($result) > 0) {
                return $result;
            }
        }
        return null;
    }
    /**
     * @method saveCacheByKey
     * @auth   lcdung
     * @param  string $key
     * @param  array  $value
     */
    protected function saveCacheByKey($key, $value, $page = 0)
    {
        if (count($value) > 0) {
            if ($page > 0) {
                $key .= "_page_" . $page;
            }
            $this->cache->save($key, json_encode($value), self::CACHE_TLL);
        }
    }

    /**
     * @method deleteCacheKey
     * @auth   lcdung
     * @param  string $key
     * @param  array  $value
     */
    protected function deleteCacheByKey($key)
    {
        $keys = $this->cache->queryKeys($key);
        foreach ($keys as $item) {
            $this->cache->delete($item);
        }
        return true;
    }
}
