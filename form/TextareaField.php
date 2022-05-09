<?php

namespace impresja\core\form;

class TextareaField extends BaseField
{
    public string $label;

    public function renderInput()
    {
        return sprintf(
            '<textarea name ="%s" id="%s" class="form-control%s">%s</textarea>',
            $this->model->{$this->attribute},
            $this->model->{$this->attribute},
            $this->model->hasError($this->attribute) ? ' is-invalid' : '',
            $this->attribute
        );
    }
}
