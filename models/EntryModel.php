<?php

namespace impresja\impresja\models;

use impresja\impresja\db\DbModel;

class EntryModel extends DbModel
{
    public ?string $id = null;
    public string $id_session;
    public string $url;
    public string $date;

    public function __construct()
    {
        $this->id_session = session_id();
        $this->url = $_SERVER['REQUEST_URI'];
        $this->date = date('Y-m-d H:i');
        try {
            $this->save();
        } catch (\Exception $e) {
        }
    }

    public static function tableName(): string
    {
        return 'imp_entries';
    }

    public static function primaryKey(): string
    {
        return 'id';
    }

    public static function attributes(): array
    {
        return ['id', 'id_session', 'url', 'date'];
    }

    public function rules(): array
    {
        return [
            'id_session' => [self::RULE_REQUIRED],
            'url' => [self::RULE_REQUIRED],
            'date' => [self::RULE_REQUIRED]
        ];
    }
}
