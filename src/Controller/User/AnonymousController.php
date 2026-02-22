<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class AnonymousController extends AbstractController
{
    public function __construct(
        private readonly EmailVerifier $emailVerifier,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route(path: '/login', name: RouteName::USER_LOGIN)]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute(RouteName::USER_MAIN);
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/register', name: RouteName::USER_REGISTER)]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute(RouteName::USER_MAIN);
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('user/register.html.twig', [
                'registrationForm' => $form,
            ]);
        }

        /** @var string $plainPassword */
        $plainPassword = $form->get('plainPassword')->getData();

        // encode the plain password
        $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

        $entityManager->persist($user);
        $entityManager->flush();

        try {
            $this->emailVerifier->sendEmailConfirmation($user);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error("Failed sending verification email for user ID={$user->getId()}.", ['exception' => $exception]);
            $this->addFlash('error', 'Failed to sent the notification. Please contact the site administration.');
        }

        // FIXME: Redirect to user main instead of main
        return $security->login($user, 'form_login', 'main')
            ?? $this->redirectToRoute(RouteName::USER_LOGIN);
    }

    #[Route('/verifyEmail', name: RouteName::USER_VERIFY_EMAIL)]
    public function verifyEmail(Request $request, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            $this->addFlash('danger', 'Unable to verify email, please retry or contact website administration.');

            return $this->redirectToRoute(RouteName::USER_MAIN);
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            $this->addFlash('danger', 'Unable to verify email, please retry or contact website administration.');

            return $this->redirectToRoute(RouteName::USER_MAIN);
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('danger', $exception->getReason());

            return $this->redirectToRoute(RouteName::USER_MAIN);
        }

        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute(RouteName::USER_MAIN);
    }
}
