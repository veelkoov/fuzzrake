<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests;

use App\Data\Definitions\Fields\Field;
use App\Entity\Artisan;
use App\Entity\ArtisanUrl;
use App\Entity\CreatorOfferStatus;
use App\Entity\CreatorSpecie;
use App\Filtering\DataRequests\Filters\SpecialItemsExtractor;
use App\Utils\Arrays\Arrays;
use App\Utils\Pagination\Pagination;
use App\Utils\StrUtils;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use Psl\Vec;
use Veelkoov\Debris\StringList;

class QueryChoicesAppender
{
    private int $uniqueAliasIndex = 1;

    public function __construct(
        private readonly Choices $choices,
    ) {
    }

    public function applyChoices(QueryBuilder $builder): void
    {
        $this->applyTextSearch($builder); // Text search should work in the maker mode

        if ($this->choices->creatorMode) {
            return; // Just return everything
        }

        $this->applyMakerId($builder);
        $this->applyCountries($builder);
        $this->applyStates($builder);
        $this->applyOpenFor($builder);
        $this->applyPaymentPlans($builder);
        $this->applySpecies($builder);
        $this->applyWantsSfw($builder);
        $this->applyWorksWithMinors($builder);
        $this->applyWantsInactive($builder);
        $this->applyCreatorValuesCount($builder, StringList::from($this->choices->productionModels), Field::PRODUCTION_MODELS);
        $this->applyCreatorValuesCount($builder, StringList::from($this->choices->styles), Field::STYLES, Field::OTHER_STYLES);
        $this->applyCreatorValuesCount($builder, StringList::from($this->choices->orderTypes), Field::ORDER_TYPES, Field::OTHER_ORDER_TYPES);
        $this->applyCreatorValuesCount($builder, StringList::from($this->choices->features), Field::FEATURES, Field::OTHER_FEATURES, true);
        $this->applyCreatorValuesCount($builder, StringList::from($this->choices->languages), Field::LANGUAGES);
    }

    public function applyPaging(Query $query): void // @phpstan-ignore missingType.generics
    {
        $query
            ->setFirstResult(Pagination::getFirstIdx($this->choices->pageSize, $this->choices->pageNumber))
            ->setMaxResults($this->choices->pageSize)
        ;
    }

    private function createSubqueryBuilder(QueryBuilder $builder, string $alias): QueryBuilder
    {
        return $builder->getEntityManager()->getRepository(Artisan::class)->createQueryBuilder($alias);
    }

    private function applyMakerId(QueryBuilder $builder): void // TODO: Test https://github.com/veelkoov/fuzzrake/issues/183
    {
        if ('' !== $this->choices->makerId) {
            $builder->andWhere($builder->expr()->exists(
                $this->createSubqueryBuilder($builder, 'a4')
                    ->select('1')
                    ->join('a4.makerIds', 'a4mi')
                    ->where('a4.id = a.id')
                    ->andWhere('a4mi.makerId = :makerId')
            ))
                ->setParameter('makerId', $this->choices->makerId);
        }
    }

    private function applyTextSearch(QueryBuilder $builder): void
    {
        // FIXME: Allow searching literal _ and % (do not allow wildcards).
        //        See https://github.com/veelkoov/fuzzrake/issues/232
        // Assumption: we are using UTF-8, where replacing ASCII is safe.
        $searchedText = str_replace(['_', '%'], '', $this->choices->textSearch);

        if ('' === $searchedText) {
            return;
        }

        $searchedText = '%'.mb_strtoupper($searchedText).'%';

        $builder->andWhere($builder->expr()->orX(
            'UPPER(a.name) LIKE :searchedText',
            'UPPER(a.formerly) LIKE :searchedText',
            $builder->expr()->exists(
                $this->createSubqueryBuilder($builder, 'a5')
                ->select('1')
                ->join('a5.makerIds', 'a5mi1')
                ->where('a5.id = a.id')
                ->andWhere('a5mi1.makerId LIKE :searchedText')
            ),
        ))
            ->setParameter('searchedText', $searchedText);
    }

    private function applyCountries(QueryBuilder $builder): void
    {
        if ($this->choices->countries->isNotEmpty()) {
            $countries = Vec\map($this->choices->countries,
                fn ($value) => Consts::FILTER_VALUE_UNKNOWN === $value ? Consts::DATA_VALUE_UNKNOWN : $value);

            $builder->andWhere('a.country IN (:countries)')->setParameter('countries', $countries);
        }
    }

    private function applyStates(QueryBuilder $builder): void
    {
        if ($this->choices->states->isNotEmpty()) {
            $states = Vec\map($this->choices->states,
                fn ($value) => Consts::FILTER_VALUE_UNKNOWN === $value ? Consts::DATA_VALUE_UNKNOWN : $value);

            $builder->andWhere('a.state IN (:states)')->setParameter('states', $states);
        }
    }

