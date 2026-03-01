<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: RouteName::USER_PASSWORD_RESET_REQUEST)]
    public function requestPasswordReset(Request $request, MailerInterface $mailer): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute(RouteName::USER_MAIN);
        }

        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $email */
            $email = $form->get(ResetPasswordRequestFormType::FLD_EMAIL)->getData();

            return $this->processSendingPasswordResetEmail($email, $mailer);
        }

        return $this->render('user/password_reset_request.html.twig', [
            'request_form' => $form,
        ]);
    }

    #[Route('/check-email', name: RouteName::USER_PASSWORD_RESET_EMAIL_SENT)]
    public function emailSentConfirmation(): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute(RouteName::USER_MAIN);
        }

        // Generate a fake token if the user does not exist or someone hit this page directly.
        // This prevents exposing whether or not a user was found with the given email address or not
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return $this->render('user/password_reset_email_sent.html.twig', [
            'reset_token' => $resetToken,
        ]);
    }

    #[Route('/reset/{token}', name: RouteName::USER_PASSWORD_RESET_FORM)]
    public function passwordResetForm(Request $request, UserPasswordHasherInterface $passwordHasher, ?string $token = null): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute(RouteName::USER_MAIN);
        }

        if (null !== $token) {
            // We store the token in session and remove it from the URL, to avoid the URL being
            // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute(RouteName::USER_PASSWORD_RESET_FORM);
        }

        $token = $this->getTokenFromSession();

        if (null === $token) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        try {
            /** @var User $user */
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $exception) {
            $this->addFlash('danger', sprintf(
                '%s - %s',
                ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE,
                $exception->getReason()
            ));

            return $this->redirectToRoute(RouteName::USER_PASSWORD_RESET_REQUEST);
        }

        // The token is valid; allow the user to change their password.
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // A password reset token should be used only once, remove it.
            $this->resetPasswordHelper->removeResetRequest($token);

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Encode(hash) the plain password, and set it.
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $this->entityManager->flush();

            // The session is cleaned up after the password has been changed.
            $this->cleanSessionAfterReset();

            return $this->redirectToRoute(RouteName::USER_LOGIN);
        }

        return $this->render('user/password_reset_form.html.twig', [
            'reset_form' => $form,
        ]);
    }

    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer): RedirectResponse
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute(RouteName::USER_MAIN);
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Do not reveal whether a user account was found or not.
        if (null === $user) {
            return $this->redirectToRoute(RouteName::USER_PASSWORD_RESET_EMAIL_SENT);
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            // If you want to tell the user why a reset email was not sent, uncomment
            // the lines below and change the redirect to 'app_forgot_password_request'.
            // Caution: This may reveal if a user is registered or not.
            //
            // $this->addFlash('reset_password_error', sprintf(
            //     '%s - %s',
            //     ResetPasswordExceptionInterface::MESSAGE_PROBLEM_HANDLE,
            //     $e->getReason()
            // ));

            return $this->redirectToRoute(RouteName::USER_PASSWORD_RESET_EMAIL_SENT);
        }

        $email = new TemplatedEmail() // TODO: Use EmailService
            ->from(new Address('changeme@getfursu.it', 'getfursu.it'))
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('emails/password_reset.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ])
        ;

        $mailer->send($email);

        // Store the token object in session for retrieval in check-email route.
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute(RouteName::USER_PASSWORD_RESET_EMAIL_SENT);
    }
}
