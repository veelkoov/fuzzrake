<?php

declare(strict_types=1);

namespace App\IuHandling\Changes;

use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\Fields\Fields;
use App\Data\Definitions\Fields\SecureValues;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Collections\StringList;
use App\Utils\Enforce;

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
        return StringList::mapFrom($this->changes, fn (ChangeInterface $change) => $change->getDescription())->join("\n");
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
            $change = new ListChange($field, new StringList(Enforce::strList($old)), new StringList(Enforce::strList($new)));
        } else {
            $change = new SimpleChange($field, $old, $new);
        }

        if ($change->isActuallyAChange()) {
            $this->changes[] = $change;
        }
    }
}
