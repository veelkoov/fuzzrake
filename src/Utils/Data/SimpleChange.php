<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\DataDefinitions\Fields\Field;
use App\Utils\StrUtils;

class SimpleChange implements ChangeInterface
{
    /**
     * @param psFieldValue $old
     * @param psFieldValue $new
     */
    public function __construct(
        private readonly Field $field,
        private readonly mixed $old,
        private readonly mixed $new,
    ) {
    }

    public function getDescription(): string
    {
        $name = $this->field->name;
        $old = StrUtils::asStr($this->old);
        $new = StrUtils::asStr($this->new);

        if ($old === $new) {
            return $name.': '.'no changes';
        } else {
            if ('' !== $new) {
                if ('' !== $old) {
                    return 'Changed '.$name.' from "'.$old.'" to "'.$new.'"';
                } else {
                    return 'Added '.$name.': "'.$new.'"';
                }
            } else {
                return 'Removed '.$name.': "'.$old.'"';
            }
        }
    }

    public function isActuallyAChange(): bool
    {
        return $this->old !== $this->new;
    }
}
