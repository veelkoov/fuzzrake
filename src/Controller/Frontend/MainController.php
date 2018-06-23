<?php

namespace App\Controller\Frontend;


use App\Repository\ArtisanRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/")
 */
class MainController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     * @return Response
     */
    public function index(ArtisanRepository $artisanRepository): Response
    {
        $artisans = $artisanRepository->getAll();
        $countryCount = $artisanRepository->getDistinctCountriesCount();

        return $this->render('frontend/main.html.twig', ['artisans' => $artisans, 'countryCount' => $countryCount]);
    }

    /**
     * @Route("/data.json", name="data")
     * @return Response
     */
    public function data(ArtisanRepository $artisanRepository): JsonResponse
    {
        $artisans = $artisanRepository->getAll();

        $response = new JsonResponse($artisans);

        return $response;
    }
}
