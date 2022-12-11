<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArtisanRepository;
use App\Repository\MakerIdRepository;
use App\Service\DataService;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Filters\FiltersService;
use App\Utils\Species\SpeciesService;
use App\ValueObject\CacheTags;
use App\ValueObject\Routing\RouteName;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class MainController extends AbstractController
{
    /**
     * @throws InvalidArgumentException
     */
    #[Route(path: '/', name: RouteName::MAIN)]
    #[Cache(maxage: 3600, public: true)]
    public function main(MakerIdRepository $makerIdRepository, FiltersService $filterService,
                         SpeciesService $speciesService, DataService $dataService,
                         TagAwareCacheInterface $cache): Response
    {
        $filters = $cache->get('mainpage.filters', function (ItemInterface $item) use ($filterService) {
            $item->tag(CacheTags::ARTISANS);

            return $filterService->getFiltersTplData();
        });

        $oldToNewMakerIdsMap = $cache->get('mainpage.oldToNewMakerIdsMap', function (ItemInterface $item) use ($makerIdRepository) {
            $item->tag(CacheTags::ARTISANS);

            return $makerIdRepository->getOldToNewMakerIdsMap();
        });

        $species = $cache->get('mainpage.species', function (ItemInterface $item) use ($speciesService) {
            $item->tag(CacheTags::CODE);

            return $speciesService->getVisibleTree();
        });

        return $this->render('main/main.html.twig', [
            'makerIdsMap'         => $oldToNewMakerIdsMap,
            'stats'               => $dataService->getMainPageStats(),
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
