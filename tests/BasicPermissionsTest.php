<?php

declare(strict_types=1);

namespace App\Tests;

use App\Data\Submission\Status;
use App\Security\Role;
use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Tests\TestUtils\Cases\Traits\MocksTrait;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Enforce;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class BasicPermissionsTest extends FuzzrakeWebTestCase
{
    use MocksTrait;

    private int $submissionId;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        self::haveACreatorUser();
        self::haveReviewerUsers();
        self::haveAnAdminUser();

        $submission = self::getEntityForSubmission(self::getCreatorUser(), new Creator(), false)
            ->setStatus(Status::IN_REVIEW);
        self::persistAndFlush($submission);
        $this->submissionId = Enforce::int($submission->getId());
    }

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
            ['/submission/SID/manage', false],
            ['/submission/SID/review', false],
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
            ['/submission/SID/manage', false],
            ['/submission/SID/review', false],
        ];
    }

    /**
     * @return list<array{string, bool|int}>
     */
    public static function unverifiedCreatorAccessDataProvider(): array
    {
        // ROLE_VERIFIED is processed in a single place in the User class, affecting all other roles.
        // Single test here (user can't perform their role's actions role unless verified) is enough.

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
            ['/submission/SID/manage', true],
            ['/submission/SID/review', true],
        ];
    }

    /**
     * @return list<array{string, bool|int}>
     */
    public static function lockedAdminAccessDataProvider(): array
    {
        // ROLE_LOCKED is processed in a single place in the User class, affecting all other roles.
        // Single test here (user can't perform their role's actions role if locked) is enough.

        return [
            ['/user/main', true],

            ['/mx/query/', false],
            ['/submissions/1/', false],
            ['/submission/SID/manage', false],
            ['/submission/SID/review', false],
        ];
    }

    /**
     * @return list<array{string, bool|int}>
     */
    public static function verifiedReviewerAccessDataProvider(): array
    {
        return [
            ['/user/main', true],
            ['/user/iu_form/start', false],

            ['/mx/query/', false],
            ['/submissions/1/', true],
            ['/submission/SID/manage', false],
            ['/submission/SID/review', true],
        ];
    }

    #[DataProvider('anonymousAccessDataProvider')]
    public function testAnonymousAccess(string $path, bool|int $allowedOrCode): void
    {
        self::$client->request('GET', $this->getResolvedPath($path));
        $this->verifyResponse($allowedOrCode, false);
    }

    #[DataProvider('verifiedCreatorAccessDataProvider')]
    public function testVerifiedCreatorAccess(string $path, bool|int $allowedOrCode): void
    {
        self::loginCreatorUser();

        self::$client->request('GET', $this->getResolvedPath($path));
        $this->verifyResponse($allowedOrCode, true);
    }

    #[DataProvider('unverifiedCreatorAccessDataProvider')]
    public function testUnverifiedCreatorAccess(string $path, bool|int $allowedOrCode): void
    {
        self::getCreatorUser()->removeRole(Role::VERIFIED);
        self::flush();
        self::loginCreatorUser();

        self::$client->request('GET', $this->getResolvedPath($path));
        $this->verifyResponse($allowedOrCode, true);
    }

    #[DataProvider('verifiedAdminAccessDataProvider')]
    public function testVerifiedAdminAccess(string $path, bool|int $allowedOrCode): void
    {
        self::loginAdminUser();

        self::$client->request('GET', $this->getResolvedPath($path));
        $this->verifyResponse($allowedOrCode, true);
    }

    #[DataProvider('lockedAdminAccessDataProvider')]
    public function testLockedAdminAccess(string $path, bool|int $allowedOrCode): void
    {
        self::getAdminUser()->addRole(Role::LOCKED);
        self::flush();
        self::loginAdminUser();

        self::$client->request('GET', $this->getResolvedPath($path));
        $this->verifyResponse($allowedOrCode, true);
    }

    #[DataProvider('verifiedReviewerAccessDataProvider')]
    public function testVerifiedReviewerAccess(string $path, bool|int $allowedOrCode): void
    {
        self::loginReviewerUser();

        self::$client->request('GET', $this->getResolvedPath($path));
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

    private function getResolvedPath(string $path): string
    {
        return str_replace('/SID/', "/$this->submissionId/", $path);
    }
}
