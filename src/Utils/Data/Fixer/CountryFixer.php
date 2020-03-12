<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Regexp\Regexp;

class CountryFixer implements FixerInterface
{
    /**
     * @var string[]
     */
    private array $replacements;

    public function __construct(array $countries)
    {
        $this->replacements = $countries['replacements'];
    }

    public function fix(string $fieldName, string $subject): string
    {
        $subject = trim($subject);
        $subject = Regexp::replaceAll($this->replacements, $subject, '#^', '$#i');

        return $subject;
    }
}
