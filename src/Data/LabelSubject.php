<?php

declare(strict_types=1);

namespace App\Data;

use App\Data\Definitions\Offers;
use App\Data\Definitions\Products;
use App\Data\Definitions\Styles;

enum LabelSubject: string
{
    case CREATOR = 'CREATOR';
    case OFFER = 'OFFER';
    case PRODUCT = 'PRODUCT';
    case STYLE = 'STYLE';

    /** @return list<string> */
    public function getValidValues(): array
    {
        return match ($this) {
            self::CREATOR => [''],
            self::OFFER => Offers::getValues(),
            self::PRODUCT => Products::getValues(),
            self::STYLE => Styles::getValues(),
        };
    }
}
