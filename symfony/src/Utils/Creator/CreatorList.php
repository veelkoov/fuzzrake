<?php

declare(strict_types=1);

namespace App\Utils\Creator;

use App\Entity\Creator as CreatorE;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Veelkoov\Debris\Base\DList;

/**
 * @extends DList<Creator>
 */
class CreatorList extends DList
{
    /**
     * @param array<CreatorE> $entities
     */
    public static function wrap(array $entities): self
    {
        return self::mapFrom($entities, Creator::wrap(...));
    }
}
