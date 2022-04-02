<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArtisanRepository;
use App\Repository\MakerIdRepository;
use App\Service\FilterService;
use App\Service\Statistics\StatisticsService;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Species\Species;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\UnexpectedResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @throws UnexpectedResultException
     */
    #[Route(path: '/', name: RouteName::MAIN)]
    #[Route(path: '/index.html')]
    #[Cache(maxage: 3600, public: true)]
    public function main(ArtisanRepository $artisanRepository, MakerIdRepository $makerIdRepository, FilterService $filterService, Species $species, StatisticsService $statistics): Response
    {
        return $this->render('main/main.html.twig', [
            'artisans'            => Artisan::wrapAll($artisanRepository->getAll()),
            'makerIdsMap'         => $makerIdRepository->getOldToNewMakerIdsMap(),
            'stats'               => $statistics->getMainPageStats(),
            'filters'             => $filterService->getFiltersTplData(),
            'species'             => $species->getTree(),
        ]);
    }

    #[Route(path: '/new.html', name: RouteName::NEW_ARTISANS)]
    #[Cache(maxage: 3600, public: true)]
    public function newArtisans(ArtisanRepository $artisanRepository): Response
    {
        return $this->render('main/new.html.twig', [
            'artisans' => Artisan::wrapAll($artisanRepository->getNew()),
        ]);
    }
}
