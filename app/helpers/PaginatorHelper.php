<?php

/**
 * PaginatorHelper
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Helpers;

class PaginatorHelper
{

    /**
     * Get the paging page number
     * @param $totalRows Total number of records
     * @param $page current page number
     * @param $pagesize the number of pages per page
     * @param int $num paging page number, the default display of 5 pages
     */
    public static function get_paginator($totalRows, $page, $pagesize = 10, $num = 5)
    {
        $page               = intval($page);
        $page <= 0 && $page = 1;

        $totalPage                  = ceil($totalRows / $pagesize);
        $page > $totalPage && $page = $totalPage;
        //Calculate the start page number based on $num
        $space = floor($num / 2);
        if ($page == 1) {
            $startPage = 1;
            $endPage   = $num;
        } elseif ($page == $totalPage) {
            $endPage   = $totalPage;
            $startPage = $endPage - $num + 1;
        } elseif ($page - $space <= 0) {
            $startPage = 1;
            $endPage   = $num;
        } elseif ($page - $space > 0) {
            $startPage = $page - $space;
            $endPage   = $startPage + $num - 1;
            if ($endPage > $totalPage) {
                $startPage = $totalPage - $num + 1;
            }
        }
        $startPage <= 0 && $startPage     = 1;
        $endPage > $totalPage && $endPage = $totalPage;
        $paginator                        = range($startPage, $endPage);
        return $paginator;
    }

    /**
     * Generate a paging link
     * @param int $page
     * @param null $url
     * @return string
     */
    public static function get_page_url($page, $url = null)
    {
        $page               = intval($page);
        $page <= 0 && $page = 1;
        empty($url) && $url = $_SERVER['REQUEST_URI'];
        $url                = rtrim($url, '/');
        //
        $index = strpos($url, '?');
        if ($index === false) {
            $url = "{$url}?page={$page}";
        } else {
            $url = "{$url}&page={$page}";
        }
        $array = parse_url($url);
        $str   = isset($array['path']) ? $array['path'] : '';
        if (!empty($array['query'])) {
            parse_str($array['query'], $queryArray);
            $query = http_build_query($queryArray);
            $str   = "{$str}?{$query}";
        }
        return $str;
    }
}
