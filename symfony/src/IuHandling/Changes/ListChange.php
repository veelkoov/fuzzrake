<?php

declare(strict_types=1);

namespace App\IuHandling\Changes;

use App\Data\Definitions\Fields\Field;
use App\Utils\Collections\StringList;
use Override;

readonly class ListChange implements ChangeInterface
{
    public StringList $added;
    public StringList $removed;

    public function __construct(
        private Field $field,
        private StringList $old,
        private StringList $new,
    ) {
        [$this->added, $this->removed] = self::calculateAddedRemoved($this->old, $this->new);
    }

    #[Override]
    public function getDescription(): string
    {
        $name = $this->field->value;

        if ($this->added->isNotEmpty()) {
            $res = 'Added '.$name.': "'.$this->added->join('", "').'"';
        } else {
            $res = '';
        }

        if ($this->removed->isNotEmpty()) {
            $res .= '' === $res ? "Removed {$name}" : ' and removed';

            $res .= ': "'.$this->removed->join('", "').'"';
        } elseif ('' === $res) {
            $res = "{$name} did not change";
        }

        return $res;
    }

    #[Override]
    public function isActuallyAChange(): bool
    {
        return $this->added->isNotEmpty() || $this->removed->isNotEmpty();
    }

    /**
     * @return array{StringList, StringList}
     */
    private static function calculateAddedRemoved(StringList $old, StringList $new): array
    {
        $added = new StringList();
        $removed = new StringList();

        $common = $new->intersect($old);

        foreach ($old as $item) {
            if (!$common->contains($item)) {
                $removed->add($item);
            }
        }

        foreach ($new as $item) {
            if (!$common->contains($item)) {
                $added->add($item);
            }
        }

        return [$added->freeze(), $removed->freeze()];
    }

    #[Override]
    public function getField(): Field
    {
        return $this->field;
    }
}
