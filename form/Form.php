<?php

namespace impresja\impresja\form;

use impresja\impresja\Model;

class Form
{
    public static function begin(string $action, $method)
    {
        echo sprintf('<form action="%s" method="%s">', $action, $method);
        return new Form();
    }

    public static function end()
    {
        echo '</form>';
    }

    public function inputField(Model $model, $attribute, $label)
    {
        return new InputField($model, $attribute, $label);
    }
}
