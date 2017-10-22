<?php

/**
 * Article business logic
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Repositories;

use \Lcd\App\Backend\Repositories\BaseRepository;
use \Lcd\App\Helpers\ImageHelper;

class Articles extends BaseRepository
{
    protected $className = 'article';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @method get_list
     * @auth: ledung
     * @param  $page
     * @param  $pagesize
     * @param  $ext
     * @return mix
     */
    public function get_list($page, $pagesize = 10, array $ext = array())
    {
        $keyCache = $this->className . $pagesize . $page . serialize($ext);
        $result = $this->getCacheByKey($keyCache);
        if(empty($result)) {
            $result = $this->get_model('ArticlesModel')->get_list($page, $pagesize, $ext);
            $this->saveCacheByKey($keyCache, $result);
        }
        return $result;
    }

    /**
     * @method detail
     * @auth: ledung
     * @param  $id
     * @return mix
     */
    public function detail($id, $ext)
    {
        $article = $this->get_model('ArticlesModel')->detail($id);
        $article = $article->toArray();
        if (is_array($article) && count($article) > 0) {
            // Categories
            $categories = $this->get_repository('Articles')->get_categories_by_ids([$id]);
            foreach ($categories as $ck => $cv) {
                $article['categories'][] = array(
                    'id'   => $cv['id_category'],
                    'name' => $cv['name'],
                );
            }
            // Tags
            $tags = $this->get_repository('Articles')->get_tags_by_ids([$id]);
            foreach ($tags as $tk => $tv) {
                $article['tags'][] = array(
                    'id'   => $tv['id'],
                    'name' => $tv['name'],
                );
            }
            $link             = $ext['link'];
            $size             = 'thumbs';
            $article['image'] = ImageHelper::get($link, $article['image'], $size);
        }
        return $article;
    }

    /**
     * @method get_tags_by_ids
     * @auth: ledung
     * @param  $id
     * @return mix
     */
    public function get_tags_by_ids($id)
    {
        $tags = $this->get_model('ArticlesTagsModel')->get_tags_by_ids($id);
        $tags = $tags->toArray();
        return $tags;
    }

    /**
     * @method get_categories_by_ids
     * @auth: ledung
     * @param  $ids
     * @return mix
     */
    public function get_categories_by_ids(array $ids)
    {
        $categories = $this->get_model('ArticlesCategoriesModel')->get_categories_by_ids($ids);
        $categories = $categories->toArray();
        return $categories;
    }

    /**
     * @method update_record
     * @auth: ledung
     * @param  $data
     * @param  $id
     * @return int
     */
    public function update_record(array $data, $id)
    {
        $affectedRows = $this->get_model('ArticlesModel')->update_record($data, $id);
        $affectedRows = intval($affectedRows);
        return $affectedRows;
    }

    /**
     * @method save
     * @auth: ledung
     * @param  $data
     * @param  $id
     * @return mix
     */
    public function save(array $data, $id = null)
    {
        $id = intval($id);
        $this->deleteCacheByKey($this->className);

        if (isset($data['image']['file']) && isset($data['image']['path']) && isset($data['image']['size'])) {
            $image = ImageHelper::upload_first($data['image']['file'], $data['image']['path'], $data['image']['size']);
            if (!empty($image)) {
                $data['image'] = $image;
            } else {
                unset($data['image']);
            }
        }
        if (empty($id)) {
            // Note
            $this->create($data);
        } else {
            // Note
            $this->update($data, $id);
        }
    }

    /**
     * @method create
     * @auth: ledung
     * @param  $data
     * @return mix
     */
    protected function create(array $data)
    {
        try {
            $db = $this->getDI()->get('db');
            // Note
            $db->begin();
            // Note
            $data['slug'] .= '-' . date('Y') + date('m') + date('d') + date('h') + date('i') + date('s');
            $id = $this->create_article($data);
            // Note
            $this->create_article_categories($id, $data['category']);
            // Note
            $tagID = $this->get_tagid_list($data['tags']);
            $this->create_article_tags($id, $tagID);
            // Note
            $db->commit();
        } catch (\Exception $e) {
            // Note
            $db->rollback();

            throw new \Exception($e->getMessage(), intval($e->getCode()));
        }
    }

    /**
     * @method update
     * @auth: ledung
     * @param  $data
     * @param  $id
     * @return mix
     */
    protected function update(array $data, $id)
    {
        try {
            $db = $this->getDI()->get('db');
            // Note
            $db->begin();
            // Note
            $this->update_article($data, $id);
            // Note
            // $this->update_article_content($data['markdown'], $data['content'], $id);
            // Note
            $this->delete_article_categories($id);
            $this->create_article_categories($id, $data['category']);
            // Note
            $this->delete_article_tags($id);
            $tagID = $this->get_tagid_list($data['tags']);
            $this->create_article_tags($id, $tagID);
            // Note
            $db->commit();
        } catch (\Exception $e) {
            // Note
            $db->rollback();

            throw new \Exception($e->getMessage(), intval($e->getCode()));
        }
    }

    /**
     * @method get_count
     * @auth: ledung
     * @return mix
     */
    public function get_count()
    {
        $count = $this->get_model('ArticlesModel')->get_count();
        return $count;
    }

    /**
     * @method delete
     * @auth: ledung
     * @param  $id
     * @return mix
     */
    public function delete($id)
    {
        $this->deleteCacheByKey($this->className);
        $affectedRows = $this->get_model('ArticlesModel')->update_record(array(
            'status' => 0,
        ), $id);
        $affectedRows = intval($affectedRows);
        return $affectedRows;
    }

    /**
     * @method create_article
     * @auth: ledung
     * @param  $data
     * @return bool|int
     */
    protected function create_article(array $data)
    {
        $id = $this->get_model('ArticlesModel')->insert_record($data);
        return $id;
    }

    /**
     * @method update_article
     * @auth: ledung
     * @param  $data
     * @param  $id
     * @return int
     */
    protected function update_article(array $data, $id)
    {
        $affectedRows = $this->get_model('ArticlesModel')->update_record($data, $id);
        return $affectedRows;
    }

    /**
     * @method create_article_content
     * @auth: ledung
     * @param  $id
     * @param  $markdown
     * @param  $content
     * @return bool|int
     */
    protected function create_article_content($id, $markdown, $content)
    {
        $id = intval($id);
        if ($id <= 0) {
            throw new \Exception('Parameter error');
        }
        $cid = $this->get_model('ContentsModel')->insert_record(array(
            'relateid' => $id,
            'markdown' => $markdown,
            'content'  => $content,
        ));
        return $cid;
    }

    /**
     * @method update_article_content
     * @auth: ledung
     * @param  $markdown
     * @param  $content
     * @param  $id
     * @return int
     */
    protected function update_article_content($markdown, $content, $id)
    {
        $id = intval($id);
        if ($id <= 0) {
            throw new \Exception('Parameter error');
        }
        $affectedRows = $this->get_model('ContentsModel')->update_record(array(
            'markdown' => $markdown,
            'content'  => $content,
        ), $id);
        return $affectedRows;
    }

    /**
     * @method create_article_categories
     * @auth: ledung
     * @param  $id
     * @param  $cid
     * @return mix
     */
    protected function create_article_categories($id_article, $id_categories)
    {
        $id_article = intval($id_article);
        if ($id_article <= 0) {
            throw new \Exception('Parameter error');
        }
        $id_categories = array_map('trim', $id_categories);
        $id_categories = array_map('intval', $id_categories);
        $id_categories = array_filter($id_categories);
        $id_categories = array_unique($id_categories);
        if (!is_array($id_categories) || count($id_categories) == 0) {
            throw new \Exception('Please select the category to which the article belongs');
        }
        $articlesCategoriesModel = $this->get_model('ArticlesCategoriesModel');
        foreach ($id_categories as $ck => $id_category) {
            $articlesCategoriesModel->insert_record(array(
                'id_article'  => $id_article,
                'id_category' => $id_category,
            ));
        }
        return true;
    }

    /**
     * @method delete_article_categories
     * @auth: ledung
     * @param  $id
     * @return bool
     */
    protected function delete_article_categories($id)
    {
        $result = $this->get_model('ArticlesCategoriesModel')->delete_record($id);
        if (!$result) {
            throw new \Exception('The updated article associated with the classification data failed');
        }
        return $result;
    }

    /**
     * @method get_tagid_list
     * @auth: ledung
     * @param  $tagName
     * @param  $data
     * @return array
     */
    protected function get_tagid_list($tagName)
    {
        $tagidArray   = array();
        $tagNameArray = explode(',', $tagName);
        $tagNameArray = array_map('trim', $tagNameArray);
        $tagNameArray = array_filter($tagNameArray);
        $tagNameArray = array_unique($tagNameArray);
        if (is_array($tagNameArray) && count($tagNameArray) > 0) {
            $tagsModel = $this->get_model('TagsModel');
            foreach ($tagNameArray as $tk => $tv) {
                $tid = $tagsModel->get_id_by_name($tv);
                if ($tid) {
                    // Note
                    $tagidArray[] = $tid;
                } else {
                    // Note
                    $tid = $tagsModel->insert_record(array(
                        'name' => $tv,
                    ));
                    $tagidArray[] = $tid;
                }
            }
        }
        return $tagidArray;
    }

    /**
     * @method create_article_tags
     * @auth: ledung
     * @param  $id
     * @param  $tagidArray
     * @return bool
     */
    protected function create_article_tags($id_article, array $tagsArray)
    {
        $id_article = intval($id_article);
        if ($id_article <= 0 || !is_array($tagsArray) || count($tagsArray) == 0) {
            return false;
        }
        $articlesTagsModel = $this->get_model('ArticlesTagsModel');
        foreach ($tagsArray as $tag) {
            $articlesTagsModel->insert_record(array(
                'id_article' => $id_article,
                'id_tag'     => $tag,
            ));
        }
        return true;
    }

    /**
     * @method delete_article_tags
     * @auth: ledung
     * @param  $id
     * @return bool
     */
    protected function delete_article_tags($id)
    {
        $result = $this->get_model('ArticlesTagsModel')->delete_record($id);
        if (!$result) {
            throw new \Exception('The associated tag data failed to update the article');
        }
        return $result;
    }
}
