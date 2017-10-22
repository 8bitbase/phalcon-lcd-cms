<?php

/**
 * PhalBaseFilter Phalcon expansion filter
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Core;

use \Phalcon\Filter;

class PhalBaseFilter extends Filter
{

    /**
     * Customize the initialization function
     * @auth: ledung
     * @return
     */
    public function init()
    {
        // Add filter remove_xss
        $this->add('remove_xss', function ($value) {
            return \Lcd\App\Libs\Filter::remove_xss($value);
        });
    }
}
