<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArtisanRepository;
use App\Repository\MakerIdRepository;
use App\Service\FilterService;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\Species\Species;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\UnexpectedResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @throws UnexpectedResultException|DateTimeException
     */
    #[Route(path: '/', name: RouteName::MAIN)]
    #[Route(path: '/index.html')]
    #[Cache(public: true)]
    public function main(Request $request, ArtisanRepository $artisanRepository, MakerIdRepository $makerIdRepository, FilterService $filterService, Species $species): Response
    {
        if ('hexometer' === $request->get('ref')) {
            return new Response('*Notices your scan* OwO what\'s this?', Response::HTTP_MISDIRECTED_REQUEST,
                ['Content-Type' => 'text/plain; charset=UTF-8']
            );
        }

        $response = $this->render('main/main.html.twig', [
            'artisans'            => $artisanRepository->getAll(),
            'activeArtisansCount' => $artisanRepository->countActive(),
            'makerIdsMap'         => $makerIdRepository->getOldToNewMakerIdsMap(),
            'countryCount'        => $artisanRepository->getDistinctCountriesCount(),
            'filters'             => $filterService->getFiltersTplData(),
            'species'             => $species->getSpeciesTree(),
        ]);

        self::setExpires($response);

        return $response;
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
