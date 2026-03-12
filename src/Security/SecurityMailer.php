<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Service\EmailService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use SymfonyCasts\Bundle\VerifyEmail\Model\VerifyEmailSignatureComponents;

class SecurityMailer
{
    public function __construct(
        private readonly EmailService $emailService,
    ) {
    }

    public function notifyEmailChange(string $oldEmail, string $newEmail): void
    {
        $this->emailService->sendRaw(new TemplatedEmail()
            ->to($oldEmail)
            ->subject('Your email has been changed')
            ->textTemplate('emails/email_changed.txt.twig')
            ->context([
                'new_email' => $newEmail,
            ])
        );
    }

    public function sendConfirmationEmail(User $user, VerifyEmailSignatureComponents $signatureComponents): void
    {
        $email = new TemplatedEmail()
                ->to($user->getEmail())
                ->subject('Please confirm your email')
                ->textTemplate('emails/email_verification.txt.twig');

        $context = $email->getContext();
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

        $email->context($context);

        $this->emailService->sendRaw($email);
    }

    public function sendPasswordResetLink(User $user, ResetPasswordToken $resetToken): void
    {
        $email = new TemplatedEmail()
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->textTemplate('emails/password_reset.txt.twig')
            ->context([
                'reset_token' => $resetToken,
            ])
        ;

        $this->emailService->sendRaw($email);
    }
}
