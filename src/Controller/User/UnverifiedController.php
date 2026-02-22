<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\User;
use App\Security\EmailVerifier;
use App\ValueObject\Routing\RouteName;
use LogicException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/uuser')] // grep-code-route-uuser-prefix
class UnverifiedController extends AbstractController
{
    public function __construct(
        private readonly EmailVerifier $emailVerifier,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route(path: '/logout', name: RouteName::USER_LOGOUT)]
    public function logout(): void
    {
        $this->logger->emergency(__METHOD__.' has been called.');
        throw new LogicException('This should have been intercepted by the firewall.');
    }

    #[Route(path: '/main', name: RouteName::USER_MAIN)]
    public function main(): Response
    {
        return $this->render('user/main.html.twig');
    }

    #[Route(path: '/resendVerificationEmail', name: RouteName::USER_RESEND_VERIFICATION_EMAIL)]
    public function resendVerificationEmail(#[CurrentUser] User $user): Response
    {
        if ($user->isVerified()) {
            $this->addFlash('info', 'Your email address is already verified.');
        } else {
            try {
                $this->emailVerifier->sendEmailConfirmation($user);

                $this->addFlash('success', 'Verification email has been resent.');
            } catch (TransportExceptionInterface $exception) {
                $this->logger->error("Failed sending verification email for user ID={$user->getId()}.", ['exception' => $exception]);
                $this->addFlash('error', 'Failed to sent the notification. Please contact the site administration.');
            }
        }

        return $this->redirectToRoute(RouteName::USER_MAIN);
    }
}
