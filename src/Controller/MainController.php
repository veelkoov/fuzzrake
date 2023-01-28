<?php

declare(strict_types=1);

namespace App\Controller;

use App\Filtering\FiltersData\FiltersService;
use App\Repository\ArtisanRepository;
use App\Service\Cache as CacheService;
use App\Service\DataService;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\ValueObject\CacheTags;
use App\ValueObject\Routing\RouteName;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @throws InvalidArgumentException
     */
    #[Route(path: '/', name: RouteName::MAIN)]
    #[Cache(maxage: 3600, public: true)]
    public function main(FiltersService $filterService, DataService $dataService, CacheService $cache): Response
    {
        $filters = $cache->getCached('mainpage.filters', CacheTags::ARTISANS,
            fn() => $filterService->getFiltersTplData());

        return $this->render('main/main.html.twig', [
            'stats'   => $dataService->getMainPageStats(),
            'filters' => $filters,
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
