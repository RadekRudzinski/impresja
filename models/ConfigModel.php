<?php

namespace impresja\impresja\models;

use impresja\impresja\db\DbModel;

class ConfigModel extends DbModel
{

    public static function tableName(): string
    {
        return 'imp_config';
    }

    public static function primaryKey(): string
    {
        return 'id';
    }

    public static function attributes(): array
    {
        return ['key', 'value', 'description', 'area'];
    }

    public function rules(): array
    {
        return [
            'key' => [self::RULE_REQUIRED]
        ];
    }

    public static function getDefaultConfig(array $areas): array
    {
        $config = [];
        $sql = implode(" OR ", array_map(fn ($area) => "`area` = '$area'", $areas));
        $statement = self::prepare("SELECT `key`, `value` FROM imp_config WHERE $sql");
        $statement->execute();
        $arr = $statement->fetchAll();
        foreach ($arr as $c) {
            $config[$c['key']] = $c['value'];
        }
        return $config;
    }
}
