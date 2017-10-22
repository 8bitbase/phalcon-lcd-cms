<?php

/**
 * Tag business logic
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Repositories;

use \Lcd\App\Backend\Repositories\BaseRepository;

class Tags extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @method get_list
     * @auth: ledung
     * @return array
     */
    public function get_list()
    {
        $tagsList = $this->get_model('TagsModel')->get_list();
        return $tagsList;
    }

    /**
     * @method get_count
     * @auth: ledung
     * @return mix
     */
    public function get_count()
    {
        $count = $this->get_model('TagsModel')->get_count();
        return $count;
    }

    /**
     * @method detail
     * @auth: ledung
     * @param  $id
     * @return array
     */
    public function detail($id)
    {
        $tag = $this->get_model('TagsModel')->detail($id);
        return $tag;
    }

    /**
     * @method save
     * @auth: ledung
     * @param  $data
     * @param  $id
     * @return bool|int
     */
    public function save(array $data, $id)
    {
        $id = intval($id);
        if ($id <= 0) {
            // Note
            $this->create($data);
        } else {
            // Note
            $this->update($data, $id);
        }
    }

    /**
     * @method delete
     * @auth: ledung
     * @param  $id
     * @return mix
     */
    public function delete($id)
    {
        $id = intval($id);
        if ($id <= 0) {
            throw new \Exception('Please select the tag you want to delete');
        }
        $affectedRows = $this->get_model('TagsModel')->update_record(array(
            'is_deleted' => 0,
        ), $id);
        if ($affectedRows <= 0) {
            throw new \Exception('Deleted tag failed');
        }
        return $affectedRows;
    }

    /**
     * @method create
     * @auth: ledung
     * @param  $data
     * @return int
     */
    protected function create(array $data)
    {
        // Note
        $isExist = $this->get_model('TagsModel')->tag_is_exist($data['name'], $data['slug']);
        if ($isExist && $isExist->count() > 0) {
            throw new \Exception('The tag name or abbreviation already exists');
        }
        // Note
        $id = $this->get_model('TagsModel')->insert_record($data);
        $id = intval($id);
        if ($id <= 0) {
            throw new \Exception('Tag data failed to store');
        }
        return $id;
    }

    /**
     * @method update
     * @auth: ledung
     * @param  $data
     * @param  $id
     * @return array
     */
    protected function update(array $data, $id)
    {
        // Note
        $isExist = $this->get_model('TagsModel')->tag_is_exist($data['name'], $data['slug'], $id);
        if ($isExist && $isExist->count() > 0) {
            throw new \Exception('The tag name or abbreviation already exists');
        }
        // Note
        $affectedRows = $this->get_model('TagsModel')->update_record($data, $id);
        if ($affectedRows <= 0) {
            throw new \Exception('Update tag failed');
        }
        return $affectedRows;
    }
}
