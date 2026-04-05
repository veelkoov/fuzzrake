<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use App\Entity\User;
use App\Security\Role;
use LogicException;
use PHPUnit\Framework\Attributes\Before;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

trait UsersTrait
{
    use ContainerTrait;

    private const string DEFAULT_TEST_PASSWORD = 'abcdef1234567890';
    private static ?User $adminUser;
    private static ?User $creatorUser;
    private static ?User $reviewerUser;

    #[Before]
    protected function resetUsers(): void
    {
        self::$adminUser = null;
        self::$creatorUser = null;
        self::$reviewerUser = null;
    }

    protected static function haveAnAdminUser(): void
    {
        $user = new User()
            ->setEmail('administrator@example.com')
            ->addRole(Role::ADMIN)
            ->addRole(Role::VERIFIED);
        self::setDefaultPassword($user);
        self::persistAndFlush($user);

        self::$adminUser = $user;
    }

    protected static function getAdminUser(): User
    {
        return self::$adminUser ?? throw new LogicException('Admin user has not been created yet.');
    }

    protected static function haveACreatorUser(): void
    {
        $user = new User()
            ->setEmail('creator@example.com')
            ->addRole(Role::CREATOR)
            ->addRole(Role::VERIFIED)
            ->setContactPermit(null);
        self::setDefaultPassword($user);
        self::persistAndFlush($user);

        self::$creatorUser = $user;
    }

    protected static function getCreatorUser(): User
    {
        return self::$creatorUser ?? throw new LogicException('Creator user has not been created yet.');
    }

    protected static function haveAReviewerUser(): void
    {
        $user = new User()
            ->setEmail('reviewer@example.com')
            ->addRole(Role::REVIEWER)
            ->addRole(Role::VERIFIED)
            ->setContactPermit(null);
        self::setDefaultPassword($user);
        self::persistAndFlush($user);

        self::$reviewerUser = $user;
    }

    protected static function getReviewerUser(): User
    {
        return self::$reviewerUser ?? throw new LogicException('Reviewer user has not been created yet.');
    }

    protected static function loginAdminUser(): void
    {
        self::loginUser(self::$adminUser ?? throw new LogicException('Admin user has not been created yet.'));
    }

    protected static function loginCreatorUser(): void
    {
        self::loginUser(self::getCreatorUser());
    }

    protected static function loginReviewerUser(): void
    {
        self::loginUser(self::getReviewerUser());
    }

    protected static function loginUser(User $user): void
    {
        // There may be better ways to do this now, or in the future. For now, this just works.

        // @phpstan-ignore function.impossibleType,function.alreadyNarrowedType (Allow both Panther and KernelBrowser)
        if (method_exists(self::$client, 'loginUser')) {
            self::$client->loginUser($user);
        } else {
            // @phpstan-ignore method.notFound (Panther only - method_exists above)
            self::$client->get('/index.php/login');

            // If we are already logged in, logout (redirects to the login form)
            if (1 !== self::$client->getCrawler()->filterXPath('//input[@type="email" and @id="username"]')->count()) {
                // @phpstan-ignore method.notFound (Panther only - method_exists above)
                self::$client->get('/index.php/user/logout');
            }

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
