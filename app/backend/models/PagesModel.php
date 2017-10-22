<?php

/**
 * PagesModel
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Models;

use \Lcd\App\Backend\Models\BaseModel;

class PagesModel extends BaseModel
{
    const TABLE_NAME = 'pages';

    public function initialize()
    {
        parent::initialize();
        $this->set_table_source(self::TABLE_NAME);
    }

    /**
     * @method get_list
     * @auth:  ttdat
     * @return array
     */
    public function get_list()
    {
        $builder = $this->getModelsManager()->createBuilder();
        $builder->from(array('p' => __CLASS__));
        $builder->columns(array('p.id', 'p.code', 'p.name'));
        $result = $builder->orderBy('p.id ASC')->getQuery()->execute();
        if (!$result) {
            throw new \Exception('The pages failed');
        }
        return $result;
    }

    /**
     * @method insert_record
     * @auth:  hltphat
     * @param  array $data
     * @return bool/array
     */
    public function insert_record(array $data)
    {
        $result = false;
        if (is_array($data) || count($data) != 0) {    
            if (empty($this->check_code($data['code']))) {
                $clone  = clone $this;
                $result = $clone->create($data);
            }
        }
        return $result;
    }
    
    /**
     * @method check_code
     * @auth:  hltphat
     * @param  string $code
     * @return array
     */
    public function check_code($code) 
    {
        $builder = $this->getModelsManager()->createBuilder();
        $builder->from(array('a' => __CLASS__));
        $builder->columns(array('a.*'));
        $result = $builder->where("a.code = :code:", array('code' => $code))
            ->getQuery()
            ->execute()->toArray();
        return $result;
    }

    /**
     *  Is executed before the fields are validated for not nulls/empty strings
     *  or foreign keys when an insertion operation is being made
     */
    public function beforeValidationOnCreate()
    {
        $this->create_by = $this->_user['uid'];
        
    }
}
