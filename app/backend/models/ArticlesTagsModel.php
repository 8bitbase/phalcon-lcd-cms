<?php

/**
 * ArticlesTagsModel
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Models;

use \Lcd\App\Backend\Models\BaseModel;

class ArticlesTagsModel extends BaseModel
{
    const TABLE_NAME = 'articles_tags';

    public function initialize()
    {
        parent::initialize();
        $this->set_table_source(self::TABLE_NAME);
    }

    /**
     * @method insert_record
     * @auth: ledung
     * @param  array         $data
     * @return bool|int
     */
    public function insert_record(array $data)
    {
        if (!is_array($data) || count($data) == 0) {
            throw new \Exception('Parameter error');
        }
        $clone  = clone $this;
        $result = $clone->create($data);
        if (!$result) {
            throw new \Exception(implode(',', $this->getMessages()));
        }
        $id = $clone->id;
        return $id;
    }

    /**
     * @method delete_record
     * @auth: ledung
     * @param  int        $id
     * @return bool|int
     */
    public function delete_record($id_article)
    {
        $id_article = intval($id_article);
        if ($id_article <= 0) {
            throw new \Exception('Parameter error');
        }
        $phql   = "DELETE FROM " . __CLASS__ . " WHERE id_article = :id_article: ";
        $result = $this->getModelsManager()->executeQuery($phql, array(
            'id_article' => $id_article,
        ));
        if (!$result->success()) {
            throw new \Exception(implode(',', $result->getMessages()));
        }
        return $result;
    }

    /**
     * @method get_tags_by_ids
     * @auth: ledung
     * @param  array            $ids
     * @return mix
     */
    public function get_tags_by_ids(array $ids)
    {
        if (!is_array($ids) || count($ids) == 0) {
            throw new \Exception('Parameter error');
        }
        $builder = $this->getModelsManager()->createBuilder();
        $builder->columns(array(
            'art_tag.id_article', 't.id', 't.name',
        ));
        $builder->from(array('art_tag' => __CLASS__));
        $builder->addFrom(__NAMESPACE__ . '\\TagsModel', 't');
        $result = $builder->where("art_tag.id_article IN ({id_article:array})", array('id_article' => $ids))
            ->andWhere("art_tag.id_tag = t.id")
            ->andWhere("t.is_deleted = 0")
            ->getQuery()
            ->execute();
        if (!$result) {
            throw new \Exception('Failed to get the label data associated with the article');
        }
        return $result;
    }
}
