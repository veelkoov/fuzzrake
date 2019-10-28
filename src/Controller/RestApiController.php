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
     * @param ArtisanRepository $artisanRepository
     *
     * @return JsonResponse
     */
    public function artisans(ArtisanRepository $artisanRepository): Response
    {
        return new JsonResponse($artisanRepository->getAll());
    }

    /**
     * @Route("/health", name="health")
     *
     * @param HealthCheckService $healthCheckService
     *
     * @return JsonResponse
     */
    public function healthcheck(HealthCheckService $healthCheckService): Response
    {
        return new JsonResponse($healthCheckService->getStatus());
    }
}
