<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArtisanRepository;
use App\Service\CountriesDataService;
use App\Service\IuFormService;
use Doctrine\ORM\UnexpectedResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main")
     * @Route("/index.html")
     * @Cache(maxage=3600, public=true)
     *
     * @throws UnexpectedResultException
     */
    public function main(ArtisanRepository $artisanRepository, CountriesDataService $countriesDataService): Response
    {
        return $this->render('main/main.html.twig', [
            'artisans'            => $artisanRepository->getAll(),
            'makerIdsMap'         => $artisanRepository->getOldToNewMakerIdsMap(),
            'countryCount'        => $artisanRepository->getDistinctCountriesCount(),
            'orderTypes'          => $artisanRepository->getDistinctOrderTypes(),
            'styles'              => $artisanRepository->getDistinctStyles(),
            'features'            => $artisanRepository->getDistinctFeatures(),
            'productionModels'    => $artisanRepository->getDistinctProductionModels(),
            'commissionsStatuses' => $artisanRepository->getDistinctCommissionStatuses(),
            'languages'           => $artisanRepository->getDistinctLanguages(),
            'countries'           => $countriesDataService->getFilterData(),
        ]);
    }

    /**
     * @Route("/redirect_iu_form/{makerId}", name="redirect_iu_form")
     * @Cache(maxage=0, public=false)
     *
     * @throws NotFoundHttpException
     */
    public function redirectToIuForm(ArtisanRepository $artisanRepository, IuFormService $iuFormService, string $makerId): Response
    {
        try {
            $artisan = $artisanRepository->findByMakerId($makerId);
        } catch (UnexpectedResultException $e) {
            throw $this->createNotFoundException('Failed to find a maker with given ID');
        }

        return $this->redirect($iuFormService->getUpdateUrl($artisan));
    }
}
