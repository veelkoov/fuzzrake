<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\DataDefinitions\Fields\Field;
use App\DataDefinitions\Fields\Fields;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\StrUtils;
use BackedEnum;
use DateTimeInterface;

class Diff
{
    /**
     * @var ChangeInterface[]
     */
    private array $changes = [];

    public function __construct(Artisan $old, Artisan $new)
    {
        foreach (Fields::persisted() as $field) {
            $this->addChange(...$this->getField($field, $old, $new));
        }
    }

    public function getDescriptionCliSafe(): string
    {
        $res = implode("\n", array_map(fn (ChangeInterface $change) => StrUtils::strSafeForCli($change->getDescription()), $this->changes));

        return '' === $res ? '' : $res."\n";
    }

    public function hasAnythingChanged(): bool
    {
        return !empty($this->changes);
    }

    private function getField(Field $field, Artisan $old, Artisan $new): array
    {
        return [$field, $old->get($field), $new->get($field)];
    }

    private function addChange(
        Field $field,
        BackedEnum|DateTimeInterface|string|bool|null $old,
        BackedEnum|DateTimeInterface|string|bool|null $new,
    ): void {
        if ($field->isList()) {
            $change = new ListChange($field, $old, $new);
        } else {
            $change = new SimpleChange($field, $old, $new);
        }

        if ($change->isActuallyAChange()) {
            $this->changes[] = $change;
        }
    }
}
