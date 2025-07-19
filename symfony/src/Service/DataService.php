<?php

declare(strict_types=1);

namespace App\Service;

use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\Fields\Fields;
use App\Entity\Creator as CreatorE;
use App\Repository\CreatorOfferStatusRepository;
use App\Repository\CreatorRepository;
use App\Repository\CreatorValueRepository;
use App\Repository\CreatorVolatileDataRepository;
use App\Repository\EventRepository;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\DateTimeException;
use App\Utils\Json;
use App\ValueObject\CacheTags;
use App\ValueObject\MainPageStats;
use DateTimeImmutable;
use Doctrine\ORM\UnexpectedResultException;
use Psr\Log\LoggerInterface;
use Veelkoov\Debris\Base\DList;
use Veelkoov\Debris\Maps\StringToInt;
use Veelkoov\Debris\StringSet;

class DataService
{
    public function __construct(
        private readonly CreatorRepository $creatorRepository,
        private readonly CreatorValueRepository $creatorValueRepository,
        private readonly CreatorVolatileDataRepository $avdRepository,
        private readonly CreatorOfferStatusRepository $cosRepository,
        private readonly EventRepository $eventRepository,
        private readonly Cache $cache,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getMainPageStats(): MainPageStats
    {
        return $this->cache->get(
            function () {
                try {
                    $lastDataUpdateTimeUtc = $this->avdRepository->getLastCsUpdateTime();
                } catch (UnexpectedResultException|DateTimeException $exception) {
                    $this->logger->error("Failed getting last CS update time: $exception");
                    $lastDataUpdateTimeUtc = null;
                }

                $activeCreatorsCount = $this->countActiveCreators();

                try {
                    $countryCount = $this->creatorRepository->getDistinctCountriesCount();
                } catch (UnexpectedResultException) {
                    $countryCount = null;
                }

                return new MainPageStats(
                    $activeCreatorsCount,
                    $countryCount,
                    $lastDataUpdateTimeUtc,
                );
            },
            [CacheTags::CREATORS, CacheTags::TRACKING],
            __METHOD__,
        );
    }

    public function countActiveCreators(): int
    {
        return $this->cache->get(fn () => $this->creatorRepository->countActive(), CacheTags::CREATORS, __METHOD__);
    }

    public function getCountries(): StringSet
    {
        return $this->cache->get(fn () => $this->creatorRepository->getDistinctCountries(), CacheTags::CREATORS, __METHOD__);
    }

    public function getStates(): StringSet
    {
        return $this->cache->get(fn () => $this->creatorRepository->getDistinctStates(), CacheTags::CREATORS, __METHOD__);
    }

    public function getOpenFor(): StringSet
    {
        return $this->cache->get(fn () => $this->cosRepository->getDistinctOpenFor(), [CacheTags::CREATORS, CacheTags::TRACKING], __METHOD__);
    }

    public function getLanguages(): StringSet
    {
        return $this->cache->get(fn () => $this->creatorValueRepository->getDistinctValues(Field::LANGUAGES->value),
            CacheTags::CREATORS, __METHOD__);
    }

    public function countActiveCreatorsHavingAnyOf(Field ...$fields): int
    {
        return $this->cache->get(
            fn () => $this->creatorValueRepository->countActiveCreatorsHavingAnyOf(Field::strings($fields)),
            CacheTags::CREATORS,
            [__METHOD__, ...$fields],
        );
    }

    public function countDistinctInActiveCreatorsHaving(Field $field): StringToInt
    {
        return $this->cache->get(
            function () use ($field) {
                if (Field::COUNTRY === $field || Field::STATE === $field) {
                    return $this->creatorRepository->countDistinctInActiveCreators(strtolower($field->value))->freeze();
                } else {
                    return $this->creatorValueRepository->countDistinctInActiveCreatorsHaving($field->value)->freeze();
                }
            },
            CacheTags::CREATORS,
            [__METHOD__, $field],
        );
    }

    public function getCompletenessStats(): StringToInt
    {
        return $this->cache->get(function (): StringToInt {
            $completeness = DList::mapFrom($this->creatorRepository->getActivePaged(),
                static fn (CreatorE $creator) => Creator::wrap($creator)->getCompleteness());

            $levels = ['100%' => 100, '90-99%' => 90, '80-89%' => 80, '70-79%' => 70, '60-69%' => 60, '50-59%' => 50,
                '40-49%' => 40, '30-39%' => 30, '20-29%' => 20, '10-19%' => 10, '0-9%' => 0];

            $result = new StringToInt();

            foreach ($levels as $description => $level) {
                $result->set($description, $completeness->filter(static fn (int $percent) => $percent >= $level)->count());

                $completeness = $completeness->filter(static fn (int $percent) => $percent < $level);
            }

            return $result->freeze();
        }, CacheTags::CREATORS, __METHOD__);
    }

    /**
     * @see SmartAccessDecorator::getLastCreatorId()
     */
    public function getProvidedInfoStats(): StringToInt
    {
        return $this->cache->get(function (): StringToInt {
            $result = StringToInt::fromKeys(Fields::inStats()->names(), fn () => 0);

            foreach ($this->creatorRepository->getActivePaged() as $creatorE) {
                $creator = Creator::wrap($creatorE);

                foreach (Fields::inStats() as $field) {
                    if (Field::FORMER_MAKER_IDS === $field) {
                        /* Some creators were added before introduction of the creators IDs. They were assigned
                         * fake ("mock") former IDs, so we can rely on SmartAccessDecorator::getLastCreatorId() etc.
                         * Those IDs are "M000000", part where the digits is zero-padded creator database ID. */

                        $placeholder = sprintf('M%06d', $creator->getId());

                        if ($creator->get($field) === [$placeholder]) {
                            continue; // Fake former creator ID - don't add to the result
                        }
                    }

                    if ($field->providedIn($creator)) {
                        $result->set($field->value, $result->get($field->value) + 1);
                    }
                }
            }

            return $result->sorted(reverse: true)->freeze();
        }, CacheTags::CREATORS, __METHOD__);
    }

    public function getOfferStatusStats(): StringToInt
    {
        return $this->cache->get(function (): StringToInt {
            $stats = $this->cosRepository->getOfferStatusStats();

            return new StringToInt([
                'Open for anything'              => $stats['open_for_anything'],
                'Closed for anything'            => $stats['closed_for_anything'],
                'Status successfully tracked'    => $stats['successfully_tracked'],
                'Partially successfully tracked' => $stats['partially_tracked'],
                'Tracking failed completely'     => $stats['tracking_failed'],
                'Tracking issues'                => $stats['tracking_issues'],
                'Status tracked'                 => $stats['tracked'],
                'Total'                          => $stats['total'],
            ])->freeze();
        }, CacheTags::TRACKING, __METHOD__);
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
        }, CacheTags::CREATORS, __METHOD__);
    }

    public function getLatestEventTimestamp(): ?DateTimeImmutable
    {
        return $this->cache->get(fn () => $this->eventRepository->getLatestEventTimestamp(),
            [CacheTags::CREATORS, CacheTags::TRACKING], __METHOD__); // TODO: https://github.com/veelkoov/fuzzrake/issues/251);
    }
}
