<?php

namespace impresja\impresja\db;

use impresja\impresja\db\DbModel;

abstract class NestedDbModel extends DbModel
{
    public $lft;
    public $rgt;
    public ?int $id_parent = null;
    public ?int $exludeNodeLft = NULL;
    public ?int $exludeNodeRgt = NULL;

    abstract public static function tableName(): string;
    abstract public static function attributes(): array;
    abstract public static function primaryKey(): string;

    public function save()
    {
        $tableName = $this->tableName();

        static::execute("LOCK TABLE $tableName WRITE");

        if ($this->{$this->primaryKey()}) {
            $this->edit();
        } else {
            $this->add();
        }
        $id = parent::save();
        static::execute("UNLOCK TABLES");
        return $id;
    }

    private function add()
    {
        $tableName = $this->tableName();
        $rightId = $this->{$this->subOrderField};
        // Sprawdzam, czy jest rgt dla rekordu o id=$rightId
        $statement = self::execute("SELECT lft FROM $tableName WHERE id = '$rightId'");
        $r =  $statement->fetch();
        // Dodawany element ma być przypisany do rodzica
        if (isset($r['lft'])) {
            static::execute("UPDATE $tableName SET rgt = rgt + 2 WHERE rgt > $r[lft]");
            static::execute("UPDATE $tableName SET lft = lft + 2 WHERE lft > $r[lft]");
            $this->lft = $r['lft'] + 1;
            $this->rgt = $r['lft'] + 2;
        }
        // nie ma rgt rodzica (nie podano, albo podano nieistniejące id rodzica), czyli równorzędny dla głównego
        else {
            static::execute("UPDATE $tableName SET `lft`=`lft`+2, `rgt`=`rgt`+2");
            $this->lft = 1;
            $this->rgt = 2;
        }
    }


    private function edit()
    {
        $tableName = $this->tableName();
        $statement = self::execute("SELECT id_parent,lft,rgt FROM $tableName WHERE `" . $this->primaryKey() . "` = '" . $this->{$this->primaryKey()} . "'");
        $old =  $statement->fetch();

        if ($this->id_parent != $old['id_parent']) {
            // ile miejsca potrzebuję
            $offset = $old['rgt'] - $old['lft'] + 1;

            $statement = self::execute("SELECT rgt FROM $tableName WHERE id = '$this->id_parent'");
            $newParent =  $statement->fetch();
            $newParentRight = $newParent ? $newParent['rgt'] : 0;

            // robię miejsce
            static::execute("UPDATE $tableName SET lft = lft + $offset WHERE lft > $newParentRight");
            static::execute("UPDATE $tableName SET rgt = rgt + $offset WHERE rgt >= $newParentRight");
            // jeśli gałąź przesuwam w lewo to została też odsunięta, więc obliczam jeszcze raz lft i rgt
            if ($old['lft'] > $newParentRight) {
                $old['lft'] = $old['lft'] + $offset;
                $old['rgt'] = $old['rgt'] + $offset;
            }
            // przesuwam gałąź do nowego rodzica
            $newParentRight = !$newParentRight ? 1 : $newParentRight;
            $newOffset = $old['lft'] - $newParentRight;
            $this->lft = $old['lft'] - $newOffset;
            $this->rgt = $old['rgt'] - $newOffset;
            static::execute("UPDATE $tableName SET lft = lft - $newOffset, rgt = rgt - $newOffset WHERE lft BETWEEN $old[lft] AND $old[rgt]");
            // niweluję powstałą lukę
            if ($this->lft > $old['rgt']) {
                $this->lft = $this->lft - $offset;
            }
            if ($this->rgt > $old['rgt']) {
                $this->rgt = $this->rgt - $offset;
            }
            static::execute("UPDATE $tableName SET lft = lft - $offset WHERE lft > $old[rgt]");
            static::execute("UPDATE $tableName SET rgt = rgt - $offset WHERE rgt > $old[rgt]");
        }
    }

    public function delete()
    {
        $tableName = $this->tableName();
        static::execute("LOCK TABLE $tableName WRITE");
        $statement = self::execute("SELECT rgt, lft FROM $tableName WHERE `" . $this->primaryKey() . "` = '" . $this->{$this->primaryKey()} . "'");
        $param =  $statement->fetch();
        if (isset($param['lft'])) {
            // Kasuję gałąź
            static::execute("DELETE FROM $tableName WHERE lft>=$param[lft] AND rgt<=$param[rgt]");
            // Przesuwam pozostałe
            $offset = $param['rgt'] - $param['lft'] + 1;
            static::execute("UPDATE $tableName SET lft=lft-$offset WHERE lft > $param[lft]");
            static::execute("UPDATE $tableName SET rgt=rgt-$offset WHERE rgt > $param[rgt]");
        }
        static::execute("UNLOCK TABLES");
        return true;
    }

    public function excludeNode($lft, $rgt)
    {
        $this->exludeNodeLft = $lft;
        $this->exludeNodeRgt = $rgt;
    }

