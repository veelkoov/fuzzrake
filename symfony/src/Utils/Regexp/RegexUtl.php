<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

use App\Utils\Traits\UtilityClass;
use Veelkoov\Debris\Maps\StringToString;

final class RegexUtl
{
    use UtilityClass;

    /**
     * @param array<array-key, ?string> $matches
     */
    public static function namedGroups(array $matches): StringToString
    {
        $result = new StringToString();

        foreach ($matches as $groupName => $match) {
            if (null !== $match && !is_numeric($groupName)) {
                $result->set($groupName, $match);
            }
        }

        return $result;
    }
}
