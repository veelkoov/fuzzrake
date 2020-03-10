<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Regexp\Regexp;

class StringFixer implements FixerInterface
{
    /**
     * @var string[]
     */
    private array $replacements;

    public function __construct(array $strings)
    {
        $this->replacements = $strings['replacements'];
    }

    public function fix(string $fieldName, string $subject): string
    {
        return trim(Regexp::replaceAll($this->replacements, $subject));
    }
}
