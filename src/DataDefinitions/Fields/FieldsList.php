<?php

declare(strict_types=1);

namespace App\DataDefinitions\Fields;

use ArrayIterator;
use Closure;
use Iterator;
use IteratorAggregate;

use function Psl\Vec\concat;
use function Psl\Vec\values;

/**
 * @implements IteratorAggregate<string, Field>
 */
class FieldsList implements IteratorAggregate
{
    /**
     * @var array<string, Field>
     */
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

    /**
     * @param Field[] $fields
     */
    public function plus(array $fields): self
    {
        return new FieldsList(concat(values($this->fields), $fields));
    }

    public function filtered(Closure $filter): self
    {
        return new FieldsList(array_filter($this->fields, $filter));
    }

    /**
     * @return Iterator<string, Field>
     */
    public function getIterator(): Iterator
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
        return 0 === count($this->fields);
    }
}
