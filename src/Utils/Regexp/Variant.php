<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

class Variant
{
    /**
     * @var string[]
     */
    private array $replacements;

    /**
     * @param string[] $replacements
     */
    public function __construct(array $replacements)
    {
        $this->replacements = $replacements;
    }

    /**
     * @return string[]
     */
    public function getReplacements(): array
    {
        return $this->replacements;
    }
}
