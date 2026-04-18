<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Captcha\CaptchaService;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\Email;
use App\Security\EmailVerifier;
use App\Security\Role;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[Cache(public: false, noStore: true)]
class AnonymousController extends AbstractController
{
    public function __construct(
        #[Autowire(service: 'monolog.logger.fuzzrake.security')]
        private readonly LoggerInterface $logger,
        private readonly EmailVerifier $emailVerifier,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/login', name: 'rt_user_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('rt_user_main');
        }

        return $this->render('user/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/register', name: 'rt_user_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher,
        SessionInterface $session, CaptchaService $captchaService): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('rt_user_main');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        $captcha = $captchaService->getCaptcha($session)->handleRequest($request, $form);

        if (!$form->isSubmitted() || !$form->isValid() || !$captcha->isSolved()) {
            return $this->render('user/register.html.twig', [
                'registration_form' => $form,
                'was_submitted' => $form->isSubmitted(),
            ]);
        }

        /** @var string $newPassword */
        $newPassword = $form->get(RegistrationFormType::FLD_NEW_PASSWORD)->getData();
        $user->setPassword($userPasswordHasher->hashPassword($user, $newPassword));

        $user->addRole(Role::CREATOR);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->logger->info('User registered.', ['user ID' => $user->getId(), 'email' => Email::obfuscate($user)]);

        $this->emailVerifier->sendEmailConfirmation($user);
        $this->addFlash('success', 'Confirmation email has been sent. Please check your inbox and SPAM folders in a few minutes.');

        return $this->redirectToRoute('rt_user_login');
    }

    #[Route('/verify-email', name: 'rt_user_verify_email')]
    public function verifyEmail(Request $request, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            $this->logger->info('Missing ID for email verification.');
            $this->addFlash('danger', 'Unable to verify email, please retry or contact website administration.');

            return $this->redirectToRoute('rt_user_main');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            $this->logger->info('Unable to find user for email verification.', ['given ID' => $id]);
            $this->addFlash('danger', 'Unable to verify email, please retry or contact website administration.');

            return $this->redirectToRoute('rt_user_main');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
            $this->entityManager->flush();
            $this->logger->info('Successfully confirmed email.', ['user ID' => $user->getId(), 'email' => Email::obfuscate($user)]);
            $this->addFlash('success', 'Your email address has been confirmed.');
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->logger->info('Failed to confirm email.', ['user ID' => $user->getId(), 'exception' => $exception]);
            $this->addFlash('danger', $exception->getReason());
        }

        return $this->redirectToRoute('rt_user_main');
    }
}
