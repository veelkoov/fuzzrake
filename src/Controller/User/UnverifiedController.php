<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\Creator;
use App\Entity\User;
use App\Form\ChangeEmailFormType;
use App\Form\ChangePasswordFormType;
use App\Form\ContactPermitFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Security\SecurityMailer;
use App\Utils\Creator\SmartAccessDecorator;
use App\Utils\Email;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Cache(public: false, noStore: true)]
#[Route(path: '/uuser')] // grep-code-route-uuser-prefix
class UnverifiedController extends AbstractController
{
    public function __construct(
        #[Autowire(service: 'monolog.logger.fuzzrake.security')]
        private readonly LoggerInterface $logger,
        private readonly EmailVerifier $emailVerifier,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $entityManager,
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
            $this->addFlash('info', 'Your email address is already confirmed.');
        } else {
            $this->emailVerifier->sendEmailConfirmation($user);
            $this->addFlash('success', 'Confirmation email has been sent.'
                .' Please check your inbox and SPAM folders in a few minutes.');
        }

        return $this->redirectToRoute(RouteName::USER_MAIN);
    }

    #[Route(path: '/change-password', name: RouteName::USER_CHANGE_PASSWORD)]
    public function changePassword(Request $request, #[CurrentUser] User $user): Response
    {
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);
        $this->validatePassword($form, ChangePasswordFormType::FLD_CURRENT_PASSWORD, $user);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $newPassword */
            $newPassword = $form->get(ChangePasswordFormType::FLD_NEW_PASSWORD)->getData();

            $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
            $this->entityManager->flush();

            $this->logger->info('Changed password.', ['user ID' => $user->getId()]);
            $this->addFlash('success', 'Your password has been changed.');

            return $this->redirectToRoute(RouteName::USER_MAIN);
        }

        return $this->render('user/change_password.html.twig', [
            'password_form' => $form,
        ]);
    }

    #[Route(path: '/change-email', name: RouteName::USER_CHANGE_EMAIL)]
    public function changeEmail(Request $request, #[CurrentUser] User $user, SecurityMailer $mailer,
        UserRepository $userRepository): Response
    {
        $form = $this->createForm(ChangeEmailFormType::class);
        $form->handleRequest($request);
        $this->validatePassword($form, ChangeEmailFormType::FLD_PASSWORD, $user);

        /** @var string $newEmail */
        $newEmail = $form->get(ChangeEmailFormType::FLD_NEW_EMAIL)->getData();

        if ($form->isSubmitted()) {
            $existingUser = $userRepository->findOneBy(['email' => $newEmail]);

            if (null !== $existingUser && $user !== $existingUser) {
                $this->logger->info('User tried to use email of another user.',
                    ['user ID' => $user->getId(), 'email' => Email::obfuscate($newEmail)]);
                $form->get(ChangeEmailFormType::FLD_NEW_EMAIL)
                    ->addError(new FormError('There is already an account registered with the given email.'));
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $oldEmail = $user->getEmail();

            $user->setEmail($newEmail)->setIsVerified(false);
            $this->entityManager->flush();
            $this->logger->info('User email has been changed.', ['user ID' => $user->getId(),
                'old' => Email::obfuscate($oldEmail), 'new' => Email::obfuscate($newEmail)]);

            $mailer->notifyEmailChange($oldEmail, $newEmail);
            $this->logger->info('Sent the email changed notification to the old address.',
                ['user ID' => $user->getId(), 'old' => Email::obfuscate($oldEmail)]);

            $this->emailVerifier->sendEmailConfirmation($user);
            $this->addFlash('warning', 'Your email has been changed. Confirmation email has been sent.'
                .' Please check your inbox and SPAM folders in a few minutes.');

            return $this->redirectToRoute(RouteName::USER_MAIN);
        }

        return $this->render('user/change_email.html.twig', [
            'email_form' => $form,
        ]);
    }

    private function validatePassword(FormInterface $form, string $passwordFieldName, User $user): void
    {
        if (!$form->isSubmitted()) {
            return;
        }

        /** @var string $password */
        $password = $form->get($passwordFieldName)->getData();

        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            $form->get($passwordFieldName)->addError(new FormError('Invalid password.'));
        }
    }
}
