<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DataService;
use App\ValueObject\Routing\RouteName;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

class PagesController extends AbstractController
{
    public function __construct(
        private readonly DataService $dataService,
    ) {
    }

    #[Route(path: '/info', name: RouteName::INFO)]
    #[Cache(maxage: 21600, public: true)]
    public function info(): Response
    {
        return $this->render('pages/information.html.twig', []);
    }

    #[Route(path: '/contact', name: RouteName::CONTACT)]
    #[Cache(maxage: 21600, public: true)]
    public function contact(): Response
    {
        return $this->render('pages/contact.html.twig', [
            'ooo_notice' => $this->dataService->getOooNotice(),
        ]);
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

    #[Route(path: '/guidelines', name: RouteName::GUIDELINES)]
    #[Cache(maxage: 21600, public: true)]
    public function guidelines(): Response
    {
        return $this->render('pages/guidelines.html.twig', []);
    }

    #[Route(path: '/should-know', name: RouteName::SHOULD_KNOW)]
    #[Cache(maxage: 21600, public: true)]
    public function shouldKnow(): Response
    {
        return $this->render('pages/should_know.html.twig', []);
    }
}
