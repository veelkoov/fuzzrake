<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Data\Definitions\ContactPermit;
use App\Entity\Creator as CreatorE;
use App\Entity\User;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Traits\UtilityClass;
use Symfony\Component\Uid\Uuid;

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
        } else {
            $user->setEmail(Uuid::v7()->toRfc4122().'@example.com');
        }

        if (null !== $contactPermit) {
            $user->setContactPermit($contactPermit);
        }

        return new Creator(new CreatorE($user));
    }
}
