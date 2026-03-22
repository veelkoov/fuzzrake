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

    private const string TEST_PASSWORD = 'abcdef1234567890';
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
        self::$adminUser = new User()->setEmail('administrator@example.com')->setRoles(['ROLE_ADMIN'])
            ->setIsVerified(true);

        self::getContainerService(UserPasswordHasherInterface::class)->hashPassword(self::$adminUser, self::TEST_PASSWORD);

        self::persistAndFlush(self::$adminUser);
    }

    protected static function haveACreatorUser(): void
    {
        self::$creatorUser = new User()->setEmail('creator@example.com')->setIsVerified(true)
            ->setContactPermit(null);

        self::$creatorUser->setPassword(self::getContainerService(UserPasswordHasherInterface::class)->hashPassword(self::$creatorUser, self::TEST_PASSWORD));

        self::persistAndFlush(self::$creatorUser);
    }

    protected static function loginAdminUser(): void
    {
        self::$client->loginUser(self::$adminUser ?? throw new LogicException('Admin user has not been created yet.'));
    }

    protected static function loginCreatorUser(): void
    {
        self::$client->get('/login');
        self::$client->submitForm('Sign in', [
            '_username' => 'creator@example.com',
            '_password' => self::TEST_PASSWORD,
        ]);
    }
}
