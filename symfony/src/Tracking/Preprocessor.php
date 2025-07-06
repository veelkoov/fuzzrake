<?php

declare(strict_types=1);

namespace App\Tracking;

use Veelkoov\Debris\StringList;

class Preprocessor
{
    public function __construct(
        private readonly Patterns $patterns,
    ) {
    }

    public function preprocess(Content $input): Content
    {
        // TODO: URL strategy
        $newContent = strtolower($input->content);
        $newContent = $this->patterns->cleaners->do($newContent);
        $newContent = $this->replaceCreatorAliases($newContent, $input->aliases);
        $newContent = $this->patterns->falsePositives->do($newContent);

        return $input->with($newContent);
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
