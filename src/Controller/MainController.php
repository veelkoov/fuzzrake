<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArtisanRepository;
use App\Service\IuFormService;
use App\Utils\FilterItems;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main")
     * @Route("/index.html")
     *
     * @param ArtisanRepository $artisanRepository
     * @param string            $projectDir
     *
     * @return Response
     *
     * @throws NonUniqueResultException
     */
    public function main(ArtisanRepository $artisanRepository, string $projectDir): Response
    {
        $countriesToCount = $artisanRepository->getDistinctCountriesToCountAssoc();

        return $this->render('main/main.html.twig', [
            'artisans'            => $artisanRepository->getAll(),
            'countryCount'        => $artisanRepository->getDistinctCountriesCount(),
            'orderTypes'          => $artisanRepository->getDistinctOrderTypes(),
            'styles'              => $artisanRepository->getDistinctStyles(),
            'features'            => $artisanRepository->getDistinctFeatures(),
            'productionModels'    => $artisanRepository->getDistinctProductionModels(),
            'commissionsStatuses' => $artisanRepository->getDistinctCommissionStatuses(),
            'languages'           => $artisanRepository->getDistinctLanguages(),
            'countries'           => $this->getCountriesFilterData($countriesToCount, $projectDir),
        ]);
    }

    /**
     * @Route("/redirect_iu_form/{makerId}", name="redirect_iu_form")
     *
     * @param ArtisanRepository $artisanRepository
     * @param IuFormService     $iuFormService
     * @param string            $makerId
     *
     * @return Response
     *
     * @throws NotFoundHttpException
     */
    public function redirectToIuForm(ArtisanRepository $artisanRepository, IuFormService $iuFormService, string $makerId): Response
    {
        try {
            $artisan = $artisanRepository->findByMakerId($makerId);
        } catch (NonUniqueResultException | NoResultException $e) {
            throw $this->createNotFoundException('Failed to find a maker with given ID');
        }

        return $this->redirect($iuFormService->getUpdateUrl($artisan));
    }

    private function getCountriesFilterData(FilterItems $countries, string $projectDir): FilterItems
    {
        $countriesData = $this->loadCountriesData($projectDir);

        $result = $this->getRegionsFromCountries($countriesData);
        $result->incUnknownCount($countries->getUnknownCount());

        foreach ($countriesData as $countryData) {
            $code = $countryData['code'];
            $region = $countryData['region'];

            $countryCount = $countries->offsetExists($code) ? $countries[$code]->getCount() : 0;

            $result[$region]->incCount($countryCount);
            $result[$region]->getValue()->addComplexItem($code, $code, $countryData['name'], $countryCount);
        }

        return $result;
    }

    private function getRegionsFromCountries(array $countriesData): FilterItems
    {
        $regionNames = array_unique(array_map(function (array $country): string {
            return $country['region'];
        }, $countriesData));

        $result = new FilterItems(false);

        foreach ($regionNames as $regionName) {
            $result->addComplexItem($regionName, new FilterItems(false), $regionName, 0);
        }

        return $result;
    }

    /**
     * @param string $projectDir
     *
     * @return array [ [ "name" => "...", "code" => "...", "region" => "..."], ... ]
     */
    private function loadCountriesData(string $projectDir): array
    {
        return json_decode(file_get_contents($projectDir.'/assets/countries.json'), true);
    }
}
