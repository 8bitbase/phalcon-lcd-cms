<?php

/**
 * System Configuration - Constant in System
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

return array(
    'path' => array(
        'articles' => ROOT_PATH . '/public/upload/articles/',
        'banners' => ROOT_PATH . '/public/upload/banners/',
        'other' => ROOT_PATH . '/public/upload/other/',
        'sale' => ROOT_PATH . '/public/upload/sale/',
    ),
    'link' => array(
        'articles' => '/upload/articles/',
        'banners' => '/upload/banners/',
        'other' => '/upload/other/',
        'sale' => '/upload/sale/',
    ),
    'imageSize' => array(
        'thumbs' => array(
            'width' => 250,
            'height' => 150
        ),
        'normal' => array(
            'width' => 500,
            'height' => 300
        ),
        'big' => array(
            'width' => 900,
            'height' => 540
        )
    )
);
