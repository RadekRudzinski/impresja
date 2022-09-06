<?php

namespace impresja\impresja\form;

class SelectField extends BaseField
{
    public string $label;
    public string $onChange = '';

    public function onChange(string $onChange)
    {
        $this->onChange = $onChange;
        return $this;
    }

    public function renderInput()
    {
        $options = "<option value=''></option>";
        $isInvalid = $this->model->hasError($this->attribute) ? ' is-invalid' : '';
        $required = '';
        if (isset($this->model->rules()[$this->attribute])) {
            $required = in_array('required', $this->model->rules()[$this->attribute]) ? ' required' : '';
        }

        $onChange = $this->onChange ? " onchange='$this->onChange'" : '';

        foreach ($this->model->getSelectData($this->attribute) as $k => $v) {
            $options .= sprintf(
                "<option value='$k'%s>$v</option>",
                $this->model->{$this->attribute} == $k ? " selected" : ""
            );
        }

        $select = "<select name ='$this->attribute' id='$this->attribute' class='form-control$isInvalid'$onChange$required>$options</select>";
        return $select;
    }
}
