<?php

namespace impresja\impresja\models;

use impresja\impresja\db\DbModel;

class Page404Model extends DbModel
{
    protected ?int $id = null;
    protected string $url = '';
    protected ?string $referer = NULL;

    public function __construct(string $url, ?string $referer = NULL)
    {
        $this->url = $url;
        $this->referer = $referer;
    }

    public static function tableName(): string
    {
        return 'imp_page_404';
    }

    public static function primaryKey(): string
    {
        return 'id';
    }

    public static function attributes(): array
    {
        return ['id', 'url', 'referer'];
    }

    public function rules(): array
    {
        return [
            'url' => [self::RULE_REQUIRED],
            'referer' => [self::RULE_REQUIRED]
        ];
    }
}
