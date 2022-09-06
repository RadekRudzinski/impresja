<?php

namespace impresja\impresja\metadata;

use impresja\impresja\interfaces\IMetaData;

class MetaCssFile implements IMetaData
{
    private string $file;


    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function render(): string
    {
        return '<link rel="stylesheet" href="' . $this->file . '">';
    }
}