    public function getParents()
    {
        $statement = static::execute("SELECT parent.*
    	FROM " . $this->tableName() . " AS node, " . $this->tableName() . " AS parent
    	WHERE node.lft BETWEEN parent.lft AND parent.rgt
    	AND node.id = '$this->id' AND parent.id<>'$this->id'
    	ORDER BY parent.lft");
        while ($p = $statement->fetch()) {
            $arr[] = $p;
        }
        return $arr;
    }

    public function getBranchIds()
    {
        $arr[] = $this->id;
        $statement = static::execute("SELECT id FROM " . $this->tableName() . " 
    	WHERE (lft < '$this->lft' AND rgt > '$this->rgt') OR (lft > '$this->lft' AND rgt < '$this->rgt')");
        while ($p = $statement->fetch()) {
            $arr[] = $p['id'];
        }
        return $arr;
    }

    public function getChildrenIds()
    {
        $arr[] = $this->id;
        $statement = static::execute("SELECT id FROM " . $this->tableName() . " 
    	WHERE lft > '$this->lft' AND rgt < '$this->rgt'");
        while ($p = $statement->fetch()) {
            $arr[] = $p['id'];
        }
        return $arr;
    }

    // public function getAllChildren()
    // {
    //     $query = "SELECT
    // 	(SELECT count(parent.id)-1 FROM $this->tableName() AS parent WHERE node.lft BETWEEN parent.lft AND parent.rgt) AS depth,
    //     node.* FROM $this->tableName() AS node WHERE node.lft BETWEEN $this->lft AND $this->rgt AND node.id<>$this->id ORDER BY node.lft";
    //     $GLOBALS['sql_' . DB]->query($query);

    //     while ($a = $GLOBALS['sql_' . DB]->rekord()) {
    //         $parents[$a['depth'] + 1]['title'] = $a['title'];
    //         $parents[$a['depth'] + 1]['id']    = $a['id'];
    //         for ($i = 1; $i <= $a['depth']; $i++) {
    //             $a['parents'][$i]['id']    = $parents[$i]['id'];
    //             $a['parents'][$i]['title'] = $parents[$i]['title'];
    //         }
    //         $nodes[] = $a;
    //     }
    //     return $nodes;
    // }

    public function getCategoriesWithPath($limit = 0, $offset = 0)
    {
        $localOffset = 0;
        $query = "SELECT
        (SELECT count(parent.id)-1 FROM " . $this->tableName() . " AS parent WHERE node.lft BETWEEN parent.lft AND parent.rgt) AS depth,
        node.* FROM " . $this->tableName() . " AS node ";
        if ($this->exludeNodeLft and $this->exludeNodeRgt) {
            $query .= "WHERE node.lft < $this->exludeNodeLft OR node.rgt > $this->exludeNodeRgt ";
        }
        $query .= "ORDER BY node.lft";
        $statement = static::execute($query);

        while ($a = $statement->fetch()) {
            $a['parent'] = '';
            $parents[$a['depth'] + 1] = $a['title'];
            for ($i = 1; $i <= $a['depth']; $i++) {
                $a['parent'] .= "$parents[$i] &raquo; ";
            }

            if ($limit) {
                if ($localOffset >= $offset and $localOffset < $offset + $limit) {
                    $categories[$a['id']] = $a;
                } elseif ($localOffset > $offset + $limit) {
                    break;
                }
                $localOffset++;
            } else {
                $categories[$a['id']] = $a;
            }
        }
        return $categories;
    }

    public function up()
    {
        $tableName = $this->tableName();
        // szukam równorzędnego bloku po lewej
        $statement = static::execute("SELECT id, rgt, lft FROM $tableName WHERE rgt = '$this->lft' - 1");
        $prev = $statement->fetch();
        // jest poprzedni równorzędny element
        if ($prev) {
            $offset_left  = $prev['rgt'] - $prev['lft'] + 1;
            $offset_right = $this->rgt - $this->lft + 1;
            $offset_sum   = $offset_right + $offset_left;
            // robie miejsce
            static::execute("UPDATE $tableName SET lft = lft + $offset_right WHERE lft >= $prev[lft]");
            static::execute("UPDATE $tableName SET rgt = rgt + $offset_right WHERE rgt >= $prev[lft]");
            // przenoszę element z prawej na lewą
            static::execute("UPDATE $tableName SET lft = lft - $offset_sum, rgt = rgt - $offset_sum
    			WHERE lft >= ($this->lft + $offset_right) AND (rgt <=$this->rgt + $offset_right)");
            // likwiduję dziurę
            static::execute("UPDATE $tableName SET lft = lft - $offset_right WHERE lft > $this->rgt + $offset_right");
            static::execute("UPDATE $tableName SET rgt = rgt - $offset_right WHERE rgt > $this->rgt + $offset_right");
        }
    }

    public function down()
    {
        $tableName = $this->tableName();
        static::execute("LOCK TABLE $tableName WRITE");
        // szukam równorzędnego bloku po prawej
        $statement = static::execute("SELECT id, rgt, lft FROM $tableName WHERE lft = '$this->rgt' + 1");
        $next = $statement->fetch();
        // jest kolejny równorzędny element
        if ($next) {
            $offset_right = $next['rgt'] - $next['lft'] + 1;
            $offset_left  = $this->rgt - $this->lft + 1;
            $offset_sum   = $offset_right + $offset_left;
            // robie miejsce
            static::execute("UPDATE $tableName SET lft = lft + $offset_right WHERE lft >= $this->lft");
            static::execute("UPDATE $tableName SET rgt = rgt + $offset_right WHERE rgt >= $this->lft");
            // przenoszę element z prawej na lewą
            static::execute("UPDATE $tableName SET lft = lft - $offset_sum, rgt = rgt - $offset_sum
    			WHERE lft >= ($next[lft] + $offset_right) AND (rgt <=$next[rgt] + $offset_right)");
            // likwiduję dziurę
            static::execute("UPDATE $tableName SET lft = lft - $offset_right WHERE lft > $next[rgt] + $offset_right");
            static::execute("UPDATE $tableName SET rgt = rgt - $offset_right WHERE rgt > $next[rgt] + $offset_right");
        }
        static::execute("UNLOCK TABLES");
    }
}
