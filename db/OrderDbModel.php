<?php

namespace impresja\impresja\db;

use impresja\impresja\db\DbModel;

abstract class OrderDbModel extends DbModel
{
    public ?string $subOrderField = null;
    private bool $raport = false;
    // private bool $raport = true;


    public static function getLastOrder($condition = null)
    {
        $condition = $condition ? "WHERE " . $condition : null;
        $statement = static::prepare("SELECT `order` FROM " . static::tableName() . " $condition ORDER BY `order` DESC LIMIT 1");
        $statement->execute();
        $arr = $statement->fetch();
        return isset($arr['order']) ? $arr['order'] : 0;
    }


    public function up()
    {
        $subOrderValue = $this->subOrderField ? $this->{$this->subOrderField} : null;
        $this->order = $this->newOrder($this->order - 1, $subOrderValue);
        $this->save();
    }

    public function down()
    {
        $subOrderValue = $this->subOrderField ? $this->{$this->subOrderField} : null;
        $this->order = $this->newOrder($this->order + 1, $subOrderValue);
        $this->save();
    }


    private function orderUp(int $order, $condition = null)
    {
        $condition = $condition ? "AND " . $condition : null;
        $query = "UPDATE " . static::tableName() . " SET `order` = `order` + 1 WHERE `order` >= '$order' $condition";
        if ($this->id)  $query .= " AND `order` < '$this->order'";
        $statement = static::prepare($query);
        $statement->execute();
    }

    private function orderDown(int $order, $condition = null)
    {
        $condition = $condition ? "AND " . $condition : null;
        $statement = static::prepare("UPDATE " . static::tableName() . " SET `order` = `order` - 1 WHERE `order` <= '$order' AND `order`> '$this->order' $condition");
        $statement->execute();
    }

    public function newOrder($newOrder, $subOrderValue = null)
    {
        $condition = $this->subOrderField !== null ? "`$this->subOrderField` = '" . ($subOrderValue ?? $this->{$this->subOrderField}) . "'" : null;
        if ($this->raport) echo "condition = $condition<br>";
        $last = $this->getLastOrder($condition);
        if (!$this->id) {
            if (!$newOrder or ($newOrder > $last)) {
                if ($this->raport) echo "Nowy, nie ma order, lub jest większy niż ostatni";
                return $last + 1;
            }
            if ($this->raport) echo "Nowy, podałem order";
            self::orderUp($newOrder, $condition);
            return $newOrder;
        }
        if (($this->order == $newOrder) or (!$newOrder)) {
            if ($this->raport) echo "Jest, nic się nie zmienia";
            return $this->order;
        }
        if ($this->order != $newOrder) {
            if ($newOrder < $this->order) {
                if ($this->raport) echo "Jest, nowy mniejszy od starego";
                self::orderUp($newOrder, $condition);
                return $newOrder;
            }
            if ($this->raport) echo "Jest, nowy większy od starego";
            $newOrder = $newOrder > $last ? $last : $newOrder;
            self::orderDown($newOrder, $condition);
            return $newOrder;
        }
    }

    public function prepareData($data): array
    {
        $data = parent::prepareData($data);

        if (in_array('order', $this->attributes())) {
            $subOrderValue = $this->subOrderField ? $data[$this->subOrderField] : null;
            $data['order'] = $this->newOrder($data['order'], $subOrderValue);
        }
        return $data;
    }

    public function delete()
    {
        parent::delete();
        $condition = $this->subOrderField !== null ? " AND `$this->subOrderField` = '" . $this->{$this->subOrderField} . "'" : null;
        static::execute("UPDATE " . $this->tableName() . " SET `order` = `order` - 1 WHERE `order` > '$this->order'$condition");
    }
}
