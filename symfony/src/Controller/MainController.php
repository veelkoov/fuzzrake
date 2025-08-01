<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\CreatorByCreatorIdTrait;
use App\Filtering\DataRequests\FilteredDataProvider;
use App\Filtering\DataRequests\RequestParser;
use App\Filtering\FiltersData\FiltersService;
use App\Repository\CreatorRepository;
use App\Service\DataService;
use App\Utils\Creator\CreatorId;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\ValueObject\Routing\RouteName;
use InvalidArgumentException;
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
        private readonly FilteredDataProvider $filtered,
        private readonly RequestParser $requestParser,
        private readonly FiltersService $filterService,
        private readonly DataService $dataService,
    ) {
    }

    #[Route(path: '/', name: RouteName::MAIN)]
    #[Cache(maxage: 3600, public: true)]
    public function main(): Response
    {
        return $this->render('main/main.html.twig', [
            'filters' => $this->filterService->getCachedFiltersTplData(),
            'stats'   => $this->dataService->getMainPageStats(),
        ]);
    }

    #[Route(path: '/new', name: RouteName::NEW_CREATORS)]
    #[Cache(maxage: 3600, public: true)]
    public function newCreators(): Response
    {
        return $this->render('main/new.html.twig', [
            'creators' => Creator::wrapAll($this->creatorRepository->getNewWithLimit()),
        ]);
    }

    #[Route(path: '/htmx/main/creator-card/{creatorId}', name: RouteName::HTMX_MAIN_CREATOR_CARD)]
    #[Cache(maxage: 3600, public: true)]
    public function creatorCard(string $creatorId): Response
    {
        $creator = $this->getCreatorByCreatorIdOrThrow404($creatorId);

        return $this->render('main/htmx/creator_card.html.twig', [
            'creator' => $creator,
        ]);
    }

    #[Route(path: '/htmx/main/updates-dialog/{creatorId}', name: RouteName::HTMX_MAIN_UPDATES_DIALOG)]
    #[Cache(maxage: 3600, public: true)]
    public function updatesDialog(string $creatorId): Response
    {
        $creator = $this->getCreatorByCreatorIdOrThrow404($creatorId);

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
        } catch (InvalidArgumentException $exception) {
            return throw new BadRequestException(previous: $exception);
        }
    }
}
