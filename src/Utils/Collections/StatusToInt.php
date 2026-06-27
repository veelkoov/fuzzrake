<?php

declare(strict_types=1);

namespace App\Utils\Collections;

use App\Data\Submission\Status;
use Veelkoov\Debris\Enforce\IntValuesTrait;
use Veelkoov\Debris\Maps\Base\DMap;

/**
 * @extends DMap<Status, int>
 */
class StatusToInt extends DMap
{
    use IntValuesTrait;
}
