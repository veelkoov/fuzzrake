<?php

declare(strict_types=1);

namespace App\DataDefinitions\Fields;

use ArrayIterator;
use Closure;
use IteratorAggregate;
use Traversable;

class FieldsList implements IteratorAggregate
{
    private array $fields = [];

    /**
     * @param Field[] $fields
     */
    public function __construct(
        array $fields,
    ) {
        foreach ($fields as $field) {
            $this->fields[$field->name] = $field;
        }
    }

    public function filtered(Closure $filter): FieldsList
    {
        return new FieldsList(array_filter($this->fields, $filter));
    }

    /**
     * @return Field[]
     * @noinspection PhpDocSignatureInspection - Workaround for generics
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->fields);
    }

    /**
     * @return Field[] ['FIELD_NAME' => Field, ...]
     */
    public function asArray(): array
    {
        return $this->fields;
    }

    /**
     * @return string[] ['FIELD_NAME', ...]
     */
    public function names(): array
    {
        return array_keys($this->fields);
    }

    public function has(Field $field): bool
    {
        return array_key_exists($field->name, $this->fields);
    }

    public function empty(): bool
    {
        return 0 !== count($this->fields);
    }
}
