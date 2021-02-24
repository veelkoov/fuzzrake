<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

class Variant
{
    public function __construct(
        /*
         * @var string[] FIXME: Type hint doesn't work
         */
        private array $replacements,
    ) {
    }

    /**
     * @return string[]
     */
    public function getReplacements(): array
    {
        return $this->replacements;
    }
}
