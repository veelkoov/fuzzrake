<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\CreatorByMakerIdTrait;
use App\Filtering\DataRequests\FilteredDataProvider;
use App\Filtering\DataRequests\RequestParser;
use App\Filtering\FiltersData\FiltersService;
use App\Repository\ArtisanRepository as CreatorRepository;
use App\Service\Cache as CacheService;
use App\Service\DataService;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\ValueObject\CacheTags;
use App\ValueObject\Routing\RouteName;
use Psl\Type\Exception\CoercionException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    use CreatorByMakerIdTrait;

    public function __construct(
        private readonly CreatorRepository $creatorRepository,
        private readonly LoggerInterface $logger,
        private readonly FilteredDataProvider $filtered,
        private readonly RequestParser $requestParser,
        private readonly FiltersService $filterService,
        private readonly CacheService $cache,
        private readonly DataService $dataService,
    ) {
    }

    #[Route(path: '/', name: RouteName::MAIN)]
    #[Cache(maxage: 3600, public: true)]
    public function main(): Response
    {
        return $this->render('main/main.html.twig', [
            'stats' => $this->dataService->getMainPageStats(),
        ]);
    }

    #[Route(path: '/new', name: RouteName::NEW_ARTISANS)]
    #[Cache(maxage: 3600, public: true)]
    public function newCreators(): Response
    {
        return $this->render('main/new.html.twig', [
            'artisans' => Artisan::wrapAll($this->creatorRepository->getNew()),
        ]);
    }

    #[Route(path: '/htmx/main/creator-card/{makerId}', name: RouteName::HTMX_CREATOR_CARD)]
    #[Cache(maxage: 3600, public: true)]
    public function creatorCard(string $makerId): Response
    {
        $creator = $this->getCreatorByMakerIdOrThrow404($makerId);

        return $this->render('main/htmx/creator_card.html.twig', [
            'creator' => $creator,
        ]);
    }

    #[Route(path: '/htmx/main/updates-dialog/{makerId}', name: RouteName::HTMX_UPDATES_DIALOG)]
    #[Cache(maxage: 3600, public: true)]
    public function updatesDialog(string $makerId): Response
    {
        $creator = $this->getCreatorByMakerIdOrThrow404($makerId);

        return $this->render('main/htmx/updates_dialog.html.twig', [
            'creator' => $creator,
        ]);
    }

    #[Route(path: '/htmx/main/primary-content', name: RouteName::HTMX_MAIN_PRIMARY_CONTENT)]
    #[Cache(maxage: 3600, public: true)]
    public function htmxMainPrimaryContent(Request $request): Response
    {
        try {
            $choices = $this->requestParser->getChoices($request);
            $creators = $this->filtered->getFilteredCreators($choices);

            $filters = $this->cache->getCached('mainpage.filters', CacheTags::ARTISANS,
                fn () => $this->filterService->getFiltersTplData());

            return $this->render('main/htmx/primary_content.html.twig', [
                'creators' => $creators,
                'active_filters_count' => 123, // TODO
                'filters' => $filters,
                'total_creators_count' => $this->dataService->getMainPageStats()->totalArtisansCount,
            ]);
        } catch (CoercionException $exception) {
            $this->logger->info('Invalid request received', ['exception' => $exception]);

            return throw new BadRequestException();
        }
    }
}
