<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

class Variant
{
    /**
     * @var array[]
     */
    private $replacements = [];

    /**
     * @param array[] $replacements
     */
    public function __construct(array $replacements)
    {
        $this->replacements = $replacements;
    }

    public function getReplacements(): array
    {
        return $this->replacements;
    }
}
