<?php

declare(strict_types=1);

namespace App\Data\Definitions\Fields;

use ArrayIterator;
use Closure;
use Iterator;
use IteratorAggregate;
use Override;

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
            $this->fields[$field->value] = $field;
        }
    }

    /**
     * @param list<Field> $fields
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
    #[Override]
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->fields);
    }

    /**
     * @return array<string, Field> ['FIELD_NAME' => Field, ...]
     */
    public function asArray(): array
    {
        return $this->fields;
    }

    /**
     * @return list<string> ['FIELD_NAME', ...]
     */
    public function names(): array
    {
        return array_keys($this->fields);
    }

    public function has(Field $field): bool
    {
        return array_key_exists($field->value, $this->fields);
    }

    public function empty(): bool
    {
        return 0 === count($this->fields);
    }
}
