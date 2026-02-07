<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\CreatorByCreatorIdTrait;
use App\Repository\CreatorRepository;
use App\Service\DataService;
use App\ValueObject\Routing\RouteName;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

class RestApiController extends AbstractController
{
    use CreatorByCreatorIdTrait;

    public function __construct(
        private readonly CreatorRepository $creatorRepository,
        private readonly DataService $dataService,
    ) {
    }

    #[Route(path: '/api/artisans.json', name: RouteName::API_CREATORS)]
    #[Cache(maxage: 3600, public: true)]
    public function creators(): JsonResponse
    {
        $result = $this->dataService->getCreatorsPublicDataJsonString();

        return new JsonResponse($result, json: true);
    }

    #[Route(path: '/api/creator/{creatorId}', name: RouteName::API_CREATOR)]
    #[Cache(maxage: 900, public: true)]
    public function creatorCard(string $creatorId): Response
    {
        $creator = $this->getCreatorByCreatorIdOrThrow404($creatorId);

        return new JsonResponse($creator->getPublicData());
    }
}
