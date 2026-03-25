<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Data\Definitions\ContactPermit;
use App\Entity\User;
use App\Utils\Creator\SmartAccessDecorator;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Traits\UtilityClass;

final class UserCreator
{
    use UtilityClass;

    public static function get(
        ?string $email = null,
        ?ContactPermit $contactPermit = null,
    ): Creator {
        $user = new User();

        if (null !== $email) {
            $user->setEmail($email);
        }

        if (null !== $contactPermit) {
            $user->setContactPermit($contactPermit);
        }

        $creator = new SmartAccessDecorator();
        $creator->entity->setUser($user);

        return $creator;
    }
}
