<?php

declare(strict_types=1);

namespace App\IuHandling\Changes;

use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\Fields\Fields;
use App\Data\Definitions\Fields\SecureValues;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Enforce;

use function Psl\Vec\map;

class Description
{
    /**
     * @var ChangeInterface[]
     */
    private array $changes = [];

    public function __construct(Artisan $old, Artisan $new)
    {
        foreach (Fields::persisted() as $field) {
            if (!SecureValues::hideInChangesDescription($field)) {
                $this->addChange(...$this->getField($field, $old, $new));
            }
        }
    }

    public function getText(): string
    {
        return implode("\n", map($this->changes, fn (ChangeInterface $change) => $change->getDescription()));
    }

    /**
     * @return ChangeInterface[]
     */
    public function getChanges(): array
    {
        return $this->changes;
    }

    /**
     * @return array{0: Field, 1: psFieldValue, 2: psFieldValue}
     */
    private function getField(Field $field, Artisan $old, Artisan $new): array
    {
        return [$field, $old->get($field), $new->get($field)];
    }

    /**
     * @param psFieldValue $old
     * @param psFieldValue $new
     */
    private function addChange(Field $field, mixed $old, mixed $new): void
    {
        if ($field->isList()) {
            $change = new ListChange($field, Enforce::string($old), Enforce::string($new));
        } else {
            $change = new SimpleChange($field, $old, $new);
        }

        if ($change->isActuallyAChange()) {
            $this->changes[] = $change;
        }
    }
}
