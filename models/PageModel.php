<?php

namespace impresja\impresja\models;

use impresja\impresja\Application;
use impresja\impresja\db\DbModel;

class PageModel extends DbModel
{
    public ?int $id = null;
    public ?string $title = '';
    public string $path = '';
    public string $controller = 'Main';
    public string $action = 'page';
    public int $noIndex = 0;
    public int $editable = 0;
    public string $lang = 'PL';
    public ?string $description = null;
    public ?string $content = null;
    public string $namespace = '';

    public static function tableName(): string
    {
        return 'imp_page';
    }

    public static function primaryKey(): string
    {
        return 'id';
    }

    public static function attributes(): array
    {
        return ['title', 'description', 'path', 'controller', 'action', 'content', 'lang', 'noIndex', 'editable'];
    }

    public function labels(): array
    {
        return [
            'title' => 'Nazwa',
            'description' => 'Description',
            'path' => 'Ścieżka',
            'controller' => 'Kontroler',
            'action' => 'Akcja',
            'content' => 'Treść',
            'lang' => 'Język',
            'noIndex' => 'Nie indeksuj',
            'editable' => 'Edytowalna'
        ];
    }

    public function grid(): array
    {
        return [
            'path' => 'left',
            'title' => 'left',
            'controller' => 'left',
            'action' => 'left',
            'lang' => 'center',
            'noIndex' => 'center'
        ];
    }

    public function rules(): array
    {
        return [
            'description' => [[self::RULE_MAX, 'max' => 155]],
            'path' => [[self::RULE_UNIQUE, 'class' => self::class]]
        ];
    }

    public static function getNamespace(string $controller): string
    {
        $namespace = 'impresja\controllers\\' . $controller . 'Controller';
        if (class_exists($namespace)) {
            return $namespace;
        }
        throw new \Exception("Controller dosn't exists.", 404);
    }

    public static function getPage(string $path)
    {
        $page = \impresja\impresja\models\PageModel::class::findOne(['path' => $path]);
        if ($page) {
            try {
                $page->namespace = self::getNamespace($page->controller);
            } catch (\Exception $e) {
                Application::$app->response->setStatusCode($e->getCode());
                echo Application::$app->view->renderView('_error', ['exception' => $e]);
                return null;
            }
            return $page;
        }
        return null;
    }

    public function getSelectData($attribute): array
    {
        if ($attribute == 'lang') {
            $lang['PL'] = 'Polski';
            $lang['EN'] = 'Angielski';
            $lang['DE'] = 'Niemiecki';
            return $lang;
        }
        return [];
    }
}
