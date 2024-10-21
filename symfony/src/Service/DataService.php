<?php

declare(strict_types=1);

namespace App\Service;

use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\Fields\Fields;
use App\Entity\Artisan as CreatorE;
use App\Repository\ArtisanRepository as CreatorRepository;
use App\Repository\ArtisanValueRepository as CreatorValueRepository;
use App\Repository\ArtisanVolatileDataRepository;
use App\Repository\CreatorOfferStatusRepository;
use App\Repository\EventRepository;
use App\Repository\KotlinDataRepository;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use App\Utils\DateTime\DateTimeException;
use App\Utils\Json;
use App\ValueObject\CacheTags;
use App\ValueObject\MainPageStats;
use DateTimeImmutable;
use Doctrine\ORM\UnexpectedResultException;
use Psl\Dict;
use Psl\Vec;

class DataService
{
    public function __construct(
        private readonly CreatorRepository $creatorRepository,
        private readonly CreatorValueRepository $creatorValueRepository,
        private readonly ArtisanVolatileDataRepository $avdRepository,
        private readonly CreatorOfferStatusRepository $cosRepository,
        private readonly EventRepository $eventRepository,
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

    /**
     * @return array<string, int>
     */
    public function getCompletenessStats(): array
    {
        return $this->cache->get(function (): array {
            $completeness = Vec\map($this->creatorRepository->getActivePaged(),
                fn (CreatorE $creator) => Creator::wrap($creator)->getCompleteness());

            $levels = ['100%' => 100, '90-99%' => 90, '80-89%' => 80, '70-79%' => 70, '60-69%' => 60, '50-59%' => 50,
                '40-49%' => 40, '30-39%' => 30, '20-29%' => 20, '10-19%' => 10, '0-9%' => 0, ];

            $result = [];

            foreach ($levels as $description => $level) {
                $result[$description] = count(array_filter($completeness, fn (int $percent) => $percent >= $level));

                $completeness = array_filter($completeness, fn (int $percent) => $percent < $level);
            }

            return $result;
        }, CacheTags::ARTISANS, __METHOD__);
    }

    /**
     * @return array<string, int>
     *
     * @see SmartAccessDecorator::getLastMakerId()
     */
    public function getProvidedInfoStats(): array
    {
        return $this->cache->get(function (): array {
            $result = Dict\from_keys(
                Vec\map(Fields::inStats(), fn (Field $field): string => $field->value),
                fn (): int => 0,
            );

            foreach ($this->creatorRepository->getActivePaged() as $creatorE) {
                $creator = Creator::wrap($creatorE);

                foreach (Fields::inStats() as $field) {
                    if (Field::FORMER_MAKER_IDS === $field) {
                        /* Some makers were added before introduction of the maker IDs. They were assigned fake former IDs,
                         * so we can rely on SmartAccessDecorator::getLastMakerId() etc. Those IDs are "M000000", part
                         * where the digits is zero-padded artisan database ID. */

                        $placeholder = sprintf('M%06d', $creator->getId());

                        if ($creator->get($field) === [$placeholder]) {
                            continue; // Fake former maker ID - don't add to the result
                        }
                    }

                    if ($field->providedIn($creator)) {
                        ++$result[$field->value];
                    }
                }
            }

            arsort($result);

            return $result;
        }, CacheTags::ARTISANS, __METHOD__);
    }

    public function getCreatorsPublicDataJsonString(): string
    {
        return $this->cache->get(function (): string {
            $result = '[';
            $empty = true;

            foreach ($this->creatorRepository->getAllPaged() as $creatorE) {
                if ($empty) {
                    $empty = false;
                } else {
                    $result .= ',';
                }

                $result .= Json::encode(Creator::wrap($creatorE));
            }

            $result .= ']';

            return $result;
        }, CacheTags::ARTISANS, __METHOD__);
    }

    public function getLatestEventTimestamp(): ?DateTimeImmutable
    {
        return $this->cache->get(fn () => $this->eventRepository->getLatestEventTimestamp(),
            [CacheTags::ARTISANS, CacheTags::TRACKING], __METHOD__); // TODO: https://github.com/veelkoov/fuzzrake/issues/251);
    }
}
