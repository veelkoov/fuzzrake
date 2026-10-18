<?php

declare(strict_types=1);

namespace App\Data;

use App\Data\Definitions\Offers;
use App\Data\Definitions\Products;

enum LabelSubject: string
{
    case CREATOR = 'CREATOR';
    case PRODUCT = 'PRODUCT';
    case OFFER = 'OFFER';

    /** @return list<string> */
    public function getValidValues(): array
    {
        return match ($this) {
            self::CREATOR => [''],
            self::PRODUCT => Products::getValues(),
            self::OFFER => Offers::getValues(),
        };
    }
}
