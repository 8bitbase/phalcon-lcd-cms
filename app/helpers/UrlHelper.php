<?php

/**
 * UrlHelper
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Helpers;

class UrlHelper
{

    public static function getUrl($slug, $id, $key = 'article')
    {
        $alias = array(
            'article' => 'article',
            'categoryNews' => 'news',
            'categoryImage' => 'image',
            'categoryVideo' => 'video'
        );
        $url = new \Phalcon\Mvc\Url();
        $url->setBaseUri('/');
        switch ($key) {
            case 'article':
            case 'news':
            case 'video':
            case 'image':
                $uri = $alias[$key] . '/' . $slug . '-' . $id . '.html';
                break;
            default:
                $uri = $slug;
                if ($slug != $alias[$key]) {
                    $uri = $alias[$key] . '/' . $slug;
                }
                break;
        }
        $result = $url->get($uri);
        return $result;
    }
}
