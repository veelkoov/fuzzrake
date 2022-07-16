<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArtisanRepository;
use App\Repository\MakerIdRepository;
use App\Service\DataOnDemand\ArtisansDOD;
use App\Service\Statistics\StatisticsService;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Filters\FiltersService;
use App\Utils\Species\SpeciesService;
use App\ValueObject\Routing\RouteName;
use Psr\Cache\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class MainController extends AbstractController
{
    /**
     * @throws InvalidArgumentException
     */
    #[Route(path: '/', name: RouteName::MAIN)]
    #[Cache(maxage: 3600, public: true)]
    public function main(ArtisansDOD $artisans, MakerIdRepository $makerIdRepository, FiltersService $filterService,
        SpeciesService $speciesService, StatisticsService $statisticsService,
        TagAwareCacheInterface $cache): Response
    {
        $filters = $cache->get('mainpage.filters', fn () => $filterService->getFiltersTplData());
        $statistics = $cache->get('mainpage.statistics', fn () => $statisticsService->getMainPageStats());
        $oldToNewMakerIdsMap = $cache->get('mainpage.oldToNewMakerIdsMap', fn () => $makerIdRepository->getOldToNewMakerIdsMap());
        $species = $cache->get('mainpage.species', fn () => $speciesService->getTree());

        return $this->render('main/main.html.twig', [
            'artisans'            => $artisans,
            'makerIdsMap'         => $oldToNewMakerIdsMap,
            'stats'               => $statistics,
            'filters'             => $filters,
            'species'             => $species,
        ]);
    }

    #[Route(path: '/new', name: RouteName::NEW_ARTISANS)]
    #[Cache(maxage: 3600, public: true)]
    public function newArtisans(ArtisanRepository $artisanRepository): Response
    {
        return $this->render('main/new.html.twig', [
            'artisans' => Artisan::wrapAll($artisanRepository->getNew()),
        ]);
    }
}
