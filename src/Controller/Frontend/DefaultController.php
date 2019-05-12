<?php

declare(strict_types=1);

namespace App\Controller\Frontend;

use App\Repository\ArtisanRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("/maker_ids.html", name="maker_ids")
     *
     * @return Response
     */
    public function makerIds(): Response
    {
        return $this->render('frontend/maker_ids.html.twig', []);
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
            'countries' => $this->getCountriesFilterData($countriesToCount, $projectDir),
        ]);
    }

    private function getCountriesFilterData(array $countriesToCount, string $projectDir): array
    {
        $countriesData = json_decode(file_get_contents($projectDir.'/assets/countries.json'), true);
        $regions = $this->getRegionsFromCountries($countriesData);

        foreach ($countriesData as $countryData) {
            $regions[$countryData['region']]['countries'][] = array_merge($countryData, [
                'count' => $countriesToCount['items'][$countryData['code']],
            ]);

            $regions[$countryData['region']]['total_count'] += $countriesToCount['items'][$countryData['code']];
        }

        ksort($regions);

        return [
            'regions' => $regions,
            'unknown_count' => $countriesToCount['unknown_count'],
        ];
    }

    private function getRegionsFromCountries($countriesData): array
    {
        $result = array_fill_keys(array_map(function (array $country) {
            return $country['region'];
        }, $countriesData), [
            'countries' => [],
            'total_count' => 0,
        ]);

        return $result;
    }
}
