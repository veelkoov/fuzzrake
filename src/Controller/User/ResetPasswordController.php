<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Captcha\CaptchaService;
use App\Entity\User;
use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Security\Email;
use App\Security\SecurityMailer;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Cache(public: false, noStore: true)]
#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        #[Autowire(service: 'monolog.logger.fuzzrake.security')]
        private readonly LoggerInterface $logger,
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'rt_user_password_reset_request')]
    public function requestPasswordReset(Request $request, SessionInterface $session, SecurityMailer $mailer,
        CaptchaService $captchaService): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('rt_user_main');
        }

        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);
        $captcha = $captchaService->getCaptcha($session)->handleRequest($request, $form);

        if ($form->isSubmitted() && $form->isValid() && $captcha->isSolved()) {
            /** @var string $email */
            $email = $form->get(ResetPasswordRequestFormType::FLD_EMAIL)->getData();

            return $this->processSendingPasswordResetEmail($email, $mailer);
        }

        return $this->render('user/password_reset_request.html.twig', [
            'request_form' => $form,
        ]);
    }

    #[Route('/check-email', name: 'rt_user_password_reset_email_sent')]
    public function emailSentConfirmation(): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('rt_user_main');
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

    #[Route('/reset/{token}', name: 'rt_user_password_reset_form')]
    public function passwordResetForm(Request $request, UserPasswordHasherInterface $passwordHasher, ?string $token = null): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('rt_user_main');
        }

        if (null !== $token) {
            // We store the token in session and remove it from the URL, to avoid the URL being
            // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('rt_user_password_reset_form');
        }

        $token = $this->getTokenFromSession();

        if (null === $token) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        try {
            /** @var User $user */
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $exception) {
            $this->logger->info('Failed to validate password reset request.', ['exception' => $exception]);
            $this->addFlash('danger', sprintf(
                '%s - %s',
                ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE,
                $exception->getReason(),
            ));

            return $this->redirectToRoute('rt_user_password_reset_request');
        }

        // The token is valid; allow the user to change their password.
        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // A password reset token should be used only once, remove it.
            $this->resetPasswordHelper->removeResetRequest($token);

            /** @var string $newPassword */
            $newPassword = $form->get(ResetPasswordFormType::FLD_NEW_PASSWORD)->getData();

            // Encode(hash) the plain password, and set it.
            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            $this->entityManager->flush();

            // The session is cleaned up after the password has been changed.
            $this->cleanSessionAfterReset();

            $this->addFlash('success', 'Your password has been changed.');
            $this->logger->info('Password reset has been successful.', ['user ID' => $user->getId()]);

            return $this->redirectToRoute('rt_user_login');
        }

        return $this->render('user/password_reset_form.html.twig', [
            'reset_form' => $form,
        ]);
    }

    private function processSendingPasswordResetEmail(string $typedEmail, SecurityMailer $mailer): RedirectResponse
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('rt_user_main');
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $typedEmail,
        ]);

        // Do not reveal whether a user account was found or not.
        if (null === $user) {
            $this->logger->info('Password reset requested for non-registered email.', ['email' => Email::obfuscate($typedEmail)]);

            return $this->redirectToRoute('rt_user_password_reset_email_sent');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $exception) {
            $this->logger->info('Password reset request failed.', ['user ID' => $user->getId(), 'exception' => $exception]);
            // If you want to tell the user why a reset email was not sent, uncomment
            // the lines below and change the redirect to 'app_forgot_password_request'.
            // Caution: This may reveal if a user is registered or not.
            //
            // $this->addFlash('reset_password_error', sprintf(
            //     '%s - %s',
            //     ResetPasswordExceptionInterface::MESSAGE_PROBLEM_HANDLE,
            //     $exception->getReason()
            // ));

            return $this->redirectToRoute('rt_user_password_reset_email_sent');
        }

        $mailer->sendPasswordResetLink($user, $resetToken);
        $this->setTokenObjectInSession($resetToken);
        $this->logger->info('Password reset email sent.', ['user ID' => $user->getId(), 'email' => $user->getEmail()]);

        return $this->redirectToRoute('rt_user_password_reset_email_sent');
    }
}
