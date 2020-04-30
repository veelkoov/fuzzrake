<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Utils\Artisan\Field;

class SimpleChange implements ChangeInterface
{
    private Field $field;
    private string $old;
    private string $new;
    private ?string $imported;

    public function __construct(Field $field, string $old, string $new, ?string $imported)
    {
        $this->field = $field;
        $this->old = $old;
        $this->new = $new;
        $this->imported = $imported;
    }

    public function getDescription(): string
    {
        $name = $this->field->name();
        $old = $this->old;
        $new = $this->new;

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
