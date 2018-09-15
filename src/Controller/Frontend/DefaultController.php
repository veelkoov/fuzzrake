<?php

declare(strict_types=1);

namespace App\Controller\Frontend;

use App\Repository\ArtisanRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/")
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/info.html", name="info")
     *
     * @return Response
     */
    public function info(): Response
    {
        return $this->render('frontend/info.html.twig', []);
    }

    /**
     * @Route("/", name="main")
     * @Route("/index.html")
     *
     * @return Response
     */
    public function main(ArtisanRepository $artisanRepository): Response
    {
        $artisans = $artisanRepository->getAll();
        $countryCount = $artisanRepository->getDistinctCountriesCount();
        $types = $artisanRepository->getDistinctTypes();
        $styles = $artisanRepository->getDistinctStyles();
        $features = $artisanRepository->getDistinctFeatures();
        $countries = $artisanRepository->getDistinctCountries();

        return $this->render('frontend/main.html.twig', [
            'artisans' => $artisans,
            'countryCount' => $countryCount,
            'types' => $types,
            'styles' => $styles,
            'features' => $features,
            'countries' => $countries,
        ]);
    }
}
