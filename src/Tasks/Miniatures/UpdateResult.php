<?php

declare(strict_types=1);

namespace App\Tasks\Miniatures;

enum UpdateResult
{
    case CLEARED;
    case NO_CHANGE;
    case RETRIEVED;
}
