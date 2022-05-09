<?php

namespace impresja\core;

use impresja\core\middlewares\BaseMiddleware;

class Controller
{
    public string $layout = 'main';
    public array $middlewares = [];
    public string $action = '';

    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    public function render($view, $params = [])
    {
        return Application::$app->view->renderView($view, $params);
    }

    public function registerMiddleware(BaseMiddleware $middleware)
    {
        $this->middlewares[] = $middleware;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
