<?php

namespace impresja\impresja\models;

use impresja\impresja\db\DbModel;

abstract class UserModel extends DbModel
{
    abstract public function getDisplayName(): string;
}
