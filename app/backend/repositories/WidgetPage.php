<?php

/**
 * WidgetPage business logic
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend\Repositories;

use \Lcd\App\Backend\Repositories\BaseRepository;

class WidgetPage extends BaseRepository
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @method get_list
     * @auth   ttdat
     * @return array
     */
    public function get_list()
    {
        $widget_page = $this->get_model('WidgetPageModel')->get_list();
        return $widget_page;
    }

    /**
     * @method insert_record
     * @auth   ttdat
     * @param  array  $data 
     * @return bool
     */
    private function insert_record(array $data)
    {
        if (!is_array($data)) {
            throw new \Exception('Parameter error');
        }
        $result = $this->get_model('WidgetPageModel')->insert_record($data);
        return $result;
    }

    /**
     * @method delete_all_record
     * @auth   ttdat
     * @return bool
     */
    private function delete_all_record()
    {
        $result = $this->get_model('WidgetPageModel')->delete_all_record();
    }

    /**
     * create multi record
     * @method
     * @auth   ttdat
     * @param  array[][]  $rawData [description]
     * @return mix
     */
    public function create(array $rawData)
    {
        $result = false;
        foreach ($rawData as $widgetId => $pages) {
            foreach ($pages as $pageId => $value) {
                $saveData = array(
                    'id_widget' => $widgetId,
                    'id_page' => $pageId,
                );
                $result = $this->insert_record($saveData);
            }
        }
        return $result;
    }

    /**
     * @method save
     * @auth   ttdat
     * @param  array[][]  $rawData
     */
    public function save(array $rawData)
    {
        if (empty($rawData)) {
            throw new \Exception('Parameter error');
        }
        try {
            $db = $this->getDI()->get('db');
            // Note
            $db->begin();
            // Note
            $this->delete_all_record();
            $this->create($rawData);
            // Note
            $db->commit();
        } catch (\Exception $e) {
            // Note
            $db->rollback();
            throw new \Exception($e->getMessage(), intval($e->getCode()));
        }
    }

    /**
     * @method get_widgets_pages_list
     * @auth   ttdat
     * @return array
     */
    public function get_widgets_pages_list()
    {
        $widget_page = $this->get_model('WidgetPageModel')->get_list();
        $result = array();
        foreach ($widget_page as $key => $item) {
            $result[$item->id_widget][$item->id_page] = 1;
        }
        return $result;
    }

    /**
     * @method check_widget
     * @auth: ledung
     * @param Strng $controllerName / String $actionName
     * @return array
     */
    public function check_widget($controller, $action)
    {
        $result = $this->get_model('WidgetPageModel')->check_widget($controller, $action);
        return $result;
    }
}
