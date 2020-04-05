<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArtisanRepository;
use App\Service\FilterService;
use App\Service\IuFormService;
use Doctrine\ORM\UnexpectedResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
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
     * @Cache(maxage=3600, public=true)
     *
     * @throws UnexpectedResultException
     */
    public function main(Request $request, ArtisanRepository $artisanRepository, FilterService $filterService): Response
    {
        if ('hexometer' === $request->get('ref')) {
            return new Response('*Notices your scan* OwO what\'s this?', Response::HTTP_MISDIRECTED_REQUEST,
                ['Content-Type' => 'text/plain; charset=UTF-8']
            );
        }

        return $this->render('main/main.html.twig', [
            'artisans'            => $artisanRepository->getAll(),
            'activeArtisansCount' => $artisanRepository->getActiveCount(),
            'makerIdsMap'         => $artisanRepository->getOldToNewMakerIdsMap(),
            'countryCount'        => $artisanRepository->getDistinctCountriesCount(),
            'filters'             => $filterService->getFiltersTplData(),
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
