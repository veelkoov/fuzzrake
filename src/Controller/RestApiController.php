<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArtisanRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function artisans(ArtisanRepository $artisanRepository)
    {
        return new JsonResponse($artisanRepository->getAll());
    }
}
