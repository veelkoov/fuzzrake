<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Tracking\Patterns\Patterns;
use Veelkoov\Debris\StringList;

class Preprocessor
{
    private const int MAX_ANALYSED_SIZE_CHARACTERS = 1 * 1024 * 1024; // ~= 1 MiB (+multibyte characters)

    public function __construct(
        private readonly Patterns $patterns,
    ) {
    }

    public function preprocess(string $input, StringList $aliases): string
    {
        $result = mb_substr($input, 0, self::MAX_ANALYSED_SIZE_CHARACTERS);
        // TODO: URL strategy
        $result = strtolower($result);
        $result = $this->patterns->cleaners->do($result);
        $result = $this->replaceCreatorAliases($result, $aliases);
        $result = $this->patterns->falsePositives->do($result);

        return $result;
    }

    private function replaceCreatorAliases(string $input, StringList $aliases): string
    {
        $result = $input;

        foreach ($aliases as $alias) {
            $alias = strtolower($alias);

            $result = str_replace($alias, 'CREATOR_NAME', $result);

            if (mb_strlen($alias) > 2 && str_ends_with($alias, 's')) {
                $result = str_replace(substr($alias, 0, -1)."'s", 'CREATOR_NAME', $result);
            }
        }

        return $result;
    }
}
