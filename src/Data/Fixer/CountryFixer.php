<?php

declare(strict_types=1);

namespace App\Data\Fixer;

class CountryFixer extends AbstractStringFixer
{
    /**
     * @param psFixerConfig $countries
     */
    public function __construct(array $countries)
    {
        parent::__construct($countries);
    }
}
