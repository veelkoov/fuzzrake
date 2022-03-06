<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArtisanRepository;
use App\Repository\MakerIdRepository;
use App\Service\FilterService;
use App\Service\Statistics\StatisticsService;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\Species\Species;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\UnexpectedResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function main(ArtisanRepository $artisanRepository, MakerIdRepository $makerIdRepository, FilterService $filterService, Species $species, StatisticsService $statistics): Response
    {
        $response = $this->render('main/main.html.twig', [
            'artisans'            => Artisan::wrapAll($artisanRepository->getAll()),
            'makerIdsMap'         => $makerIdRepository->getOldToNewMakerIdsMap(),
            'stats'               => $statistics->getMainPageStats(),
            'filters'             => $filterService->getFiltersTplData(),
            'species'             => $species->getTree(),
        ]);

        self::setExpires($response);

        return $response;
    }

    /**
     * @throws DateTimeException
     */
    #[Route(path: '/new.html', name: RouteName::NEW_ARTISANS)]
    #[Cache(public: true)]
    public function newArtisans(ArtisanRepository $artisanRepository): Response
    {
        $response = $this->render('main/new.html.twig', [
            'artisans' => Artisan::wrapAll($artisanRepository->getNew()),
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

        $nextExpiresCandidates = [ // TODO: Move to configuration, make somehow connected to the Cron job; make safer assumptions (e.g. max 4-6h)? grep-tracking-frequency
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
