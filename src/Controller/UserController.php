<?php

declare(strict_types=1);

namespace App\Controller;

use App\ValueObject\Routing\RouteName;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
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

    #[Route(path: '/logout', name: RouteName::USER_LOGOUT)]
    public function logout(): void
    {
        throw new LogicException('This should have been intercepted by the firewall.');
    }

    #[Route(path: '/user', name: RouteName::USER_MAIN)]
    public function landing(): Response
    {
        return $this->render('user/main.html.twig');
    }
}
