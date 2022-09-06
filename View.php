<?php

namespace impresja\impresja;

use impresja\impresja\interfaces\IMetaData;

class View
{
    public string $title = '';
    public string $description = '';
    public int $noIndex = 0;
    public array $metaData = [];

    public function renderView(string $view, $params = [])
    {
        $viewContent = $this->renderOnlyView($view, $params);
        $layoutContent = $this->layoutContent($params);
        return str_replace('{{content}}', $viewContent, $layoutContent);
    }

    /*
    public function renderContent(string $content)
    {
        return str_replace('{{content}}', $content, $this->layoutContent());
    }
*/
    public function layoutContent($params)
    {
        foreach ($params as $key => $value) {
            $$key = $value;
        }
        $layout = Application::$app->controller->layout;
        ob_start();
        include_once Application::$ROOT_DIR . "/views/layouts/$layout.php";
        return ob_get_clean();
    }

    public function renderOnlyView($view, $params)
    {
        foreach ($params as $key => $value) {
            $$key = $value;
        }
        ob_start();
        try {
            if (file_exists(Application::$ROOT_DIR . "/views/$view.php")) {
                include_once Application::$ROOT_DIR . "/views/$view.php";
                return ob_get_clean();
            }
            throw new \Exception("View <b>$view</b> doesn't exists", 404);
        } catch (\Exception $e) {
            echo Application::$app->view->renderView('_error', ['exception' => $e]);
        }
    }

    public function loadDefaultConfig()
    {
        $this->title = Application::$app->page->title ?? $_ENV['TITLE'] ?? 'Impresja';
        $this->description = Application::$app->page->description ?? $_ENV['DESCRIPTION'] ?? '';
        $this->noIndex = Application::$app->page->noIndex ?? 0;
    }

    public function addMetaData(IMetaData $data)
    {
        $this->metaData[] = $data;
    }

    public function renderMetaData(): string
    {
        $metaString = '';
        foreach ($this->metaData as $meta) {
            $metaString .= $meta->render() . "\n";
        }
        return $metaString;
    }
}
