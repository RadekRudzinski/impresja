<?php

namespace impresja\impresja\models;

use impresja\impresja\db\DbModel;

class ModuleSectionModel extends DbModel
{
    public string $icon;
    public string $title;
    public string $order;
    public array $items = [];

    public function __construct($title, ?string $icon = null)
    {
        $this->icon = $icon ?? '<i class="fa-solid fa-gears"></i>';
        $this->title = $title;
    }

    public function __toString()
    {
        return "<div class='module'>$this->icon $this->title</div>";
    }

    public static function tableName(): string
    {
        return 'imp_modules_section';
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
