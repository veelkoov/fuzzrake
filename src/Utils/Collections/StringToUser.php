<?php

declare(strict_types=1);

namespace App\Utils\Collections;

use App\Entity\User;
use Override;
use Veelkoov\Debris\Base\DStringMap;

/**
 * @extends DStringMap<User>
 */
class StringToUser extends DStringMap
{
    #[Override]
    protected static function enforceValueType(mixed $value): User
    {
        return $value;
    }
}
