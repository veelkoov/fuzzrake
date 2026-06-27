<?php

declare(strict_types=1);

namespace App\Utils\Collections;

use App\Utils\Creator\SmartAccessDecorator;
use Veelkoov\Debris\Maps\Base\DStringMap;

/**
 * @extends DStringMap<SmartAccessDecorator>
 */
class StringToCreator extends DStringMap
{
}
