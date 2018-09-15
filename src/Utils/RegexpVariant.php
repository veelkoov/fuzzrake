<?php

declare(strict_types=1);

namespace App\Utils;

class RegexpVariant
{
    /**
     * @var array[]
     */
    private $replacements = [];

    /**
     * RegexpVariant constructor.
     *
     * @param array $replacements
     */
    public function __construct(array $replacements)
    {
        $this->replacements = $replacements;
    }

    /**
     * @return array
     */
    public function getReplacements(): array
    {
        return $this->replacements;
    }
}
