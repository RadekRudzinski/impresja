<?php

namespace impresja\impresja\form;

use impresja\impresja\Model;

class InputField extends BaseField
{
    public const TYPE_TEXT = 'text';
    public const TYPE_PASSWORD = 'password';
    public const TYPE_NUMBER = 'number';
    public const TYPE_EMAIL = 'email';
    public const TYPE_CURRENCY = 'number';
    public const TYPE_INTEGER = 'number';
    public const TYPE_HIDDEN = 'hidden';
    public const TYPE_FILE = 'file';
    public string $type;
    public string $option = '';

    public string $label;


    public function __construct(Model $model, string $attribute)
    {
        $this->type = self::TYPE_TEXT;
        parent::__construct($model, $attribute);
    }


    public function currencyField()
    {
        $this->type = self::TYPE_CURRENCY;
        $this->option = ' step = "0.01"';
        return $this;
    }

    public function integerField()
    {
        $this->type = self::TYPE_INTEGER;
        $this->option = ' step = "1"';
        return $this;
    }

    public function passwordField()
    {
        $this->type = self::TYPE_PASSWORD;
        return $this;
    }

    public function emailField()
    {
        $this->type = self::TYPE_EMAIL;
        return $this;
    }

    public function hiddenField()
    {
        $this->type = self::TYPE_HIDDEN;
        return $this;
    }
    public function fileField($multiple = false)
    {
        $this->option = $multiple ? ' step = "1"' : '';
        $this->type = self::TYPE_FILE;
        return $this;
    }

    public function numberField()
    {
        $this->type = self::TYPE_NUMBER;
        return $this;
    }
    public function renderInput()
    {
        $required = '';
        if (isset($this->model->rules()[$this->attribute])) {
            $required = in_array('required', $this->model->rules()[$this->attribute]) ? ' required' : '';
        }
        return sprintf(
            '<input type="%s" value="%s" class="form-control%s" id="%s" name="%s"%s%s>',
            $this->type,
            $this->model->{$this->attribute},
            $this->model->hasError($this->attribute) ? ' is-invalid' : '',
            $this->attribute,
            $this->attribute,
            $this->option,
            $required
        );
    }
}
