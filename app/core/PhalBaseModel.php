<?php

/**
 * PhalBaseModel Phalcon expansion model
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Core;

class PhalBaseModel extends \Phalcon\Mvc\Model implements \Phalcon\Mvc\ModelInterface
{

    /**
     * Database connection object
     * @var \Phalcon\Db\Adapter\Pdo\Mysql
     */
    protected $db;

    public function initialize()
    {
        $this->db = $this->getDI()->get('db');

        // Noted.
        self::setup(array(
            'notNullValidations' => false,
        ));
    }

    /**
     * Set the table (make up the table prefix)
     * @param string $tableName
     */
    protected function set_table_source($tableName)
    {
        $prefix = $this->getDI()->get('systemConfig')->database->prefix;
        $this->setSource($prefix . $tableName);
    }

    /**
     * Encapsulates the phalcon model's update method, which only updates the data change field, not all field updates
     * @param array|null $data
     * @param null $whiteList
     * @return bool
     */
    public function iupdate(array $data = null, $whiteList = null)
    {
        if (count($data) > 0) {
            $attributes = $this->getModelsMetaData()->getAttributes($this);
            $this->skipAttributesOnUpdate(array_diff($attributes, array_keys($data)));
        }
        return parent::update($data, $whiteList);
    }
}
