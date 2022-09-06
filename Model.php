<?php

namespace impresja\impresja;

abstract class Model
{
    public const RULE_REQUIRED = 'required';
    public const RULE_EMAIL = 'email';
    public const RULE_MIN = 'min';
    public const RULE_MAX = 'max';
    public const RULE_MATCH = 'match';
    public const RULE_UNIQUE = 'unique';
    public const RULE_INT = 'integer';
    public const RULE_FLOAT = 'float';
    public const RULE_CURRENCY = 'currency';
    public array $errors = [];

    public function loadData($data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    abstract public function rules(): array;

    public function validate()
    {
        foreach ($this->rules() as $attribute => $rules) {
            $value = $this->{$attribute};
            foreach ($rules as $rule) {
                $ruleName = $rule;
                if (!is_string($ruleName)) {
                    $ruleName = $rule[0];
                }
                if ($ruleName === self::RULE_INT && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->addErrorForRule($attribute, self::RULE_INT);
                }
                if ($ruleName === self::RULE_FLOAT && !filter_var($value, FILTER_VALIDATE_FLOAT)) {
                    $this->addErrorForRule($attribute, self::RULE_FLOAT);
                }
                if ($ruleName === self::RULE_CURRENCY && (!filter_var($value, FILTER_VALIDATE_FLOAT) && $value !== NULL && $value != 0)) {
                    $this->addErrorForRule($attribute, self::RULE_CURRENCY);
                }
                if ($ruleName === self::RULE_REQUIRED && !$value) {
                    $this->addErrorForRule($attribute, self::RULE_REQUIRED);
                }
                if ($ruleName === self::RULE_EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addErrorForRule($attribute, self::RULE_EMAIL);
                }
                if ($ruleName === self::RULE_MIN && strlen($value) < $rule['min']) {
                    $this->addErrorForRule($attribute, self::RULE_MIN, $rule);
                }
                if ($ruleName === self::RULE_MAX && strlen($value) > $rule['max']) {
                    $this->addErrorForRule($attribute, self::RULE_MAX, $rule);
                }
                if ($ruleName === self::RULE_MATCH && $value !== $this->{$rule['match']}) {
                    $this->addErrorForRule($attribute, self::RULE_MATCH, ['match' => $this->getLabel($rule['match'])]);
                }
                if ($ruleName === self::RULE_UNIQUE) {
                    $className = $rule['class'];
                    $uniqueAttr = $rule['attribute'] ?? $attribute;
                    $tableName = $className::tableName();
                    $statement = Application::$app->db->prepare("SELECT * FROM $tableName WHERE $uniqueAttr = :attr AND `id` <> '$this->id'");
                    $statement->bindValue(":attr", $value);
                    $statement->execute();
                    $record = $statement->fetchObject();
                    if ($record) {
                        $this->addErrorForRule($attribute, self::RULE_UNIQUE, ['field' => $this->getLabel($attribute)]);
                    }
                }
            }
        }
        return empty($this->errors);
    }

    private function addErrorForRule(string $attribute, string $rule, $params = [])
    {
        $message = $this->errorMesseges()[$rule] ?? '';

        foreach ($params as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }
        $this->errors[$attribute][] = $message;
    }

    public function addError(string $attribute, string $message)
    {
        $this->errors[$attribute][] = $message;
    }

    public function errorMesseges()
    {
        return [
            self::RULE_REQUIRED => 'To pole jest wymagane',
            self::RULE_EMAIL => 'Wprowadź prawidłowy adres email',
            self::RULE_MIN => 'Minimalna długość to {min} znaków',
            self::RULE_MAX => 'Maksymalna długość to {max} znaków',
            self::RULE_MATCH => 'To pole musi mieć taką samą wartość jak {match}',
            self::RULE_UNIQUE => 'Wartość pola {field} już istnieje w bazie',
            self::RULE_INT => 'Wprowadź prawidłową liczbę całkowitą',
            self::RULE_FLOAT => 'Wprowadź prawidłową liczbę',
            self::RULE_CURRENCY => 'Wprowadź prawidłową kwotę',
        ];
    }

    public function hasError($attribute)
    {
        return $this->errors[$attribute] ?? false;
    }

    public function getFirstError($attribute)
    {
        return $this->errors[$attribute][0] ?? false;
    }

    protected function labels(): array
    {
        return [];
    }

    public function getLabel($attribute)
    {
        return $this->labels()[$attribute] ?? $attribute;
    }

    public function getSelectData($attribute): array
    {
        return [];
    }
}
