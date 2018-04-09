<?php
/**
 * Created by Serhii Polishchuk
 * Yet another test job
 */

namespace App\Model;

abstract class AbstractModel
{
    protected $errors;

    public function __construct(array $data)
    {
        $this->setData(
            array_keys(get_object_vars($this)),
            $data
        );
        $this->errors = [];
    }

    abstract public function validate(): bool;

    protected function setData(array $fields, $data)
    {
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $this->{$field} = $data[$field];
            }
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
