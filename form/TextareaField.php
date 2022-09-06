<?php

namespace impresja\impresja\form;

class TextareaField extends BaseField
{
    public string $label;

    public function renderInput()
    {
        return sprintf(
            '<textarea name ="%s" id="%s" class="form-control%s">%s</textarea><script>CKEDITOR.replace("' . $this->attribute . '");</script>',
            $this->attribute,
            $this->attribute,
            $this->model->hasError($this->attribute) ? ' is-invalid' : '',
            $this->model->{$this->attribute},
            $this->attribute
        );
    }
}
