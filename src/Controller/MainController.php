<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArtisanRepository;
use App\Service\CountriesDataService;
use App\Service\IuFormService;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Fig\Link\Link;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main")
     * @Route("/index.html")
     *
     * @throws NonUniqueResultException
     */
    public function main(Request $request, ArtisanRepository $artisanRepository, CountriesDataService $countriesDataService): Response
    {
        $this->addLink($request, new Link('preload', $this->generateUrl('api_artisans')));
        $this->addLink($request, new Link('preload', $this->generateUrl('api_old_to_new_maker_ids_map')));

        return $this->render('main/main.html.twig', [
            'artisans'            => $artisanRepository->getAll(),
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
}
