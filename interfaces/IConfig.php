<?php

namespace impresja\impresja\interfaces;

interface IConfig
{
    public function get(string $key): string;
}
