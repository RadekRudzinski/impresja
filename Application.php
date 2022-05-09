<?php

namespace impresja\core;

use impresja\core\db\Database;
use impresja\core\db\DbModel;

class Application
{
    public static string $ROOT_DIR;
    public string $userClass;
    public static Application $app;
    public Session $session;
    public Controller $controller;
    public View $view;
    public Router $router;
    public Request $request;
    public Response $response;
    public Database $db;
    public ?UserModel $user;

    public function __construct($rootPath, array $config)
    {
        self::$ROOT_DIR = $rootPath;
        self::$app = $this;
        $this->userClass = $config['userClass'];
        $this->session = new Session();
        $this->request = new Request();
        $this->response = new Response();
        $this->controller = new Controller();
        $this->view = new View();
        $this->router = new Router($this->request, $this->response);
        $this->db = new Database($config['db']);

        $primaryValue = $this->session->get('user');
        if ($primaryValue) {
            $primaryKey = $this->userClass::primaryKey();
            $this->user = $this->userClass::findOne([$primaryKey => $primaryValue]);
        } else {
            $this->user = null;
        }
    }

    public function run()
    {
        try {
            echo $this->router->resolve();
        } catch (\Exception $e) {
            $this->response->setStatusCode($e->getCode());
            echo $this->view->renderView('_error', ['exception' => $e]);
        }
    }

    public function setController(\impresja\core\Controller $controller): void
    {
        $this->controller = $controller;
    }

    public function getController(): \impresja\core\Controller
    {
        return $this->controller;
    }


    public function login(UserModel $user)
    {
        $this->user = $user;
        $primaryKey = $user->primaryKey();
        $primaryValue = $user->{$primaryKey};
        $this->session->set('user', $primaryValue);
        return true;
    }

    public function logout()
    {
        $this->user = null;
        $this->session->remove('user');
    }

    public static function isGuest()
    {
        return !self::$app->user;
    }
}
