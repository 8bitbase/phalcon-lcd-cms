<?php

/**
 * @category ProjectShare
 * @copyright (c) open-source Phalcon framework
 * @license MIT License (c) 2017 8bit baseball
 */

namespace Lcd\App\Backend;

use \Phalcon\DiInterface;
use \Phalcon\Mvc\Dispatcher;
use \Phalcon\Mvc\ModuleDefinitionInterface;
use \Phalcon\Mvc\View;

class BackendModule implements ModuleDefinitionInterface
{
    public function registerAutoloaders(DiInterface $di = null)
    {
    }

    /**
     * DI registration related services
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
     * DI registration dispatcher service
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
            // The default is set to the background scheduler
            $dispatcher->setDefaultNamespace($systemConfig->app->root_namespace . '\\App\\Backend\\Controllers');
            return $dispatcher;
        }, true);
    }

    /**
     * DI registered url service
     * @param DiInterface $di
     */
    protected function registerUrlService(DiInterface $di)
    {
        $systemConfig = $di->get('systemConfig');
        $di->setShared('url', function () use ($systemConfig) {
            $url = new \Phalcon\Mvc\Url();
            $url->setBaseUri($systemConfig->app->backend->module_pathinfo);
            $url->setStaticBaseUri($systemConfig->app->backend->assets_url);
            return $url;
        });
    }

    /**
     * DI registered view service
     * @param DiInterface $di
     */
    protected function registerViewService(DiInterface $di)
    {
        $systemConfig = $di->get('systemConfig');
        $di->setShared('view', function () use ($systemConfig) {
            $view = new \Phalcon\Mvc\View();
            $view->setViewsDir($systemConfig->app->backend->views);
            $view->registerEngines(array(
                '.phtml' => function ($view, $di) use ($systemConfig) {
                    $volt = new \Lcd\App\Core\PhalBaseVolt($view, $di);

                    $volt->setOptions(array(
                        'compileAlways' => $systemConfig->app->backend->is_compiled,
                        'compiledPath'  => $systemConfig->app->backend->compiled_path,
                    ));

                    $volt->initFunction();
                    return $volt;
                },
            ));
            return $view;
        });
    }
}
