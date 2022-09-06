<?php

namespace impresja\impresja\metadata;

use impresja\impresja\interfaces\IMetaData;

class MetaCanonical implements IMetaData
{
    private string $path;


    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function render(): string
    {
        return '<link rel="canonical" href="' . $this->path . '">';
    }
}
