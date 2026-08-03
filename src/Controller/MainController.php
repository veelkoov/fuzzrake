<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Utils\CreatorByCreatorIdTrait;
use App\Filtering\FiltersData\FiltersService;
use App\Filtering\RequestsHandling\FilteredDataProvider;
use App\Filtering\RequestsHandling\RequestParser;
use App\Repository\CreatorRepository;
use App\Service\DataService;
use App\Utils\Creator\CreatorId;
use App\Utils\Creator\SmartAccessDecorator as Creator;
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

    #[Route(path: '/', name: 'rt_main')]
    #[Cache(maxage: 0, public: false, noStore: true)] // TODO: Revert
    public function main(): Response
    {
        return $this->render('main/main.html.twig', [
            'filters' => $this->filterService->getCachedFiltersTplData(),
            'stats'   => $this->dataService->getMainPageStats(),
        ]);
    }

    #[Route(path: '/c/{creatorId}', name: 'rt_creator')]
    #[Cache(maxage: 900, public: true)]
    public function creator(string $creatorId): Response
    {
        $creator = $this->getCreatorByCreatorIdOrThrow404($creatorId);

        return $this->render('main/creator.html.twig', [
            'creator' => $creator,
        ]);
    }

    #[Route(path: '/htmx/menu', name: 'rt_htmx_menu')]
    #[Cache(public: false, noStore: true)]
    public function menu(Request $request): Response
    {
        return $this->render('_menu.html.twig', [
            'ignore_session' => $request->query->get('ignore_session'),
        ]);
    }

    #[Route(path: '/new', name: 'rt_new_creators')]
    #[Cache(maxage: 900, public: true)]
    public function newCreators(): Response
    {
        return $this->render('main/new.html.twig', [
            'creators' => Creator::wrapAll($this->creatorRepository->getNewWithLimit()),
        ]);
    }

    // TODO: Remove
    #[Route(path: '/htmx/main/creator-card/{creatorId}', name: 'rt_htmx_main_creator_card')]
    #[Cache(maxage: 900, public: true)]
    public function creatorCard(string $creatorId): Response
    {
        $creator = $this->getCreatorByCreatorIdOrThrow404($creatorId);

        return $this->render('main/htmx/creator_card.html.twig', [
            'creator' => $creator,
        ]);
    }

    #[Route(path: '/htmx/main/creators-list', name: 'rt_htmx_main_creators_list')]
    #[Cache(maxage: 900, public: true)]
    public function htmxCreatorsList(Request $request): Response
    {
        try {
            $choices = $this->requestParser->getChoices($request);
            $creatorsPage = $this->filtered->getCreatorsPage($choices);

            $searchedCreatorId = mb_strtoupper($choices->textSearch);

            if (!CreatorId::isValid($searchedCreatorId)) {
                $searchedCreatorId = '';
            }

            return $this->render('main/parts/creators_list_v2.html.twig', [
                'creators_page'       => $creatorsPage,
                'searched_creator_id' => $searchedCreatorId,
            ]);
        } catch (InvalidArgumentException $exception) {
            return throw new BadRequestException(previous: $exception);
        }
    }
}
