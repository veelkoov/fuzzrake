<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArtisanRepository;
use App\Service\FilterService;
use App\Service\IuFormService;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\Species\Species;
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
     * @Cache(public=true)
     *
     * @throws UnexpectedResultException|DateTimeException
     */
    public function main(Request $request, ArtisanRepository $artisanRepository, FilterService $filterService, Species $species): Response
    {
        if ('hexometer' === $request->get('ref')) {
            return new Response('*Notices your scan* OwO what\'s this?', Response::HTTP_MISDIRECTED_REQUEST,
                ['Content-Type' => 'text/plain; charset=UTF-8']
            );
        }

        $response = $this->render('main/main.html.twig', [
            'artisans'            => $artisanRepository->getAll(),
            'activeArtisansCount' => $artisanRepository->getActiveCount(),
            'makerIdsMap'         => $artisanRepository->getOldToNewMakerIdsMap(),
            'countryCount'        => $artisanRepository->getDistinctCountriesCount(),
            'filters'             => $filterService->getFiltersTplData(),
            'species'             => $species->getSpeciesTree(),
        ]);

        self::setExpires($response);

        return $response;
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

    /**
     * @throws DateTimeException
     */
    private static function setExpires(Response $response): void
    {
        $now = DateTimeUtils::getNowUtc();

        $nextExpiresCandidates = [ // TODO: Move to configuration, make somehow connected to the Cron job; make safer assumptions (e.g. max 4-6h)?
            DateTimeUtils::getUtcAt('6:10'),
            DateTimeUtils::getUtcAt('18:10'),
            DateTimeUtils::getUtcAt('tomorrow 6:10'),
        ];

        foreach ($nextExpiresCandidates as $nextExpires) {
            if ($nextExpires > $now) {
                $response->setExpires($nextExpires);

                return;
            }
        }
    }
}
