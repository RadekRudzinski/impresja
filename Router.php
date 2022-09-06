<?php

namespace impresja\impresja;

use impresja\controllers\RedirectController;
use impresja\impresja\models\PageModel;

class Router
{
    public Request $request;
    public Response $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }/*
    public function get($path, $callback)
    {
        $this->routes['get'][$path] = $callback;
    }

    public function post($path, $callback)
    {
        $this->routes['post'][$path] = $callback;
    }
*/

    public function resolve(?PageModel $page)
    {
        if ($page === null) {
            RedirectController::redirect();
            return Application::$app->set404();
        }
        /*
        if (is_string($callback)) {
            return Application::$app->view->renderView($callback);
        }
        */
        $controller = new $page->namespace();
        Application::$app->controller = $controller;
        $controller->action = $page->action;
        $middlewares = $controller->getMiddlewares();
        foreach ($middlewares as $middleware) {
            $middleware->execute();
        }
        try {
            if (method_exists($controller, $controller->action)) {
                $return =  call_user_func([$controller, $controller->action], $this->request, $this->response);
                Application::$app->session->save();
                return $return;
            }
            throw new \Exception("This method dosn't exists in this controller", 404);
        } catch (\Exception $e) {
            if (is_numeric($e->getCode()))
                $this->response->setStatusCode($e->getCode());
            echo Application::$app->view->renderView('_error', ['exception' => $e]);
        }
    }
}
