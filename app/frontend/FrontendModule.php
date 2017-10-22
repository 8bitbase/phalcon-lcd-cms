<?php

/**
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Frontend;

use \Phalcon\DiInterface;
use \Phalcon\Mvc\Dispatcher;
use \Phalcon\Mvc\ModuleDefinitionInterface;
use \Phalcon\Mvc\View;

class FrontendModule implements ModuleDefinitionInterface
{
    public function registerAutoloaders(DiInterface $di = null)
    {
    }

    /**
     * DiInterface
     * @param DiInterface $di
     */
    public function registerServices(DiInterface $di)
    {
        // Noted.
        $this->registerDispatcherService($di);
        // Noted.
        $this->registerUrlService($di);
        // Noted.
        $this->registerViewService($di);
    }

    /**
     * DI dispatcher
     * @param DiInterface $di
     */
    protected function registerDispatcherService(DiInterface $di)
    {
        $systemConfig = $di->get('systemConfig');
        $di->set('dispatcher', function () use ($systemConfig) {
            $eventsManager = new \Phalcon\Events\Manager();
            $eventsManager->attach("dispatch:beforeException", function ($event, $dispatcher, $exception) {
                if ($event->getType() == 'beforeException') {
                    switch ($exception->getCode()) {
                        case \Phalcon\Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                            $dispatcher->forward(array(
                                'controller' => 'Index',
                                'action'     => 'notfound',
                            ));
                            return false;
                        case \Phalcon\Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                            $dispatcher->forward(array(
                                'controller' => 'Index',
                                'action'     => 'notfound',
                            ));
                            return false;
                    }
                }
            });
            $dispatcher = new \Phalcon\Mvc\Dispatcher();
            $dispatcher->setEventsManager($eventsManager);
            // The default is the foreground scheduler
            $dispatcher->setDefaultNamespace($systemConfig->app->root_namespace . '\\App\\Frontend\\Controllers');
            return $dispatcher;
        }, true);
    }

    /**
     * DI url
     * @param DiInterface $di
     */
    protected function registerUrlService(DiInterface $di)
    {
        $systemConfig = $di->get('systemConfig');
        $di->setShared('url', function () use ($systemConfig) {
            $url = new \Phalcon\Mvc\Url();
            $url->setBaseUri($systemConfig->app->frontend->module_pathinfo);
            return $url;
        });
    }

    /**
     * DI view
     * @param DiInterface $di
     */
    protected function registerViewService(DiInterface $di)
    {
        $systemConfig = $di->get('systemConfig');
        $di->setShared('view', function () use ($systemConfig) {
            $view = new \Phalcon\Mvc\View();
            $view->setViewsDir($systemConfig->app->frontend->views);
            $view->registerEngines(array(
                '.phtml' => function ($view, $di) use ($systemConfig) {
                    //$volt = new \Phalcon\Mvc\View\Engine\Volt($view, $di);
                    $volt = new \Lcd\App\Core\PhalBaseVolt($view, $di);
                    $volt->setOptions(array(
                        'compileAlways' => $systemConfig->app->frontend->is_compiled,
                        'compiledPath'  => $systemConfig->app->frontend->compiled_path,
                    ));
                    $volt->initFunction();
                    return $volt;
                },
            ));
            return $view;
        });
    }
}
