<?php

declare(strict_types=1);

namespace App\Utils\Collections;

use App\Data\Submission\Status;
use Veelkoov\Debris\Base\DMap;
use Veelkoov\Debris\Base\Enforce\IntValuesTrait;

/**
 * @extends DMap<Status, int>
 */
class StatusToInt extends DMap
{
    use IntValuesTrait;
}
