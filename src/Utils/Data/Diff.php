<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Entity\Artisan;
use App\Utils\Artisan\Field;
use App\Utils\Artisan\Fields;

class Diff
{
    /**
     * @var ChangeInterface[]
     */
    private array $changes = [];

    public function __construct(Artisan $old, Artisan $new, ?Artisan $imported)
    {
        foreach (Fields::persisted() as $field) {
            $this->addChange(...$this->getField($field, $old, $new, $imported));
        }
    }

    public function getDescription(): string
    {
        $res = implode("\n", array_map(fn (ChangeInterface $change) => $change->getDescription(), $this->changes));

        return '' === $res ? '' : $res."\n";
    }

    public function hasAnythingChanged(): bool
    {
        return !empty($this->changes);
    }

    private function getField(Field $field, Artisan $old, Artisan $new, ?Artisan $imported)
    {
        return [$field, $old->get($field), $new->get($field), $imported ? $imported->get($field) : null];
    }

    private function addChange(Field $field, string $old, string $new, ?string $imported): void
    {
        if ($field->isList()) {
            $change = new ListChange($field, $old, $new, $imported);
        } else {
            $change = new SimpleChange($field, $old, $new, $imported);
        }

        if ($change->isActuallyAChange()) {
            $this->changes[] = $change;
        }
    }
}
