<?php

declare(strict_types=1);

namespace App\Controller;

use App\ValueObject\Routing\RouteName;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PagesController extends AbstractController
{
    #[Route(path: '/info', name: RouteName::INFO)]
    #[Cache(maxage: 21600, public: true)]
    public function info(): Response
    {
        return $this->render('pages/information.html.twig', []);
    }

    #[Route(path: '/tracking', name: RouteName::TRACKING)]
    #[Cache(maxage: 21600, public: true)]
    public function tracking(): Response
    {
        return $this->render('pages/tracking.html.twig', []);
    }

    #[Route(path: '/maker-ids', name: RouteName::MAKER_IDS)]
    #[Cache(maxage: 21600, public: true)]
    public function makerIds(): Response
    {
        return $this->render('pages/maker_ids.html.twig', []);
    }

    #[Route(path: '/donate', name: RouteName::DONATE)]
    #[Cache(maxage: 21600, public: true)]
    public function donate(): Response
    {
        return $this->render('pages/donate.html.twig', []);
    }

    #[Route(path: '/rules', name: RouteName::RULES)]
    #[Cache(maxage: 21600, public: true)]
    public function rules(): Response
    {
        return $this->render('pages/rules.html.twig', []);
    }

    #[Route(path: '/should-know', name: RouteName::SHOULD_KNOW)]
    #[Cache(maxage: 21600, public: true)]
    public function shouldKnow(): Response
    {
        return $this->render('pages/should_know.html.twig', []);
    }
}
