<?php

namespace impresja\impresja\db;

use impresja\impresja\Application;
use impresja\impresja\Model;

abstract class DbModel extends Model
{
    abstract public static function tableName(): string;
    abstract public static function attributes(): array;
    abstract public static function primaryKey(): string;


    public function save()
    {
        $edit = null;
        $tableName = $this->tableName();
        $attributes = $this->attributes();
        if ($this->{$this->primaryKey()}) {
            $params = array_map(fn ($attr) => "`$attr` = :$attr", $attributes);
            $statement = self::prepare("UPDATE $tableName SET " . implode(',', $params) . " WHERE `" . $this->primaryKey() . "` = '" . $this->{$this->primaryKey()} . "'");
            $edit = $this->{$this->primaryKey()};
        } else {
            $params = array_map(fn ($attr) => ":$attr", $attributes);
            foreach ($attributes as $k => $v) {
                $fields[$k] = "`$v`";
            }
            $statement = self::prepare("INSERT INTO $tableName (" . implode(',', $fields) . ") VALUES (" . implode(',', $params) . ")");
        }
        foreach ($attributes as $attribute) {
            $statement->bindValue(":$attribute", $this->{$attribute});
        }
        $statement->execute();

        return $edit ?? Application::$app->db->pdo->lastInsertId();
    }

    public function delete()
    {
        static::execute("DELETE FROM " . $this->tableName() . " WHERE `" . $this->primaryKey() . "` = '" . $this->{$this->primaryKey()} . "'");
    }

    public static function prepare($sql)
    {
        return Application::$app->db->pdo->prepare($sql);
    }
    public static function execute($sql)
    {
        $statement = self::prepare($sql);
        $statement->execute();
        return $statement;
    }

    public static function findOne($where, $constructorArgs = [])
    {
        $tableName = static::tableName();
        $attributes = array_keys($where);
        $sql = implode(" AND ", array_map(fn ($attr) => "`$attr` = :$attr", $attributes));
        $statement = self::prepare("SELECT * FROM $tableName WHERE $sql");
        foreach ($where as $key => $item) {
            $statement->bindValue(":$key", $item);
        }
        $statement->execute();
        return $statement->fetchObject(static::class, $constructorArgs);
    }

    public static function getAll($params)
    {
        $condition = $params['conditions'] ?? [];
        $tableName = static::tableName();
        $sql = "SELECT * FROM $tableName";
        if (count($condition)) {
            $sql .= " WHERE " . implode(" AND ", $condition);
        }
        if (isset($params['order'])) {
            $sql .= " ORDER BY `$params[order]`";
        } elseif (in_array('order', static::attributes())) {
            $sql .= " ORDER BY `order`";
        }
        if (isset($params['limit'])) {
            $offset = $params['offset'] ? $params['offset'] . ", " : '';
            $sql .= " LIMIT $offset $params[limit]";
        }
        $statement = self::prepare($sql);
        $statement->execute();
        return $statement->fetchAll();
    }

    public static function getCount($condition = [])
    {
        $query = "SELECT count(*) as `count` FROM " . static::tableName();
        if ($condition) {
            $query .= " WHERE " . implode(" AND ", $condition);
        }
        $statement = static::prepare($query);
        $statement->execute();
        return $statement->fetch()['count'];
    }

    public function searchFields(): array
    {
        return [];
    }

    public function strUrl($tekst)
    {
        $polskie = array(',', '&#232;', '&prime;', '&nbsp;', '&#8222;', '&#8221;', '&#8211;', ' - ', ' ', '-', "'", "/", "?", '"', ":", '!', '&', '&amp;', '#', ';', '[', ']', '(', ')', '`', '%', '”', '„', '…', 'ã', 'è', 'ą', 'ż', 'ó', 'ł', 'ć', 'ę', 'ś', 'ź', 'ń', 'Ą', 'Ż', 'Ó', 'Ł', 'Ć', 'Ę', 'Ś', 'Ź', 'Ń', '.');
        $miedzyn = array('-', 'e', '', '-', '', '', '', '-', '-', '-', "", "", "", "", "", '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'a', 'e', 'a', 'z', 'o', 'l', 'c', 'e', 's', 'z', 'n', 'a', 'z', 'o', 'l', 'c', 'e', 's', 'z', 'n', '');

        $tekst = str_replace($polskie, $miedzyn, $tekst);
        $tekst = iconv('utf-8', 'us-ascii//TRANSLIT//IGNORE', $tekst);
        // usuń wszytko co jest niedozwolonym znakiem
        $tekst = preg_replace('/[^0-9A-Za-z\-\.]+/', '', $tekst);
        // zredukuj liczbę myślników do jednego obok siebie
        $tekst = preg_replace('/[\-]+/', '-', $tekst);
        // usuwamy możliwe myślniki na początku i końcu
        $tekst = trim($tekst, '-');
        $tekst = stripslashes($tekst);
        // na wszelki wypadek
        return strtolower(urlencode($tekst));
    }

    public function prepareData($data): array
    {
        if (isset($data['id']) && !is_numeric($data['id'])) {
            unset($data['id']);
        }
        if (in_array('url_path', $this->attributes())) {
            $data['url_path'] = $data['url_path'] != '' ? $this->strUrl($data['url_path']) : $this->strUrl($data['title']);
        }
        if (in_array('active', $this->attributes())) {
            $data['active'] = isset($data['active']) ? 1 : 0;
        }
        return $data;
    }
}
