<?php

namespace impresja\impresja\models;

use impresja\impresja\db\DbModel;

class ModuleModel extends DbModel
{
    public string $icon;
    public string $title;
    public string $description;
    public string $url;

    public function __construct($icon, $title, $url)
    {
        $this->icon = $icon;
        $this->title = $title;
        $this->url = $url;
    }

    public static function tableName(): string
    {
        return 'imp_modules';
    }

    public static function primaryKey(): string
    {
        return 'id';
    }

    public static function attributes(): array
    {
        return ['id', 'title', 'icon', 'order'];
    }

    public function rules(): array
    {
        return [
            'title' => [self::RULE_REQUIRED],
            'icon' => [self::RULE_REQUIRED],
            'order' => [self::RULE_REQUIRED]
        ];
    }
}
