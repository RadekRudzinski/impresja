<?php

namespace impresja\impresja\models;

use impresja\impresja\db\DbModel;

class SessionModel extends DbModel
{
    public string $id;
    public ?int $id_cookie = null;
    public string $year;
    public string $month;
    public string $day;
    public string $hour;
    public string $minute;
    public string $date;
    public string $host;
    public string $ip;
    public ?string $browser;
    public ?string $referer;

    public function __construct()
    {
        $host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        if (!strpos($host, "googlebot.com")) {
            if (!isset($_SESSION['is_saved'])) {
                $this->id = session_id();
                $this->date = time();
                $this->year = date('Y');
                $this->month = date('m');
                $this->day = date('d');
                $this->hour = date('H');
                $this->minute = date('i');
                $this->host = $host;
                $this->ip = $_SERVER['REMOTE_ADDR'];
                $this->browser = $_SERVER['HTTP_USER_AGENT'] ?? null;
                $this->referer = $GLOBALS['_SERVER']['HTTP_REFERER'] ?? null;
                try {
                    $this->save();
                    $_SESSION['is_saved'] = 1;
                } catch (\Exception $e) {
                }
            }
            $entry = new EntryModel();
        }
    }

    public static function tableName(): string
    {
        return 'imp_sessions';
    }

    public static function primaryKey(): string
    {
        return 'id';
    }

    public static function attributes(): array
    {
        return ['id', 'id_cookie', 'year', 'month', 'day', 'hour', 'minute', 'date', 'host', 'ip', 'browser', 'referer'];
    }

    public function rules(): array
    {
        return [
            'id' => [self::RULE_REQUIRED],
            'date' => [self::RULE_REQUIRED]
        ];
    }
}
