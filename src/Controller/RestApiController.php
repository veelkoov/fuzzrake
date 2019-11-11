<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArtisanRepository;
use App\Service\HealthCheckService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RestApiController extends AbstractController
{
    /**
     * @Route("/api/artisans.json", name="api_artisans")
     *
     * @return JsonResponse
     */
    public function artisans(ArtisanRepository $artisanRepository): Response
    {
        return new JsonResponse($artisanRepository->getAll());
    }

    /**
     * @Route("/api/old_to_new_maker_ids_map.json", name="api_old_to_new_maker_ids_map")
     *
     * @return JsonResponse
     */
    public function oldToNewMakerIdsMap(ArtisanRepository $artisanRepository): Response
    {
        return new JsonResponse($artisanRepository->getOldToNewMakerIdsMap());
    }

    /**
     * @Route("/health", name="health")
     *
     * @return JsonResponse
     */
    public function healthcheck(HealthCheckService $healthCheckService): Response
    {
        return new JsonResponse($healthCheckService->getStatus());
    }
}
