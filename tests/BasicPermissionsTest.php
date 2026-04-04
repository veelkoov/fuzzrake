<?php

declare(strict_types=1);

namespace App\Tests;

use App\Security\Role;
use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class BasicPermissionsTest extends FuzzrakeWebTestCase // TODO: Reviews
{
    /**
     * @return list<array{string, bool|int}>
     */
    public static function anonymousAccessDataProvider(): array
    {
        return [
            ['/', true],
            ['/feedback', true],
            ['/no-such-path', 404], // Sanity check

            ['/login', true],
            ['/verify-email', 302],

            // All /reset-password could be removed when a proper test gets created
            ['/reset-password', true],
            ['/reset-password/check-email', true],
            ['/reset-password/reset/aaaa', 302],
            ['/reset-password/reset', 404],

            ['/user/main', false],
            ['/user/iu_form/start', false],

            ['/mx/query/', false],
            ['/submissions/1/', false],
        ];
    }

    /**
     * @return list<array{string, bool|int}>
     */
    public static function verifiedCreatorAccessDataProvider(): array
    {
        return [
            ['/', true], // Sanity check

            ['/login', 302],
            ['/verify-email', 302],

            // All /reset-password could be removed when a proper test gets created
            ['/reset-password', 302],
            ['/reset-password/reset', 302],

            ['/user/main', true],
            ['/user/iu_form/start', true],

            ['/mx/query/', false],
            ['/submissions/1/', false],
        ];
    }

    /**
     * @return list<array{string, bool|int}>
     */
    public static function unverifiedCreatorAccessDataProvider(): array
    {
        return [
            ['/user/main', true],
            ['/user/iu_form/start', false],
        ];
    }

    /**
     * @return list<array{string, bool|int}>
     */
    public static function verifiedAdminAccessDataProvider(): array
    {
        return [
            ['/user/main', true],
            ['/user/iu_form/start', false],

            ['/mx/query/', true],
            ['/submissions/1/', true],
        ];
    }

    /**
     * @return list<array{string, bool|int}>
     */
    public static function unverifiedAdminAccessDataProvider(): array
    {
        return [
            ['/user/main', true],

            ['/mx/query/', false],
            ['/submissions/1/', false],
        ];
    }

    #[DataProvider('anonymousAccessDataProvider')]
    public function testAnonymousAccess(string $path, bool|int $allowedOrCode): void
    {
        self::$client->request('GET', $path);
        $this->verifyResponse($allowedOrCode, false);
    }

    #[DataProvider('verifiedCreatorAccessDataProvider')]
    public function testVerifiedCreatorAccess(string $path, bool|int $allowedOrCode): void
    {
        self::haveACreatorUser();
        self::loginCreatorUser();

        self::$client->request('GET', $path);
        $this->verifyResponse($allowedOrCode, true);
    }

    #[DataProvider('unverifiedCreatorAccessDataProvider')]
    public function testUnverifiedCreatorAccess(string $path, bool|int $allowedOrCode): void
    {
        self::haveACreatorUser();
        self::getCreatorUser()->removeRole(Role::VERIFIED);
        self::flush();
        self::loginCreatorUser();

        self::$client->request('GET', $path);
        $this->verifyResponse($allowedOrCode, true);
    }

    #[DataProvider('verifiedAdminAccessDataProvider')]
    public function testVerifiedAdminAccess(string $path, bool|int $allowedOrCode): void
    {
        self::haveAnAdminUser();
        self::loginAdminUser();

        self::$client->request('GET', $path);
        $this->verifyResponse($allowedOrCode, true);
    }

    #[DataProvider('unverifiedAdminAccessDataProvider')]
    public function testUnverifiedAdminAccess(string $path, bool|int $allowedOrCode): void
    {
        self::haveAnAdminUser();
        self::getAdminUser()->removeRole(Role::VERIFIED);
        self::flush();
        self::loginAdminUser();

        self::$client->request('GET', $path);
        $this->verifyResponse($allowedOrCode, true);
    }

    private function verifyResponse(bool|int $allowedOrCode, bool $loggedIn): void
    {
        if (is_int($allowedOrCode)) {
            self::assertResponseStatusCodeIs($allowedOrCode);
        } elseif ($allowedOrCode) {
            self::assertResponseStatusCodeIs(200);
        } elseif ($loggedIn) {
            self::assertResponseStatusCodeIs(403);
        } else {
            self::assertResponseRedirects('/login');
        }
    }
}
