<?php

namespace impresja\impresja\form;

use impresja\impresja\Model;

class Form
{
    public static function begin(string $action, $method, $name = 'newForm', $multipart = false)
    {
        $mp = $multipart ? " enctype = 'multipart/form-data'" : '';
        echo "<form action='$action' method='$method' name='$name'$mp>";
        return new Form();
    }

    public static function end()
    {
        echo '</form>';
    }

    public function inputField(Model $model, $attribute)
    {
        return new InputField($model, $attribute);
    }
    public function textareaField(Model $model, $attribute)
    {
        return new TextareaField($model, $attribute);
    }
    public function checkboxField(Model $model, $attribute)
    {
        return new CheckboxField($model, $attribute);
    }
    public function selectField(Model $model, $attribute)
    {
        return new SelectField($model, $attribute);
    }
}
