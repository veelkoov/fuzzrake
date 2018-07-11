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
     * @Route("/info.html", name="info")
     * @return Response
     */
    public function info(): Response
    {
        return $this->render('frontend/info.html.twig', []);
    }

    /**
     * @Route("/{anything}", name="main", defaults={"anything": ""})
     * @return Response
     */
    public function main(ArtisanRepository $artisanRepository): Response
    {
        $artisans = $artisanRepository->getAll();
        $countryCount = $artisanRepository->getDistinctCountriesCount();
        $types = $artisanRepository->getDistinctTypes();
        $features = $artisanRepository->getDistinctFeatures();
        $countries = $artisanRepository->getDistinctCountries();

        return $this->render('frontend/main.html.twig', [
            'artisans' => $artisans,
            'countryCount' => $countryCount,
            'types' => $types,
            'features' => $features,
            'countries' => $countries
        ]);
    }
}
