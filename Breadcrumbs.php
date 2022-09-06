<?php

namespace impresja\impresja;

class Breadcrumbs
{
    private array $breadcrumb = [['name' => '<i class="fa fa-home"></i>', 'link' => '/']];

    public function __construct($item)
    {

        if (is_object($item)) {
            return $this->getFromObject($item);
        }
        if (is_string($item)) {
            return $this->getFromString($item);
        }
        if (is_array($item)) {
            return $this->getFromArray($item);
        }
    }
    public function setMainPage(string $mainPage, string $link = '/')
    {
        $this->breadcrumb[0] = ['name' => $mainPage, 'link' => $link];
    }

    private function getFromString($string)
    {
        $this->breadcrumb[] = ['name' => $string, 'link' => null];
    }

    private function getFromArray($array)
    {
        foreach ($array as $val) {
            $this->breadcrumb[] = ['name' => $val[0], 'link' => $val[1] ?? null];
        }
    }

    private function getFromObject($object)
    {
        $this->breadcrumb = array_merge($this->breadcrumb, $object->getBreadcrumbs());
    }


    public function addBreadcrumb(string $name, ?string $link = null)
    {
        $this->breadcrumb[] = ['name' => $name, 'link' => $link];
    }

    public function getBreadcrumbs()
    {
        return $this->breadcrumb;
    }
}
