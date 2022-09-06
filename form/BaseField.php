<?php

namespace impresja\impresja\form;

use impresja\impresja\Model;

abstract class BaseField
{
    public Model $model;
    public string $attribute;

    public function __construct(Model $model, string $attribute)
    {
        $this->model = $model;
        $this->attribute = $attribute;
    }

    abstract public function renderInput();

    public function __toString()
    {
        if (isset($this->type) && $this->type == 'hidden') {
            return $this->renderInput();
        }
        return sprintf(
            '<label for="%s" class="form-label">%s</label>
            %s
             <div class="invalid-feedback">%s</div>',
            $this->attribute,
            $this->model->getLabel($this->attribute),
            $this->renderInput(),
            $this->model->getFirstError($this->attribute)
        );
    }
}
