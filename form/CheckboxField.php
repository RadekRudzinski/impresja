<?php

namespace impresja\impresja\form;

class CheckboxField extends BaseField
{
    public string $label;

    public function renderInput()
    {
        return sprintf(
            '<input type="checkbox"%s name="%s" id="%s" value="1"%s>',
            $this->model->hasError($this->attribute) ? ' class="is-invalid"' : '',
            $this->attribute,
            $this->attribute,
            $this->model->{$this->attribute} ? ' checked = "checked"' : ''
        );
    }

    public function __toString()
    {
        return sprintf(
            '%s<label for="%s" class="form-label">%s</label><div class="invalid-feedback">%s</div>',
            $this->renderInput(),
            $this->attribute,
            $this->model->getLabel($this->attribute),
            $this->model->getFirstError($this->attribute)
        );
    }
}