    private function applyWantsSfw(QueryBuilder $builder): void
    {
        if (true !== $this->choices->isAdult || false !== $this->choices->wantsSfw) {
            $builder->andWhere($builder->expr()->exists(
                $this->createSubqueryBuilder($builder, 'a3')
                    ->select('1')
                    ->join('a3.values', 'a3v1')
                    ->join('a3.values', 'a3v2')
                    ->where('a3.id = a.id')
                    ->andWhere('a3v1.fieldName = :nsfwWebsite')
                    ->andWhere('a3v1.value = :a3vFalse')
                    ->andWhere('a3v2.fieldName = :nsfwSocial')
                    ->andWhere('a3v2.value = :a3vFalse')
            ))
                ->setParameter('nsfwWebsite', Field::NSFW_WEBSITE->value)
                ->setParameter('nsfwSocial', Field::NSFW_SOCIAL->value)
                ->setParameter('a3vFalse', StrUtils::asStr(false));
        }
    }

    private function applyWorksWithMinors(QueryBuilder $builder): void
    {
        if (true !== $this->choices->isAdult) {
            $builder->andWhere($builder->expr()->exists(
                $this->createSubqueryBuilder($builder, 'a2')
                    ->select('1')
                    ->join('a2.values', 'a2v')
                    ->where('a2.id = a.id')
                    ->andWhere('a2v.fieldName = :wwmFieldName')
                    ->andWhere('a2v.value = :a2vTrue')
            ))
                ->setParameter('wwmFieldName', Field::WORKS_WITH_MINORS->value)
                ->setParameter('a2vTrue', StrUtils::asStr(true));
        }
    }

    private function applyPaymentPlans(QueryBuilder $builder): void
    {
        if ($this->choices->wantsUnknownPaymentPlans) {
            if ($this->choices->wantsAnyPaymentPlans) {
                if ($this->choices->wantsNoPaymentPlans) {
                    // Unknown + ANY + None
                    return;
                } else {
                    // Unknown + ANY
                    $andWhere = 'a.paymentPlans <> :paymentPlans';
                    $parameter = Consts::DATA_PAYPLANS_NONE;
                }
            } else {
                if ($this->choices->wantsNoPaymentPlans) {
                    // Unknown + None
                    $andWhere = 'a.paymentPlans IN (:paymentPlans)';
                    $parameter = [Consts::DATA_VALUE_UNKNOWN, Consts::DATA_PAYPLANS_NONE];
                } else {
                    // Unknown
                    $andWhere = 'a.paymentPlans = :paymentPlans';
                    $parameter = Consts::DATA_VALUE_UNKNOWN;
                }
            }
        } else {
            if ($this->choices->wantsAnyPaymentPlans) {
                if ($this->choices->wantsNoPaymentPlans) {
                    // ANY + None
                    $andWhere = 'a.paymentPlans <> :paymentPlans';
                    $parameter = Consts::DATA_VALUE_UNKNOWN;
                } else {
                    // ANY
                    $andWhere = 'a.paymentPlans NOT IN (:paymentPlans)';
                    $parameter = [Consts::DATA_PAYPLANS_NONE, Consts::DATA_VALUE_UNKNOWN];
                }
            } else {
                if ($this->choices->wantsNoPaymentPlans) {
                    // None
                    $andWhere = 'a.paymentPlans = :paymentPlans';
                    $parameter = Consts::DATA_PAYPLANS_NONE;
                } else {
                    // Nothing selected
                    return;
                }
            }
        }

        $builder
            ->andWhere($andWhere)
            ->setParameter('paymentPlans', $parameter);
    }

    private function applyWantsInactive(QueryBuilder $builder): void
    {
        if (!$this->choices->wantsInactive) {
            $builder
                ->andWhere('a.inactiveReason = :emptyInactiveReason')
                ->setParameter('emptyInactiveReason', '');
        }
    }

    private function applySpecies(QueryBuilder $builder): void
    {
        if ($this->choices->species->isEmpty()) {
            return;
        }

        $conditions = [];

        $items = new SpecialItemsExtractor(StringList::from($this->choices->species), Consts::FILTER_VALUE_UNKNOWN);

        if ($items->hasSpecial(Consts::FILTER_VALUE_UNKNOWN)) {
            $conditions[] = $builder->expr()->not($builder->expr()->exists(
                $builder->getEntityManager()
                    ->getRepository(CreatorSpecie::class)
                    ->createQueryBuilder('cs1')
                    ->select('1')
                    ->join('cs1.specie', 'sp1')
                    ->where('cs1.creator = a')
            ));
        }

        if ($items->common->isNotEmpty()) {
            $conditions[] = $builder->expr()->exists(
                $builder->getEntityManager()
                    ->getRepository(CreatorSpecie::class)
                    ->createQueryBuilder('cs2')
                    ->select('1')
                    ->join('cs2.specie', 'sp2')
                    ->where('sp2.name IN (:specieNames)')
                    ->andWhere('cs2.creator = a')
            );

            $builder->setParameter('specieNames', $items->common);
        }

        $this->addWheres($builder, $conditions);
    }

