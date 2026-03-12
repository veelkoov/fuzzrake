<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\SecurityMailer;
use App\Utils\DateTime\UtcClock;
use Override;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use SymfonyCasts\Bundle\VerifyEmail\Model\VerifyEmailSignatureComponents;

class SecurityMailerTest extends KernelTestCase
{
    private static SecurityMailer $subject;
    private static User $user;

    #[Override]
    public static function setUpBeforeClass(): void
    {
        self::bootKernel();

        $subject = self::getContainer()->get(SecurityMailer::class);
        self::assertInstanceOf(SecurityMailer::class, $subject);
        self::$subject = $subject;

        self::$user = new User()->setEmail('email@example.com');
    }

    public function testNotifyEmailChange(): void
    {
        self::$subject->notifyEmailChange('old-email@example.com', 'new-email@example.com');
    }

    public function testSendPasswordResetLink(): void
    {
        $signatureComponents = new VerifyEmailSignatureComponents(UtcClock::now(), 'https://example.com', UtcClock::time());
        self::$subject->sendConfirmationEmail(self::$user, $signatureComponents);
    }

    public function testSendConfirmationEmail(): void
    {
        $resetToken = new ResetPasswordToken('resetPasswordToken', UtcClock::now(), UtcClock::time());
        self::$subject->sendPasswordResetLink(self::$user, $resetToken);
    }
}
