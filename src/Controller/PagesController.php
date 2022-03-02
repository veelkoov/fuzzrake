<?php

declare(strict_types=1);

namespace App\Controller;

use App\ValueObject\Routing\RouteName;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PagesController extends AbstractController
{
    #[Route(path: '/data_updates.html', name: RouteName::DATA_UPDATES)]
    #[Cache(maxage: 21600, public: true)]
    public function dataUpdates(): Response
    {
        return $this->render('pages/data_updates.html.twig', []);
    }

    #[Route(path: '/info.html', name: RouteName::INFO)]
    #[Cache(maxage: 21600, public: true)]
    public function info(): Response
    {
        return $this->render('pages/information.html.twig', []);
    }

    #[Route(path: '/tracking.html', name: RouteName::TRACKING)]
    #[Cache(maxage: 21600, public: true)]
    public function tracking(): Response
    {
        return $this->render('pages/tracking.html.twig', []);
    }

    #[Route(path: '/maker_ids.html', name: RouteName::MAKER_IDS)]
    #[Cache(maxage: 21600, public: true)]
    public function makerIds(): Response
    {
        return $this->render('pages/maker_ids.html.twig', []);
    }

    #[Route(path: '/donate.html', name: RouteName::DONATE)]
    #[Cache(maxage: 21600, public: true)]
    public function donate(): Response
    {
        return $this->render('pages/donate.html.twig', []);
    }

    #[Route('/rules.html', name: RouteName::RULES)]
    #[Cache(maxage: 21600, public: true)]
    public function rules(): Response
    {
        return $this->render('pages/rules.html.twig', []);
    }
}
