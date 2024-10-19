<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\CreatorByCreatorIdTrait;
use App\Filtering\DataRequests\FilteredDataProvider;
use App\Filtering\DataRequests\RequestParser;
use App\Filtering\FiltersData\FiltersService;
use App\Repository\ArtisanRepository as CreatorRepository;
use App\Service\Cache as CacheService;
use App\Service\DataService;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Creator\CreatorId;
use App\ValueObject\CacheTags;
use App\ValueObject\Routing\RouteName;
use Psl\Type\Exception\CoercionException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    use CreatorByCreatorIdTrait;

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
        $filters = $this->cache->getCached('mainpage.filters', CacheTags::ARTISANS,
            fn () => $this->filterService->getFiltersTplData());

        return $this->render('main/main.html.twig', [
            'filters' => $filters,
            'stats'   => $this->dataService->getMainPageStats(),
        ]);
    }

    #[Route(path: '/new', name: RouteName::NEW_ARTISANS)]
    #[Cache(maxage: 3600, public: true)]
    public function newCreators(): Response
    {
        return $this->render('main/new.html.twig', [
            'artisans' => Artisan::wrapAll($this->creatorRepository->getNewWithLimit()),
        ]);
    }

    #[Route(path: '/htmx/main/creator-card/{makerId}', name: RouteName::HTMX_MAIN_CREATOR_CARD)]
    #[Cache(maxage: 3600, public: true)]
    public function creatorCard(string $makerId): Response
    {
        $creator = $this->getCreatorByCreatorIdOrThrow404($makerId);

        return $this->render('main/htmx/creator_card.html.twig', [
            'creator' => $creator,
        ]);
    }

    #[Route(path: '/htmx/main/updates-dialog/{makerId}', name: RouteName::HTMX_MAIN_UPDATES_DIALOG)]
    #[Cache(maxage: 3600, public: true)]
    public function updatesDialog(string $makerId): Response
    {
        $creator = $this->getCreatorByCreatorIdOrThrow404($makerId);

        return $this->render('main/htmx/updates_dialog.html.twig', [
            'creator' => $creator,
        ]);
    }

    #[Route(path: '/htmx/main/creators-in-table', name: RouteName::HTMX_MAIN_CREATORS_IN_TABLE)]
    #[Cache(maxage: 3600, public: true)]
    public function htmxCreatorsInTable(Request $request): Response
    {
        try {
            $choices = $this->requestParser->getChoices($request);
            $creatorsPage = $this->filtered->getCreatorsPage($choices);

            $searchedCreatorId = mb_strtoupper($choices->textSearch);

            if (!CreatorId::isValid($searchedCreatorId)) {
                $searchedCreatorId = '';
            }

            return $this->render('main/htmx/creators_in_table.html.twig', [
                'creators_page'        => $creatorsPage,
                'searched_creator_id'  => $searchedCreatorId,
            ]);
        } catch (CoercionException $exception) {
            $this->logger->info('Invalid request received', ['exception' => $exception]);

            return throw new BadRequestException();
        }
    }
}
