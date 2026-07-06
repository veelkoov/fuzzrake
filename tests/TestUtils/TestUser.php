<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Data\Definitions\ContactPermit;
use App\Entity\User;
use App\Security\Role;
use App\Utils\Collections\Arrays;
use App\Utils\Traits\UtilityClass;
use Symfony\Component\Uid\Uuid;

final class TestUser
{
    use UtilityClass;

    /**
     * @param List<Role> $roles
     */
    public static function get(
        bool $randomMinimalData = false,
        ?string $email = null,
        ?ContactPermit $contactPermit = null,
        array $roles = [],
    ): User {
        $user = new User()->addRole(Role::VERIFIED);
        foreach ($roles as $role) {
            $user->addRole($role);
        }

        if (null !== $email) {
            $user->setEmail($email);
        } else {
            $user->setEmail(Uuid::v7()->toRfc4122().'@example.com');
        }

        $contactPermit ??= $randomMinimalData ? Arrays::rndValue(ContactPermit::cases()) : null;
        $user->setContactPermit($contactPermit); // Default to null, since that's our legacy data

        return $user;
    }
}
