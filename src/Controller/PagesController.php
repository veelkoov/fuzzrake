<?php

declare(strict_types=1);

namespace App\Controller;

use App\Captcha\CaptchaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

class PagesController extends AbstractController
{
    #[Route(path: '/info', name: 'rt_info')]
    #[Cache(maxage: 3600, public: true)]
    public function info(): Response
    {
        return $this->render('pages/information.html.twig', []);
    }

    #[Route(path: '/contact', name: 'rt_contact')]
    #[Cache(maxage: 3600, public: true)]
    public function contact(CaptchaService $captchaService, SessionInterface $session, Request $request,
        #[Autowire(env: 'CONTACT_EMAIL')] string $contactEmail): Response
    {
        $form = $captchaService->getStandaloneForm();
        $captcha = $captchaService->getCaptcha($session)->handleRequest($request, $form);

        return $this->render('pages/contact.html.twig', [
            'form' => $form,
            'is_solved' => $captcha->isSolved(),
            'contact_email' => $contactEmail,
        ]);
    }

    #[Route(path: '/tracking', name: 'rt_tracking')]
    #[Cache(maxage: 3600, public: true)]
    public function tracking(): Response
    {
        return $this->render('pages/tracking.html.twig', []);
    }

    #[Route(path: '/maker-ids', name: 'rt_creator_ids')]
    #[Cache(maxage: 3600, public: true)]
    public function creatorIds(): Response
    {
        return $this->render('pages/creator_ids.html.twig', []);
    }

    #[Route(path: '/donate', name: 'rt_donate')]
    #[Cache(maxage: 3600, public: true)]
    public function donate(): Response
    {
        return $this->render('pages/donate.html.twig', []);
    }

    #[Route(path: '/guidelines', name: 'rt_guidelines')]
    #[Cache(maxage: 3600, public: true)]
    public function guidelines(): Response
    {
        return $this->render('pages/guidelines.html.twig', []);
    }

    #[Route(path: '/should-know', name: 'rt_should_know')]
    #[Cache(maxage: 3600, public: true)]
    public function shouldKnow(): Response
    {
        return $this->render('pages/should_know.html.twig', []);
    }
}
