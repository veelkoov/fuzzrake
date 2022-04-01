<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

use App\Utils\Traits\UtilityClass;
use TRegx\CleanRegex\Internal\Replace\Counting\PatternStructure;

final class CountReceiver
{
    use UtilityClass;

    /**
     * @param string|callable $orElse Can be a callable `(int $count, PatternStructure $pattern)` which will handle the failure case (e.g. throw a customized exception, log some stuff) or an exception class name supposed to be instantiated with a predefined error messages.
     *
     * @return callable To be passed as $countReceiver parameter in `->replace()->counting()`
     */
    public static function once(string|callable $orElse): callable
    {
        return function (int $count, PatternStructure $pattern) use ($orElse): void {
            $fail = function (string $message) use ($orElse, $count, $pattern): void {
                if (is_callable($orElse)) {
                    $orElse($count, $pattern);
                } else {
                    throw new $orElse($message);
                }
            };

            if (0 === $count) {
                $fail('Pattern has not been replaced');
            } elseif ($count > 1) {
                $fail("Pattern has been replaced $count times instead of once");
            }
        };
    }
}
