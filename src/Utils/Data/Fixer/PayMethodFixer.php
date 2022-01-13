<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

class PayMethodFixer extends AbstractListFixer
{
    public function __construct(array $paymentMethods, array $strings)
    {
        parent::__construct($paymentMethods, $strings);
    }

    protected static function shouldSort(): bool
    {
        return false;
    }

    protected static function getSeparatorRegexp(): string
    {
        return '[\n,.]';
    }

    protected function getNonsplittable(): array
    {
        return [
            'wise.com',
        ];
    }
}
