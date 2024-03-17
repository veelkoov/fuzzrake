<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ArtisanRepository;
use App\Repository\ArtisanVolatileDataRepository;
use App\Repository\CreatorOfferStatusRepository;
use App\Repository\KotlinDataRepository;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\DateTime\DateTimeException;
use App\Utils\StringList;
use App\ValueObject\CacheTags;
use App\ValueObject\MainPageStats;
use Doctrine\ORM\UnexpectedResultException;

class DataService
{
    public function __construct(
        private readonly ArtisanRepository $artisanRepository,
        private readonly ArtisanVolatileDataRepository $avdRepository,
        private readonly CreatorOfferStatusRepository $cosRepository,
        private readonly KotlinDataRepository $kotlinDataRepository,
        private readonly Cache $cache,
    ) {
    }

    public function getMainPageStats(): MainPageStats
    {
        return $this->cache->getCached('DataService.getMainPageStats', [CacheTags::ARTISANS, CacheTags::CODE, CacheTags::TRACKING],
            function () {
                try {
                    $lastDataUpdateTimeUtc = $this->avdRepository->getLastCsUpdateTime();
                } catch (UnexpectedResultException|DateTimeException) {
                    $lastDataUpdateTimeUtc = null;
                }

                try {
                    $activeArtisansCount = $this->artisanRepository->countActive();
                } catch (UnexpectedResultException) {
                    $activeArtisansCount = null;
                }

                try {
                    $allArtisansCount = $this->artisanRepository->countAll();
                } catch (UnexpectedResultException) {
                    $allArtisansCount = null;
                }

                try {
                    $countryCount = $this->artisanRepository->getDistinctCountriesCount();
                } catch (UnexpectedResultException) {
                    $countryCount = null;
                }

                return new MainPageStats(
                    $allArtisansCount,
                    $activeArtisansCount,
                    $countryCount,
                    $lastDataUpdateTimeUtc,
                );
            }
        );
    }

    /**
     * @return list<string>
     */
    public function getCountries(): array
    {
        return $this->cache->getCached('DataService.getCountries', CacheTags::ARTISANS,
            fn () => $this->artisanRepository->getDistinctCountries());
    }

    /**
     * @return list<string>
     */
    public function getStates(): array
    {
        return $this->cache->getCached('DataService.getStates', CacheTags::ARTISANS,
            fn () => $this->artisanRepository->getDistinctStates());
    }

    /**
     * @return list<string>
     */
    public function getOpenFor(): array
    {
        return $this->cache->getCached('DataService.getOpenFor', [CacheTags::ARTISANS, CacheTags::TRACKING],
            fn () => $this->cosRepository->getDistinctOpenFor());
    }

    /**
     * @return list<string>
     */
    public function getLanguages(): array
    {
        return $this->cache->getCached('DataService.getLanguages', [CacheTags::ARTISANS, CacheTags::TRACKING],
            function () {
                $result = [];

                foreach ($this->artisanRepository->getDistinctLanguages() as $languages) {
                    $result = [...$result, ...StringList::unpack($languages)];
                }

                return array_unique($result);
            }
        );
    }

    /**
     * @return list<Artisan>
     */
    public function getAllArtisans(): array
    {
        return $this->cache->getCached('DataService.getAllArtisans', CacheTags::ARTISANS,
            fn () => Artisan::wrapAll($this->artisanRepository->getAll()));
    }

    public function getOooNotice(): string
    {
        return $this->kotlinDataRepository->getString(KotlinDataRepository::OOO_NOTICE);
    }
}
