<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\ValueObject\Routing\RouteName;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    public function __construct(
        private readonly VerifyEmailHelperInterface $verifyEmailHelper,
        private readonly SecurityMailer $mailer,
        #[Autowire(service: 'monolog.logger.security')]
        private readonly LoggerInterface $logger,
    ) {
    }

    public function sendEmailConfirmation(User $user): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            RouteName::USER_VERIFY_EMAIL,
            (string) $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()]
        );

        $this->mailer->sendConfirmationEmail($user, $signatureComponents);
        $this->logger->info('Sent email confirmation message.', ['user ID' => $user->getId()]);
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, User $user): void
    {
        $this->verifyEmailHelper->validateEmailConfirmationFromRequest($request, (string) $user->getId(), $user->getEmail());

        $user->setIsVerified(true);
    }
}
