<?php

declare(strict_types=1);

namespace App\Controller\Frontend;

use App\Repository\ArtisanRepository;
use Symfony\Component\Routing\Annotation\Route;
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
     * @Route("/tracking.html", name="tracking")
     *
     * @return Response
     */
    public function tracking(): Response
    {
        return $this->render('frontend/tracking.html.twig', []);
    }

    /**
     * @Route("/whoopsies.html", name="whoopsies")
     *
     * @return Response
     */
    public function whoopsies(): Response
    {
        return $this->render('frontend/whoopsies.html.twig', []);
    }

    /**
     * @Route("/", name="main")
     * @Route("/index.html")
     *
     * @return Response
     */
    public function main(ArtisanRepository $artisanRepository, string $projectDir): Response
    {
        $countriesToCount = $artisanRepository->getDistinctCountriesToCountAssoc();

        return $this->render('frontend/main/main.html.twig', [
            'artisans' => $artisanRepository->getAll(),
            'countryCount' => $artisanRepository->getDistinctCountriesCount(),
            'orderTypes' => $artisanRepository->getDistinctOrderTypes(),
            'styles' => $artisanRepository->getDistinctStyles(),
            'features' => $artisanRepository->getDistinctFeatures(),
            'productionModels' => $artisanRepository->getDistinctProductionModels(),
            'countries' => $this->getCountriesData($countriesToCount, $projectDir),
        ]);
    }

    private function getCountriesData(array $countriesToCount, string $projectDir): array
    {
        $countriesData = json_decode(file_get_contents($projectDir.'/assets/countries.json'), true);
        $result = array_fill_keys(array_map(function (array $country) { return $country['region']; }, $countriesData), []);

        foreach ($countriesData as $countryData) {
            $result[$countryData['region']][] = array_merge($countryData, [
                'count' => $countriesToCount[$countryData['code']],
            ]);
        }

        ksort($result);

        return $result;
    }
}
