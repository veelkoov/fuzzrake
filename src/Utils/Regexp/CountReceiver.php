<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

use App\Utils\Traits\UtilityClass;
use Throwable;
use TRegx\CleanRegex\Internal\Replace\Counting\PatternStructure;

final class CountReceiver
{
    use UtilityClass;

    /**
     * @param string|callable|Throwable $orElse Can be a callable `(int $count, PatternStructure $pattern)` which will handle the failure case (e.g. throw a customized exception, log some stuff) or an exception class name supposed to be instantiated with a predefined error messages, or an exception object ready to be thrown.
     *
     * @return callable To be passed as $countReceiver parameter in `->replace()->counting()`
     */
    public static function once(string|callable|Throwable $orElse): callable
    {
        return function (int $count, PatternStructure $pattern) use ($orElse): void {
            $fail = function (string $message) use ($orElse, $count, $pattern): void {
                if (is_callable($orElse)) {
                    $orElse($count, $pattern);
                } elseif ($orElse instanceof Throwable) {
                    throw $orElse;
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
