<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\StrUtils;

class SpeciesListFixer extends AbstractListFixer
{
    private const KEEP_WHOLE = [ // TODO
        'All species, but I specialize in dragons',
    ];

    protected static function shouldSort(): bool
    {
        return false;
    }

    protected static function getSeparatorRegexp(): string
    {
        return "#\n#";
    }

    public function fix(string $fieldName, string $subject): string
    {
        $subject = parent::fix($fieldName, $subject);

        $subject = explode(',', $subject);

        foreach ($subject as &$specie) {
            $specie = StrUtils::ucfirst(trim($specie));
        }

        return implode("\n", array_filter($subject));
    }
}
