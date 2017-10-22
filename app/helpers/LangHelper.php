<?php

/**
 * LangHelper
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Helpers;

use \Phalcon\Http\Request;
use \Phalcon\Translate\Adapter\NativeArray;

class LangHelper
{

    /**
     * @method getTranslation
     * @auth: ledung
     * @return
     */
    public static function getTranslation()
    {
        $translate = require ROOT_PATH . '/app/config/translate.php';
        if ($translate['use_fallback'] == true) {
            $lang = $translate['fallback_locale'];
        } else {
            // Ask browser what is the best language
            $request  = new Request();
            $lang_local = $request->getBestLanguage();
            $lang = substr($lang_local, 0, 2);
        }
        // Check if we have a translation file for that lang
        $translation = ROOT_PATH . "/app/lang/" . $lang . ".php";
        if (!file_exists($translation)) {
            $translation =  ROOT_PATH . "/app/lang/" . $translate['locales'][0] . ".php";
        }
        require $translation;

        // Return a translation object
        return new NativeArray(
            [
                "content" => $messages,
            ]
        );
    }
}
