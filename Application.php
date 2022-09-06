<?php

namespace impresja\impresja;

use impresja\impresja\db\Database;
use impresja\impresja\models\Page404Model;
use impresja\impresja\models\UserModel;
use impresja\impresja\models\PageModel;

class Application
{
    public const CONFIG_SITE = '1';
    public const CONFIG_ADMIN = '2';
    public const CONFIG_ALL = '3';

    public static string $ROOT_DIR;
    public string $startTime;
    public string $userClass;
    public static Application $app;
    public Session $session;
    public Controller $controller;
    public View $view;
    public Router $router;
    public Request $request;
    public Response $response;
    public Database $db;
    public Config $config;
    public ?PageModel $page;
    public ?UserModel $user;

    public function __construct($rootPath)
    {
        $this->startTime = microtime(true);
        self::$ROOT_DIR = $rootPath;
        self::$app = $this;
        $dotenv = \Dotenv\Dotenv::createImmutable(self::$ROOT_DIR);
        $dotenv->load();
        $this->db = new Database();
        $this->config = new Config();
        $this->userClass = \impresja\models\User::class;
        $this->session = new Session();
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);
        $this->controller = new Controller();
        $this->view = new View();
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
        $this->config->loadDefaultConfig([self::CONFIG_ALL, self::CONFIG_SITE]);
        $this->displayErrors($_ENV['DISPLAY_ERRORS']);
        $this->page = PageModel::getPage($this->request->getPath());
        $this->view->loadDefaultConfig();
        try {
            echo $this->router->resolve($this->page);
        } catch (\Exception $e) {
            #$this->response->setStatusCode($e->getCode());
            echo $this->view->renderView('_error', ['exception' => $e]);
        }
    }

    public function setController(\impresja\impresja\Controller $controller): void
    {
        $this->controller = $controller;
    }

    public function getController(): \impresja\impresja\Controller
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

    private function displayErrors($error)
    {
        ini_set('display_errors',  $error ?? 'off');
        error_reporting(E_ALL);
    }

    public function set404()
    {
        $page404 = new Page404Model(filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_SPECIAL_CHARS), $_SERVER['HTTP_REFERER'] ?? NULL);
        $page404->save();
        $this->response->setStatusCode(404);
        return $this->view->renderView("_404");
    }
}
