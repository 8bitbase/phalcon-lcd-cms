<?php

/**
 * OptionsModel
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Models;

use \Lcd\App\Backend\Models\BaseModel;

class OptionsModel extends BaseModel
{
    const TABLE_NAME = 'options';

    public function initialize()
    {
        parent::initialize();
        $this->set_table_source(self::TABLE_NAME);
    }

    /**
     * @method get_list
     * @auth: ledung
     * @param  array    $ext
     * @return mix
     */
    public function get_list(array $ext = array())
    {
        $result = $this->find();
        if (!$result) {
            throw new \Exception('Get configuration data failed');
        }
        $options = $result->toArray();
        return $options;
    }

    /**
     * @method update_record
     * @auth: ledung
     * @param  array         $data
     * @param  $opkey
     * @return int
     */
    public function update_record(array $data, $opkey)
    {
        if (count($data) == 0 || empty($opkey)) {
            throw new \Exception('Parameter error');
        }

        $keys   = array_keys($data);
        $values = array_values($data);
        $result = $this->db->update(
            $this->getSource(),
            $keys,
            $values,
            array(
                'conditions' => 'op_key = ?',
                'bind'       => array($opkey),
            )
        );
        if (!$result) {
            throw new \Exception('Update failed');
        }
        $affectedRows = $this->db->affectedRows();
        return $affectedRows;
    }
}
