<?php

declare(strict_types=1);

namespace App\IuHandling\Changes;

use App\Data\Definitions\Fields\Field;

readonly class ListChange implements ChangeInterface
{
    /**
     * @var list<string>
     */
    public array $added;

    /**
     * @var list<string>
     */
    public array $removed;

    /**
     * @param list<string> $old
     * @param list<string> $new
     */
    public function __construct(
        private Field $field,
        private array $old,
        private array $new,
    ) {
        [$this->added, $this->removed] = self::calculateAddedRemoved($this->old, $this->new);
    }

    public function getDescription(): string
    {
        $name = $this->field->value;
        $added = $this->added;
        $removed = $this->removed;

        if (!empty($added)) {
            $res = 'Added '.$name.': "'.implode('", "', $added).'"';
        } else {
            $res = '';
        }

        if (!empty($removed)) {
            $res .= '' === $res ? "Removed {$name}" : ' and removed';

            $res .= ': "'.implode('", "', $removed).'"';
        } elseif ('' === $res) {
            $res = "{$name} did not change";
        }

        return $res;
    }

    public function isActuallyAChange(): bool
    {
        return !empty($this->added) || !empty($this->removed);
    }

    /**
     * @param string[] $new
     * @param string[] $old
     *
     * @return array{string[], string[]}
     */
    private static function calculateAddedRemoved(array $old, array $new): array
    {
        $added = [];
        $removed = [];

        $common = array_intersect($new, $old);

        foreach ($old as $item) {
            if (!in_array($item, $common)) {
                $removed[] = $item;
            }
        }

        foreach ($new as $item) {
            if (!in_array($item, $common)) {
                $added[] = $item;
            }
        }

        return [$added, $removed];
    }

    public function getField(): Field
    {
        return $this->field;
    }
}
