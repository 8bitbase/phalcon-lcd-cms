<?php

/**
 * ArticlesModel
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Models;

use \Lcd\App\Backend\Models\BaseModel;
use \Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class ArticlesModel extends BaseModel
{
    const TABLE_NAME = 'articles';

    public function initialize()
    {
        parent::initialize();
        $this->set_table_source(self::TABLE_NAME);
    }

    /**
     * @method get_list
     * @auth: ledung
     * @param  array   $page
     * @param  integer  $pagesize
     * @param  array    $ext
     * @return mix
     */
    public function get_list($page, $pagesize = 10, array $ext = array())
    {
        $page                                           = intval($page);
        $page <= 0 && $page                             = 1;
        $pagesize                                       = intval($pagesize);
        ($pagesize <= 0) && $pagesize = 10;

        $builder = $this->getModelsManager()->createBuilder();
        $builder->from(array('a' => __CLASS__));
        $builder->columns(array(
            'a.id',
            'a.title',
            'a.slug',
            'a.status',
            'a.view_number',
            'a.is_recommend',
            'a.is_top',
            'a.create_time',
            'a.modify_by',
            'a.modify_time',
        ));
        $builder->where('a.is_deleted = :is_deleted:', array('is_deleted' => 0));
        if (isset($ext['id_category']) && $ext['id_category'] > 0) {
            $builder->addFrom(__NAMESPACE__ . '\\ArticlesCategoriesModel', 'ac');
            $builder->andWhere("ac.id_category = :id_category:", array('id_category' => $ext['id_category']));
            $builder->andWhere("ac.id_article = a.id");
        }
        if (isset($ext['keyword']) && !empty($ext['keyword'])) {
            $builder->andWhere("a.title like :title:", array('title' => "%{$ext['keyword']}%"));
        }
        $builder->orderBy('a.create_time DESC');

        $paginator = new PaginatorQueryBuilder(array(
            'builder' => $builder,
            'limit'   => $pagesize,
            'page'    => $page,
        ));
        $result = $paginator->getPaginate();
        return $result;
    }

    /**
     * @method detail
     * @auth: ledung
     * @param  $id
     * @return mix
     */
    public function detail($id)
    {
        $id = intval($id);
        if ($id <= 0) {
            throw new Exception('Parameter error');
        }
        $builder = $this->getModelsManager()->createBuilder();
        $builder->from(array('a' => __CLASS__));
        // $builder->columns(array(
        //     'a.id', 'a.title', 'a.status', 'a.create_time', 'a.modify_by', 'a.modify_time',
        // ));
        $result = $builder->where("a.is_deleted = :is_deleted:", array('is_deleted' => 0))
            ->andWhere("a.id = :id:", array('id' => $id))
            ->limit(1)
            ->getQuery()
            ->getSingleResult();
        if (!$result) {
            throw new \Exception('Get data failed');
        }
        return $result;
    }

    /**
     *     Is executed before the fields are validated for not nulls/empty strings
     *  or foreign keys when an insertion operation is being made
     */
    public function beforeValidationOnCreate()
    {
        $this->create_by = $this->_user['uid'];
        if (empty($this->create_time) || !strtotime($this->create_time)) {
            $this->create_time = date('Y-m-d H:i:s');
        }
        $this->modify_by   = $this->_user['uid'];
        $this->modify_time = date('Y-m-d H:i:s');
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
        $result = $this->create($data);
        if (!$result) {
            throw new \Exception(implode(',', $this->getMessages()));
        }
        $id = $this->id;
        return $id;
    }

    /**
     * @method before_update
     * @auth: ledung
     * @param  array         $data
     * @return array
     */
    protected function before_update(array $data)
    {
        $data['modify_by']   = $this->_user['uid'];
        $data['modify_time'] = date('Y-m-d H:i:s');
        return $data;
    }

    /**
     * @method update_record
     * @auth: ledung
     * @param  array         $data
     * @param  int           $id
     * @return int
     */
    public function update_record(array $data, $id)
    {
        $id = intval($id);
        if ($id <= 0 || !is_array($data) || count($data) == 0) {
            throw new \Exception('Parameter error');
        }
        $data = $this->before_update($data);

        $this->id = $id;
        $result   = $this->iupdate($data);
        if (!$result) {
            throw new \Exception('Update failed');
        }
        $affectedRows = $this->db->affectedRows();
        return $affectedRows;
    }

    /**
     * @method get_count
     * @auth: ledung
     * @param  integer   $status
     * @return int
     */
    public function get_count($status = 1)
    {
        $status = intval($status);
        $count  = $this->count(array(
            'conditions' => 'status = :status:',
            'bind'       => array(
                'status' => $status,
            ),
        ));
        return $count;
    }
}
