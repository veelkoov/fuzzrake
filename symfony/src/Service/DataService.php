<?php

declare(strict_types=1);

namespace App\Service;

use App\Data\Definitions\Fields\Field;
use App\Repository\ArtisanRepository as CreatorRepository;
use App\Repository\ArtisanValueRepository as CreatorValueRepository;
use App\Repository\ArtisanVolatileDataRepository;
use App\Repository\CreatorOfferStatusRepository;
use App\Repository\KotlinDataRepository;
use App\Utils\DateTime\DateTimeException;
use App\ValueObject\CacheTags;
use App\ValueObject\MainPageStats;
use Doctrine\ORM\UnexpectedResultException;

class DataService
{
    public function __construct(
        private readonly CreatorRepository $creatorRepository,
        private readonly CreatorValueRepository $creatorValueRepository,
        private readonly ArtisanVolatileDataRepository $avdRepository,
        private readonly CreatorOfferStatusRepository $cosRepository,
        private readonly KotlinDataRepository $kotlinDataRepository,
        private readonly Cache $cache,
    ) {
    }

    public function getMainPageStats(): MainPageStats
    {
        return $this->cache->get(
            function () {
                try {
                    $lastDataUpdateTimeUtc = $this->avdRepository->getLastCsUpdateTime();
                } catch (UnexpectedResultException|DateTimeException) {
                    $lastDataUpdateTimeUtc = null;
                }

                $activeArtisansCount = $this->countActiveCreators();

                try {
                    $allArtisansCount = $this->creatorRepository->countAll();
                } catch (UnexpectedResultException) {
                    $allArtisansCount = null;
                }

                try {
                    $countryCount = $this->creatorRepository->getDistinctCountriesCount();
                } catch (UnexpectedResultException) {
                    $countryCount = null;
                }

                return new MainPageStats(
                    $allArtisansCount,
                    $activeArtisansCount,
                    $countryCount,
                    $lastDataUpdateTimeUtc,
                );
            },
            [CacheTags::ARTISANS, CacheTags::TRACKING],
            __METHOD__,
        );
    }

    public function countActiveCreators(): int
    {
        return $this->cache->get(fn () => $this->creatorRepository->countActive(), CacheTags::ARTISANS, __METHOD__);
    }

    /**
     * @return list<string>
     */
    public function getCountries(): array
    {
        return $this->cache->get(fn () => $this->creatorRepository->getDistinctCountries(), CacheTags::ARTISANS, __METHOD__);
    }

    /**
     * @return list<string>
     */
    public function getStates(): array
    {
        return $this->cache->get(fn () => $this->creatorRepository->getDistinctStates(), CacheTags::ARTISANS, __METHOD__);
    }

    /**
     * @return list<string>
     */
    public function getOpenFor(): array
    {
        return $this->cache->get(fn () => $this->cosRepository->getDistinctOpenFor(), [CacheTags::ARTISANS, CacheTags::TRACKING], __METHOD__);
    }

    /**
     * @return list<string>
     */
    public function getLanguages(): array
    {
        return $this->cache->get(fn () => $this->creatorValueRepository->getDistinctValues(Field::LANGUAGES->value),
            CacheTags::ARTISANS, __METHOD__);
    }

    public function getOooNotice(): string
    {
        return $this->kotlinDataRepository->getString(KotlinDataRepository::OOO_NOTICE);
    }

    public function countActiveCreatorsHavingAnyOf(Field ...$fields): int
    {
        return $this->cache->get(
            fn () => $this->creatorValueRepository->countActiveCreatorsHavingAnyOf(Field::strings($fields)),
            CacheTags::ARTISANS,
            [__METHOD__, ...$fields],
        );
    }

    /**
     * @return array<string, int>
     */
    public function countDistinctInActiveCreatorsHaving(Field $field): array
    {
        return $this->cache->get(
            function () use ($field) {
                if (Field::COUNTRY === $field || Field::STATE === $field) {
                    return $this->creatorRepository->countDistinctInActiveCreators(strtolower($field->value));
                } else {
                    return $this->creatorValueRepository->countDistinctInActiveCreatorsHaving($field->value);
                }
            },
            CacheTags::ARTISANS,
            [__METHOD__, $field],
        );
    }
}
