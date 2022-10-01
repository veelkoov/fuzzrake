<?php

declare(strict_types=1);

namespace App\IuHandling\Changes;

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

        $oldIsEmpty = null === $this->old || '' === $this->old;
        $newIsEmpty = null === $this->new || '' === $this->new;

        $old = $this->getOptionallyQuotedValue($this->old);
        $new = $this->getOptionallyQuotedValue($this->new);

        if ($old === $new) {
            return "{$name} did not change";
        }

        if ($oldIsEmpty) {
            if ($newIsEmpty) {
                return "Changed {$name} from {$old} to {$new}";
            } else {
                return "Added {$name}: {$new}";
            }
        } else {
            if ($newIsEmpty) {
                return "Removed {$name}: {$old}";
            } else {
                return "Changed {$name} from {$old} to {$new}";
            }
        }
    }

    public function isActuallyAChange(): bool
    {
        return $this->old !== $this->new;
    }

    /**
     * @param psFieldValue $value
     */
    private function getOptionallyQuotedValue(mixed $value): string
    {
        if (null === $value) {
            return 'unknown';
        } else {
            return '"'.StrUtils::asStr($value).'"';
        }
    }

    public function getField(): Field
    {
        return $this->field;
    }
}
