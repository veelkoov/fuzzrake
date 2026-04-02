<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Entity\User;
use App\Security\Role;
use App\Utils\Collections\Arrays;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Traits\UtilityClass;
use Symfony\Component\Uid\Uuid;

final class UserCreator
{
    use UtilityClass;

    private static int $randomId = 0;

    public static function get(
        bool $randomMinimalData = false,
        ?string $email = null,
        ?ContactPermit $contactPermit = null,
    ): Creator {
        $user = new User()->addRole(Role::VERIFIED)->addRole(Role::CREATOR);

        if (null !== $email) {
            $user->setEmail($email);
        } else {
            $user->setEmail(Uuid::v7()->toRfc4122().'@example.com');
        }

        $contactPermit ??= $randomMinimalData ? Arrays::rndValue(ContactPermit::cases()) : null;
        $user->setContactPermit($contactPermit); // Default to null, since that's our legacy data

        $result = new Creator(user: $user);

        if ($randomMinimalData) {
            ++self::$randomId;

            $randomPart = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 3);
            $creatorId = sprintf("$randomPart%04d", self::$randomId);

            $result
                ->setCreatorId($creatorId) // May require improvements if tests will fail often
                ->setName(sprintf("Testing creator $randomPart (%04d)", self::$randomId))
                ->setCountry('FI')
            ;

            switch (rand(1, 3)) {
                case 1:
                    $result->setAges(Ages::ADULTS)->setDoesNsfw(true)->setWorksWithMinors(false)
                        ->setNsfwWebsite(self::rndBool())->setNsfwSocial(self::rndBool());
                    break;

                case 2:
                    $result->setAges(Arrays::rndValue(Ages::cases()))->setDoesNsfw(false)
                        ->setWorksWithMinors(true)->setNsfwWebsite(false)->setNsfwSocial(false);
                    break;

                case 3:
                    $result->setAges(Arrays::rndValue(Ages::cases()))->setDoesNsfw(false)
                        ->setWorksWithMinors(false)->setNsfwWebsite(self::rndBool())->setNsfwSocial(self::rndBool());
                    break;
            }
        }

        return $result;
    }

    private static function rndBool(): bool
    {
        return 0 === rand(0, 1);
    }
}
