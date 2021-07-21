<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Utils\Artisan\Field;
use App\Utils\StrUtils;

class SimpleChange implements ChangeInterface
{
    public function __construct(
        private Field $field,
        private string | bool $old,
        private string | bool $new,
    ) {
    }

    public function getDescription(): string
    {
        $name = $this->field->name();
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
