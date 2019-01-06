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
     * @Route("/", name="main")
     * @Route("/index.html")
     *
     * @return Response
     */
    public function main(ArtisanRepository $artisanRepository, string $projectDir): Response
    {
        $artisans = $artisanRepository->getAll();
        $countryCount = $artisanRepository->getDistinctCountriesCount();
        $types = $artisanRepository->getDistinctTypes();
        $styles = $artisanRepository->getDistinctStyles();
        $features = $artisanRepository->getDistinctFeatures();
        $countriesToCount = $artisanRepository->getDistinctCountriesToCountAssoc();

        return $this->render('frontend/main/main.html.twig', [
            'artisans' => $artisans,
            'countryCount' => $countryCount,
            'types' => $types,
            'styles' => $styles,
            'features' => $features,
            'countries' => $this->getCountriesData($countriesToCount, $projectDir),
        ]);
    }

    private function getCountriesData(array $countriesToCount, string $projectDir): array
    {
        $countriesData = $this->getRawCountriesDataFilteredAssoc($projectDir, array_keys($countriesToCount));
        $result = array_fill_keys(array_map(function (array $country) { return $country['region']; }, $countriesData), []);

        foreach ($countriesData as $countryData) {
            $result[$countryData['region']][] = $this->getCountryData($countryData, $countriesToCount);
        }

        ksort($result);

        return $result;
    }

    private function getRawCountriesDataFilteredAssoc(string $projectDir, array $countriesCodes)
    {
        $countriesData = json_decode(file_get_contents($projectDir.'/assets/3rd-party/ISO-3166-Countries-with-Regional-Codes/all/all.json'), true);
        $countriesData = array_filter($countriesData, function (array $item) use ($countriesCodes) {
            return in_array($item['alpha-2'], $countriesCodes);
        });

        $result = [];

        foreach ($countriesData as $countryData) {
            $result[$countryData['alpha-2']] = $countryData;
        }

        return $result;
    }

    private function getCountryData(array $countryData, array $countriesToCount): array
    {
        return [
            'originalData' => $countryData, // TODO: drop
            'code' => $countryData['alpha-2'],
            'count' => $countriesToCount[$countryData['alpha-2']],
            'name' => $countryData['name'],
            'region' => in_array($countryData['region'], ['Americas', 'Europe']) || empty($countryData['intermediate-region']) ? $countryData['region'] : $countryData['intermediate-region'],
        ];
    }
}
