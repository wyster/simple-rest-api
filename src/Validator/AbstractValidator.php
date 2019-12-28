<?php declare(strict_types=1);

namespace App\Validator;

use Zend\Validator;

abstract class AbstractValidator
{
    private array $fields = [];
    private array $messages = [];

    protected function addField(array $field): void
    {
        $this->fields[$field['name']] = $field;
    }

    protected function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @inheritDoc
     */
    public function isValid($value)
    {
        foreach ($this->getFields() as $field) {
            if ($field['required'] && !array_key_exists($field['name'], $value)) {
                $this->messages = [$field['name'] => ['Is required']];
                return false;
            }

            /**
             * @var Validator\ValidatorInterface $validator
             */
            foreach ($field['validators'] as $validator) {
                if ($validator->isValid($value[$field['name']])) {
                    continue;
                }

                $this->messages = [$field['name'] => array_values($validator->getMessages())];
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
