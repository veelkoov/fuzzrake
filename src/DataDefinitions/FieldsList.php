<?php

declare(strict_types=1);

namespace App\DataDefinitions;

use ArrayIterator;
use Closure;
use IteratorAggregate;
use Traversable;

class FieldsList implements IteratorAggregate
{
    public function __construct(
        private array $fields
    ) {
    }

    public function filtered(Closure $filter): FieldsList
    {
        return new FieldsList(array_filter($this->fields, $filter));
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->fields);
    }

    /**
     * @return Field[] 'FIELD_NAME' => Field
     */
    public function asArray(): array
    {
        return $this->fields;
    }
}
