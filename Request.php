<?php

namespace impresja\impresja;


class Request
{
    private bool $pagination = false;
    private int $pageNumber = 1;

    public function getFullPath()
    {
        $path = filter_var($_SERVER['REQUEST_URI'] ?? '/', FILTER_SANITIZE_SPECIAL_CHARS);
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }
        $pathArray = explode("/", trim($path, "\x2F"));
        $potentialPage = is_numeric(end($pathArray));
        if ($this->pagination && $potentialPage) {
            $this->pageNumber = intval(end($pathArray));
            unset($pathArray[count($pathArray) - 1]);
        }
        return $pathArray;
    }

    public function getPath(): string
    {
        return $this->getFullPath()['0'];
    }

    public function getPageNumber(): string
    {
        $this->getFullPath();
        return $this->pageNumber;
    }

    public function setPagination()
    {
        $this->pagination = true;
    }

    public function method()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function isGet()
    {
        return $this->method() === 'get';
    }
    public function isPost()
    {
        return $this->method() === 'post' && $_POST;
    }

    public function getBody()
    {
        $body = [];
        if ($this->method() === 'get') {
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        if ($this->method() === 'post') {
            // foreach ($_POST as $key => $value) {
            //     $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            // }
            $body = $_POST;
        }
        return $body;
    }
}