    private function applyOpenFor(QueryBuilder $builder): void
    {
        $conditions = [];

        $items = new SpecialItemsExtractor(StringList::from($this->choices->openFor),
            Consts::FILTER_VALUE_TRACKING_ISSUES, Consts::FILTER_VALUE_NOT_TRACKED);

        if ($items->hasSpecial(Consts::FILTER_VALUE_TRACKING_ISSUES)) {
            $conditions[] = $builder->expr()->eq('vd.csTrackerIssue', ':hasCsTrackerIssue');

            $builder->setParameter('hasCsTrackerIssue', true, ParameterType::BOOLEAN);
        }

        if ($items->hasSpecial(Consts::FILTER_VALUE_NOT_TRACKED)) {
            $conditions[] = $builder->expr()->not($builder->expr()->exists(
                $builder->getEntityManager()
                    ->getRepository(ArtisanUrl::class)
                    ->createQueryBuilder('au1')
                    ->select('1')
                    ->where('au1.artisan = a')
                    ->andWhere('au1.type = :urlTypeCommissions')
            ));

            $builder->setParameter('urlTypeCommissions', Field::URL_COMMISSIONS->value);
        }

        if ($items->common->isNotEmpty()) {
            $conditions[] = $builder->expr()->exists(
                $builder->getEntityManager()
                    ->getRepository(CreatorOfferStatus::class)
                    ->createQueryBuilder('cos1')
                    ->select('1')
                    ->where('cos1.isOpen = :isOpen')
                    ->andWhere('cos1.offer IN (:openForOffers)')
                    ->andWhere('cos1.artisan = a')
            );

            $builder
                ->setParameter('openForOffers', $items->common)
                ->setParameter('isOpen', true, ParameterType::BOOLEAN);
        }

        $this->addWheres($builder, $conditions);
    }

    /**
     * @param list<Func|Comparison> $conditions
     */
    private function addWheres(QueryBuilder $builder, array $conditions): void
    {
        if ([] === $conditions) {
            return;
        } elseif (1 === count($conditions)) {
            $condition = Arrays::single($conditions);
        } else {
            $condition = $builder->expr()->orX(...$conditions);
        }

        $builder->andWhere($condition);
    }

    private function applyCreatorValuesCount(QueryBuilder $builder, StringList $selectedItems, Field $primaryField,
        ?Field $otherField = null, bool $allInsteadOfAny = false): void
    {
        $conditions = [];

        $items = new SpecialItemsExtractor($selectedItems, Consts::FILTER_VALUE_OTHER, Consts::FILTER_VALUE_UNKNOWN);

        if ($items->hasSpecial(Consts::FILTER_VALUE_OTHER)) {
            if (null === $otherField) {
                throw new InvalidArgumentException('Other field not selected');
            }

            $creatorAlias = $this->getUniqueAlias();
            $valuesAlias = $this->getUniqueAlias();
            $parameterAlias = $this->getUniqueAlias();

            $conditions[] = $builder->expr()->exists(
                $this->createSubqueryBuilder($builder, $creatorAlias)
                    ->select('1')
                    ->join("$creatorAlias.values", $valuesAlias)
                    ->where("$creatorAlias.id = a.id")
                    ->andWhere("$valuesAlias.fieldName = :$parameterAlias")
            );

            $builder->setParameter($parameterAlias, $otherField->value);
        }

        if ($items->hasSpecial(Consts::FILTER_VALUE_UNKNOWN)) {
            $creatorAlias = $this->getUniqueAlias();
            $valuesAlias = $this->getUniqueAlias();
            $parameterAlias = $this->getUniqueAlias();

            $conditions[] = $builder->expr()->not($builder->expr()->exists(
                $this->createSubqueryBuilder($builder, $creatorAlias)
                    ->select('1')
                    ->join("$creatorAlias.values", $valuesAlias)
                    ->where("$creatorAlias.id = a.id")
                    ->andWhere("$valuesAlias.fieldName IN (:$parameterAlias)")
            ));

            $builder->setParameter($parameterAlias, Vec\filter(
                [$primaryField->value, $otherField?->value],
                fn (?string $name): bool => null !== $name,
            ));
        }

        if ($items->common->isNotEmpty()) {
            $creatorAlias = $this->getUniqueAlias();
            $valuesAlias = $this->getUniqueAlias();
            $fieldNameParamAlias = $this->getUniqueAlias();
            $valueParamAlias = $this->getUniqueAlias();

            $having = $allInsteadOfAny
                ? $builder->expr()->eq("COUNT($creatorAlias)", $items->common->count())
                : $builder->expr()->gt("COUNT($creatorAlias)", 0);

            $conditions[] = $builder->expr()->exists(
                $this->createSubqueryBuilder($builder, $creatorAlias)
                ->select('1')
                ->join("$creatorAlias.values", $valuesAlias)
                ->where("$creatorAlias.id = a.id")
                ->andWhere("$valuesAlias.fieldName = :$fieldNameParamAlias")
                ->andWhere("$valuesAlias.value IN (:$valueParamAlias)")
                ->groupBy("$creatorAlias.id")
                ->having($having)
            );

            $builder->setParameter($fieldNameParamAlias, $primaryField->value);
            $builder->setParameter($valueParamAlias, $items->common);
        }

        $this->addWheres($builder, $conditions);
    }

    private function getUniqueAlias(): string
    {
        return 'qca'.((string) $this->uniqueAliasIndex++);
    }
}
