<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArtisanCommissionsStatusRepository;
use App\Repository\ArtisanRepository;
use App\Utils\DateTimeUtils;
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
     * @param ArtisanCommissionsStatusRepository $acsr
     *
     * @return JsonResponse
     */
    public function healthcheck(ArtisanCommissionsStatusRepository $acsr): Response
    {
        return new JsonResponse([
            'status'        => 'OK',
            'lastCstRunUtc' => $acsr->getLastCstUpdateTimeAsString(),
            'serverTimeUtc' => DateTimeUtils::getNowUtc()->format('Y-m-d H:i:s'),
        ]);
    }
}
