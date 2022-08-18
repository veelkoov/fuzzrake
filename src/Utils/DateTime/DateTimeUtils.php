<?php

declare(strict_types=1);

namespace App\Utils\DateTime;

use App\Utils\Traits\UtilityClass;
use DateTimeImmutable;

final class DateTimeUtils
{
    use UtilityClass;

    public static function equal(?DateTimeImmutable $first, ?DateTimeImmutable $second): bool
    {
        if (null === $first) {
            if (null === $second) {
                return true;
            } else {
                return false;
            }
        } else {
            if (null === $second) {
                return false;
            } else {
                return $first->getTimezone()->getName() === $second->getTimezone()->getName()
                    && $first->getTimestamp() === $second->getTimestamp();
            }
        }
    }
}
