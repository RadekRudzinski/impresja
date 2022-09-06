<?php

namespace impresja\impresja;

use impresja\impresja\models\ConfigModel;
use impresja\impresja\interfaces\IConfig;

class Config implements IConfig
{
    public ConfigModel $model;

    public function __construct()
    {
        $this->model = new ConfigModel();
    }

    public function loadDefaultConfig(array $areas)
    {
        $config = $this->model->getDefaultConfig($areas);
        foreach ($config as $k => $v) {
            $_ENV[$k] = $v;
        }
    }

    public function get(string $key): string
    {
        return $_ENV[$key] ?? null;
    }
}
