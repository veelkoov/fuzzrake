<?php

declare(strict_types=1);

namespace App\IuHandling\Changes;

use App\Data\Definitions\Fields\Field;
use App\Data\FieldValue;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\StrUtils;
use DateTimeImmutable;
use Override;

class SimpleChange implements ChangeInterface
{
    /**
     * @param psPhpFieldValue $old
     * @param psPhpFieldValue $new
     */
    public function __construct(
        private readonly Field $field,
        private readonly mixed $old,
        private readonly mixed $new,
    ) {
    }

    #[Override]
    public function getDescription(): string
    {
        $name = $this->field->value;

        $oldIsEmpty = !FieldValue::isProvided($this->field, $this->old);
        $newIsEmpty = !FieldValue::isProvided($this->field, $this->new);

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

    #[Override]
    public function isActuallyAChange(): bool
    {
        return $this->old instanceof DateTimeImmutable && $this->new instanceof DateTimeImmutable
            ? !DateTimeUtils::equal($this->old, $this->new)
            : $this->old !== $this->new;
    }

    /**
     * @param psPhpFieldValue $value
     */
    private function getOptionallyQuotedValue(mixed $value): string
    {
        if (null === $value) {
            return 'unknown';
        } else {
            return '"'.StrUtils::asStr($value).'"';
        }
    }

    #[Override]
    public function getField(): Field
    {
        return $this->field;
    }
}
