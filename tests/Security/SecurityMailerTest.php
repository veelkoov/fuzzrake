<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\SecurityMailer;
use App\Tests\TestUtils\Cases\Traits\MessageBusTrait;
use App\Utils\DateTime\UtcClock;
use App\Utils\Enforce;
use Override;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use SymfonyCasts\Bundle\VerifyEmail\Model\VerifyEmailSignatureComponents;

class SecurityMailerTest extends KernelTestCase
{
    use MessageBusTrait;

    private static SecurityMailer $subject;
    private static User $user;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        self::$subject = Enforce::objectOf(self::getContainer()->get(SecurityMailer::class), SecurityMailer::class);
        self::$user = new User()->setEmail('email@example.com');
    }

    public function testNotifyEmailChange(): void
    {
        self::$subject->notifyEmailChange('old-email@example.com', 'new-email@example.com');

        self::assertQueuedEmailCount(1);
        $email = self::getMailerMessage(0);
        // @phpstan-ignore argument.type (count checked above)
        self::assertEmailAddressContains($email, 'To', 'old-email@example.com');
    }

    public function testSendPasswordResetLink(): void
    {
        $signatureComponents = new VerifyEmailSignatureComponents(UtcClock::now(), 'https://example.com', UtcClock::time());
        self::$subject->sendConfirmationEmail(self::$user, $signatureComponents);

        self::assertQueuedEmailCount(1);
        $email = self::getMailerMessage(0);
        // @phpstan-ignore argument.type (count checked above)
        self::assertEmailAddressContains($email, 'To', 'email@example.com');
    }

    public function testSendConfirmationEmail(): void
    {
        $resetToken = new ResetPasswordToken('resetPasswordToken', UtcClock::now(), UtcClock::time());
        self::$subject->sendPasswordResetLink(self::$user, $resetToken);

        self::assertQueuedEmailCount(1);
        $email = self::getMailerMessage(0);
        // @phpstan-ignore argument.type (count checked above)
        self::assertEmailAddressContains($email, 'To', 'email@example.com');
    }
}
