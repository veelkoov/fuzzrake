<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\Creator;
use App\Entity\User;
use App\Form\ChangeEmailFormType;
use App\Form\ChangePasswordFormType;
use App\Form\ContactPermitFormType;
use App\Security\EmailVerifier;
use App\Utils\Creator\SmartAccessDecorator;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
    public function main(Request $request, #[CurrentUser] User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContactPermitFormType::class, SmartAccessDecorator::wrap($user->getCreator() ?? new Creator())); // FIXME: Stupid workaround; move the stupid permit data garbage to the stupid user entity
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Your contact preferences have been saved.');
            $entityManager->flush();

            return $this->redirectToRoute(RouteName::USER_MAIN);
        }

        return $this->render('user/main.html.twig', [
            'contact_form' => $form,
        ]);
    }

    #[Route(path: '/resend-verification-email', name: RouteName::USER_RESEND_VERIFICATION_EMAIL)]
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

    #[Route(path: '/change-password', name: RouteName::USER_CHANGE_PASSWORD)]
    public function changePassword(Request $request, #[CurrentUser] User $user, UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            /** @var string $currentPassword */
            $currentPassword = $form->get(ChangePasswordFormType::FLD_CURRENT_PASSWORD)->getData();
            $isPasswordValid = $passwordHasher->isPasswordValid($user, $currentPassword);

            if ($form->isValid() && $isPasswordValid) {
                /** @var string $newPassword */
                $newPassword = $form->get(ChangePasswordFormType::FLD_NEW_PASSWORD)->getData();

                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                $entityManager->flush();

                $this->addFlash('success', 'Your password has been changed.');
                // TODO: Send email

                return $this->redirectToRoute(RouteName::USER_MAIN);
            }

            if (!$isPasswordValid) {
                $form->get(ChangePasswordFormType::FLD_CURRENT_PASSWORD)
                    ->addError(new FormError('Invalid password.'));
            }
        }

        return $this->render('user/change_password.html.twig', [
            'password_form' => $form,
        ]);
    }

    #[Route(path: '/change-email', name: RouteName::USER_CHANGE_EMAIL)]
    public function changeEmail(Request $request, #[CurrentUser] User $user, UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ChangeEmailFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            /** @var string $password */
            $password = $form->get(ChangeEmailFormType::FLD_PASSWORD)->getData();
            $isPasswordValid = $passwordHasher->isPasswordValid($user, $password);

            if ($form->isValid() && $isPasswordValid) {
                /** @var string $newEmail */
                $newEmail = $form->get(ChangeEmailFormType::FLD_NEW_EMAIL)->getData();

                $user->setEmail($newEmail)->setIsVerified(false);
                $entityManager->flush();
                $this->emailVerifier->sendEmailConfirmation($user);

                $this->addFlash('warning', 'Your email has been changed. A verification email has been sent.');

                return $this->redirectToRoute(RouteName::USER_MAIN);
            }

            if (!$isPasswordValid) {
                $form->get(ChangeEmailFormType::FLD_PASSWORD)
                    ->addError(new FormError('Invalid password.'));
            }
        }

        return $this->render('user/change_email.html.twig', [
            'email_form' => $form,
        ]);
    }
}
