<?php

declare(strict_types=1);

namespace App\Submissions\Changes;

use App\DataDefinitions\Fields\Field;
use App\DataDefinitions\Fields\Fields;
use App\DataDefinitions\Fields\SecureValues;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Enforce;
use App\Utils\StrUtils;

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
            if (!SecureValues::hideConfirmDiff($field)) {
                $this->addChange(...$this->getField($field, $old, $new));
            }
        }
    }

    public function getText(): string
    {
        return implode("\n", map($this->changes, fn (ChangeInterface $change) => $change->getDescription()));
    }

    public function getDescriptionCliSafe(): string // TODO: Remove
    {
        $res = implode("\n", array_map(fn (ChangeInterface $change) => StrUtils::strSafeForCli($change->getDescription()), $this->changes));

        return '' === $res ? '' : $res."\n";
    }

    public function hasAnythingChanged(): bool // TODO: Remove
    {
        return !empty($this->changes);
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