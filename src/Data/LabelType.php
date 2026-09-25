<?php

declare(strict_types=1);

namespace App\Data;

enum LabelType: string
{
    case PRODUCT_VERIFIED = 'PRODUCT_VERIFIED';

    case CREATOR_ADDED_BEFORE_2026 = 'CREATOR_ADDED_BEFORE_2026';
    case CREATOR_GOT_3_REVIEWS = 'CREATOR_GOT_3_REVIEWS';
}
