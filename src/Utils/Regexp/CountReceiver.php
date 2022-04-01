<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

use App\Utils\Traits\UtilityClass;
use TRegx\CleanRegex\Internal\Replace\Counting\PatternStructure;

final class CountReceiver
{
    use UtilityClass;

    /** @noinspection PhpUnusedParameterInspection */
    public static function once(): callable
    {
        return function (int $count, PatternStructure $pattern): void {
            if (0 === $count) {
                throw new ReplacingException('Pattern has not been replaced');
            }

            if ($count > 1) {
                throw new ReplacingException("Pattern has been replaced $count times instead of once");
            }
        };
    }
}
