<?php

declare(strict_types=1);

namespace App\Utils\Creator\Changes;

use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\Fields\Fields;
use App\Data\Definitions\Fields\SecureValues;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Enforce;
use Veelkoov\Debris\Lists\StringList;

class Description
{
    /**
     * @var ChangeInterface[]
     */
    private array $changes = [];

    public function __construct(Creator $old, Creator $new)
    {
        foreach (Fields::persisted() as $field) {
            if (!SecureValues::hideInChangesDescription($field)) {
                $this->addChange(...$this->getField($field, $old, $new));
            }
        }
    }

    public function getText(): string
    {
        return $this->getList()->join("\n");
    }

    public function getList(): StringList
    {
        return StringList::mapFrom($this->changes, static fn (ChangeInterface $change) => $change->getDescription());
    }

    /**
     * @return array{0: Field, 1: psPhpFieldValue, 2: psPhpFieldValue}
     */
    private function getField(Field $field, Creator $old, Creator $new): array
    {
        return [$field, $old->get($field), $new->get($field)];
    }

    /**
     * @param psPhpFieldValue $old
     * @param psPhpFieldValue $new
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
