<?php

/**
 * PhalBaseController Phalcon expansion controller
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 *
 */

namespace Lcd\App\Core;

class PhalBaseController extends \Phalcon\Mvc\Controller
{
    public function initialize()
    {
        // Use trans: $this->trans->_("hi-name", ["name" => 'value']);
        $this->trans = \Lcd\App\Helpers\LangHelper::getTranslation();
    }

    /**
     * ajax
     * @param $message
     * @param int $code
     * @param array $data
     */
    protected function ajax_return($message, $code = 1, array $data = array())
    {
        $result = array(
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        );
        //$this -> response -> setContent(json_encode($result));
        $this->response->setJsonContent($result);
        $this->response->send();
    }

    /**
     * exception logging
     * @param \Exception $e
     */
    protected function write_exception_log(\Exception $e)
    {
        $logArray = array(
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'code'    => $e->getCode(),
            'message' => $e->getMessage(),
            'trace'   => $e->getTraceAsString(),
        );
        $this->logger->write_log($logArray);
    }

    /**
     * @method translate
     * @auth: ledung
     * @param  $url
     * @return
     */
    public function _($key, $field = null)
    {
        if (!empty($field) && is_array($field)) {
            return $this->trans->_($key, $field);
        } else if (!empty($field) && !is_array($field)) {
            return $this->trans->_($key). ' ' . $field;
        } else {
            return $this->trans->_($key);
        }
    }
}
