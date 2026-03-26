<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use App\Entity\User;
use LogicException;
use PHPUnit\Framework\Attributes\Before;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

trait UsersTrait
{
    use ContainerTrait;

    private const string DEFAULT_TEST_PASSWORD = 'abcdef1234567890';
    private static ?User $adminUser;
    private static ?User $creatorUser;

    #[Before]
    protected function resetUsers(): void
    {
        self::$adminUser = null;
        self::$creatorUser = null;
    }

    protected static function haveAnAdminUser(): void
    {
        $user = new User()
            ->setEmail('administrator@example.com')
            ->setRoles(['ROLE_ADMIN'])
            ->setIsVerified(true);
        self::setDefaultPassword($user);
        self::persistAndFlush($user);

        self::$adminUser = $user;
    }

    protected static function haveACreatorUser(): void
    {
        $user = new User()
            ->setEmail('creator@example.com')
            ->setIsVerified(true)
            ->setContactPermit(null);
        self::setDefaultPassword($user);
        self::persistAndFlush($user);

        self::$creatorUser = $user;
    }

    protected static function getCreatorUser(): User
    {
        return self::$creatorUser ?? throw new LogicException('Creator user has not been created yet.');
    }

    protected static function loginAdminUser(): void
    {
        self::loginUser(self::$adminUser ?? throw new LogicException('Admin user has not been created yet.'));
    }

    protected static function loginCreatorUser(): void
    {
        self::loginUser(self::getCreatorUser());
    }

    protected static function loginUser(User $user): void
    {
        // TODO: Possibly find a better method to do this
        // @phpstan-ignore function.impossibleType,function.alreadyNarrowedType
        if (method_exists(self::$client, 'loginUser')) {
            self::$client->loginUser($user);
        } else {
            self::$client->get('/uuser/logout'); // @phpstan-ignore method.notFound
            self::$client->restart(); // FIXME: Why is this necessary?
            self::$client->get('/login'); // @phpstan-ignore method.notFound
            self::$client->submitForm('Sign in', [
                '_username' => $user->getEmail(),
                '_password' => self::DEFAULT_TEST_PASSWORD,
            ]);
        }
    }

    protected static function setDefaultPassword(User $user): void
    {
        $user->setPassword(self::getContainerService(UserPasswordHasherInterface::class)
            ->hashPassword($user, self::DEFAULT_TEST_PASSWORD));
    }
}
