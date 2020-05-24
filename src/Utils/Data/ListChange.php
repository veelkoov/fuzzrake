<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Utils\Artisan\Field;
use App\Utils\StringList;

class ListChange implements ChangeInterface
{
    private Field $field;
    private array $old;
    private array $new;
    private array $added;
    private array $removed;
    private ?string $imported;

    public function __construct(Field $field, string $old, string $new, ?string $imported)
    {
        $this->field = $field;
        $this->old = StringList::unpack($old);
        $this->new = StringList::unpack($new);
        $this->imported = $imported;

        $this->setCalculatedAddedRemoved();
    }

    public function getDescription(): string
    {
        $name = $this->field->name();
        $added = $this->added;
        $removed = $this->removed;

        if (!empty($added)) {
            $res = 'Added '.$name.': "'.implode('", "', $added).'"';
        } else {
            $res = '';
        }

        if (!empty($removed)) {
            $res .= '' === $res ? 'Removed '.$name : ' and removed';

            $res .= ': "'.implode('", "', $removed).'"';
        } elseif ('' === $res) {
            $res = $name.': no changes';
        }

        return $res;
    }

    public function isActuallyAChange(): bool
    {
        return !empty($this->added) || !empty($this->removed);
    }

    private function setCalculatedAddedRemoved(): void
    {
        $this->added = [];
        $this->removed = [];
        $common = array_intersect($this->new, $this->old);

        foreach ($this->old as $item) {
            if (!in_array($item, $common)) {
                $this->removed[] = $item;
            }
        }

        foreach ($this->new as $item) {
            if (!in_array($item, $common)) {
                $this->added[] = $item;
            }
        }
    }
}
