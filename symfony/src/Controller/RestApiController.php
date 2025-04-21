<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DataService;
use App\ValueObject\Routing\RouteName;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

class RestApiController extends AbstractController
{
    #[Route(path: '/api/artisans.json', name: RouteName::API_CREATORS)]
    #[Cache(maxage: 3600, public: true)]
    public function creators(DataService $dataService): JsonResponse
    {
        $result = $dataService->getCreatorsPublicDataJsonString();

        return new JsonResponse($result, json: true);
    }
}
