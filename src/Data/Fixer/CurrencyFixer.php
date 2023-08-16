<?php

declare(strict_types=1);

namespace App\Data\Fixer;

class CurrencyFixer extends AbstractListFixer
{
    /**
     * @param psFixerConfig $currencies
     * @param psFixerConfig $strings
     */
    public function __construct(array $currencies, array $strings)
    {
        parent::__construct($currencies, $strings);
    }

    protected static function shouldSort(): bool
    {
        return false;
    }

    protected static function getSeparatorRegexp(): string
    {
        return '[\n,.]|[, ]and ';
    }
}
