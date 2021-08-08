<?php

declare(strict_types=1);

namespace App\Service\Statistics;

use App\Repository\ArtisanRepository;
use App\Repository\ArtisanVolatileDataRepository;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use Doctrine\ORM\UnexpectedResultException;

class StatisticsService
{
    public function __construct(
        private ArtisanRepository $artisanRepository,
        private ArtisanVolatileDataRepository $avdRepository,
    ) {
    }

    public function getMainPageStats(): MainPageStats
    {
        try {
            $lastSystemUpdateTimeUtc = $this->avdRepository->getLastCsUpdateTime();
        } catch (UnexpectedResultException|DateTimeException) {
            $lastSystemUpdateTimeUtc = null;
        }

        try {
            $activeArtisansCount = $this->artisanRepository->countActive();
        } catch (UnexpectedResultException) {
            $activeArtisansCount = null;
        }

        try {
            $countryCount = $this->artisanRepository->getDistinctCountriesCount();
        } catch (UnexpectedResultException) {
            $countryCount = null;
        }

        try {
            $lastDataUpdateTimeUtc = DateTimeUtils::getUtcAt(shell_exec('TZ=UTC git log -n1 --format=%cd --date=local'));
        } catch (DateTimeException) {
            $lastDataUpdateTimeUtc = null;
        }

        return new MainPageStats(
            $activeArtisansCount,
            $countryCount,
            $lastDataUpdateTimeUtc, // TODO: CS&BP? See #29
            $lastSystemUpdateTimeUtc,
        );
    }
}
