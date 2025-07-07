<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests;

use App\Data\Definitions\Fields\Field;
use App\Entity\Creator;
use App\Entity\CreatorOfferStatus;
use App\Entity\CreatorSpecie;
use App\Entity\CreatorUrl;
use App\Filtering\DataRequests\Filters\SpecialItemsExtractor;
use App\Utils\Collections\Arrays;
use App\Utils\Pagination\Pagination;
use App\Utils\StrUtils;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use Psl\Vec;
use Veelkoov\Debris\StringSet;

class QueryChoicesAppender
{
    private int $uniqueIdIndex = 1;

    public function __construct(
        private readonly Choices $choices,
    ) {
    }

    public function applyChoices(QueryBuilder $builder): void
    {
        $this->applyFilters($builder);
        $this->applyOrder($builder);
        $this->applyPaging($builder);
    }

    private function applyFilters(QueryBuilder $builder): void
    {
        $this->applyTextSearch($builder); // Text search should work in the creator mode

        if ($this->choices->creatorMode) {
            return; // Just return everything
        }

        $this->applyCreatorId($builder);
        $this->applyCountries($builder);
        $this->applyStates($builder);
        $this->applyOpenFor($builder);
        $this->applyPaymentPlans($builder);
        $this->applySpecies($builder);
        $this->applyWantsSfw($builder);
        $this->applyWorksWithMinors($builder);
        $this->applyWantsInactive($builder);
        $this->applyCreatorValuesCount($builder, $this->choices->productionModels, Field::PRODUCTION_MODELS);
        $this->applyCreatorValuesCount($builder, $this->choices->styles, Field::STYLES, Field::OTHER_STYLES);
        $this->applyCreatorValuesCount($builder, $this->choices->orderTypes, Field::ORDER_TYPES, Field::OTHER_ORDER_TYPES);
        $this->applyCreatorValuesCount($builder, $this->choices->features, Field::FEATURES, Field::OTHER_FEATURES, true);
        $this->applyCreatorValuesCount($builder, $this->choices->languages, Field::LANGUAGES);
    }

    private function applyOrder(QueryBuilder $builder): void
    {
        $addedDateTime = $this->getUniqueId();
        $updatedDateTime = $this->getUniqueId();
        $addedDateTimeValue = $this->getUniqueId();
        $updatedDateTimeValue = $this->getUniqueId();
        $beforeDateTimesValue = $this->getUniqueId();

        $builder
            // Retrieve datetime added for sorting by the last update time
            ->leftJoin('d_c.values', $addedDateTime, Join::WITH,
                "$addedDateTime.creator = d_c AND $addedDateTime.fieldName = :$addedDateTimeValue")
            ->setParameter($addedDateTimeValue, Field::DATE_ADDED->value)

            // Retrieve datetime updated for sorting by the last update time
            ->leftJoin('d_c.values', $updatedDateTime, Join::WITH,
                "$updatedDateTime.creator = d_c AND $updatedDateTime.fieldName = :$updatedDateTimeValue")
            ->setParameter($updatedDateTimeValue, Field::DATE_UPDATED->value)

            // FIXME: https://github.com/doctrine/orm/issues/5905 grep-code-cannot-use-coalesce-in-doctrine-order-by
            // Unable to sort despite EBNF/OrderByItem/ScalarExpression/CaseExpression/CoalesceExpression
            // https://www.doctrine-project.org/projects/doctrine-orm/en/3.3/reference/dql-doctrine-query-language.html#ebnf
            // The sorting column is added regardless of the creator mode, to have a concise return type.
            // With the column added we get an array of 1) entity and 2) the column used for sorting (ignored later).
            ->addSelect("COALESCE($updatedDateTime.value, $addedDateTime.value, :$beforeDateTimesValue) AS last_update_datetime")
            ->setParameter($beforeDateTimesValue, '2000-01-01 00:00:00')
        ;

        if (!$this->choices->creatorMode) {
            $builder->addOrderBy("CASE WHEN d_c.country = 'RU' THEN 1 ELSE 0 END"); // 2022-02-24 & 1939-09-17
            $builder->addOrderBy('last_update_datetime', 'DESC'); // Put recently updated makers on top
        }

        $builder
            ->addOrderBy('LOWER(d_c.name)') // Then sort by name as typical
            ->addOrderBy('d_cu.id') // Keep miniatures in the same order on creator cards; grep-code-order-support-workaround
        ;
    }

    private function applyPaging(QueryBuilder $builder): void
    {
        $builder
            ->setFirstResult(Pagination::getFirstIdx($this->choices->pageSize, $this->choices->pageNumber))
            ->setMaxResults($this->choices->pageSize)
        ;
    }

    private function createSubqueryBuilder(QueryBuilder $builder, string $alias): QueryBuilder
    {
        return $builder->getEntityManager()->getRepository(Creator::class)->createQueryBuilder($alias);
    }

    private function applyCreatorId(QueryBuilder $builder): void // TODO: Test https://github.com/veelkoov/fuzzrake/issues/183
    {
        if ('' !== $this->choices->creatorId) {
            $creator = $this->getUniqueId();
            $creatorId = $this->getUniqueId();
            $creatorIdValue = $this->getUniqueId();

            $builder->andWhere($builder->expr()->exists(
                $this->createSubqueryBuilder($builder, $creator)
                    ->select('1')
                    ->join("$creator.creatorIds", $creatorId)
                    ->where("$creator.id = d_c.id")
                    ->andWhere("$creatorId.creatorId = :$creatorIdValue")
            ))
                ->setParameter($creatorIdValue, $this->choices->creatorId);
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

        $searchedTextValue = $this->getUniqueId();
        $creator = $this->getUniqueId();
        $creatorId = $this->getUniqueId();

        $builder->andWhere($builder->expr()->orX(
            "UPPER(d_c.name) LIKE :$searchedTextValue",
            "UPPER(d_c.formerly) LIKE :$searchedTextValue",
            $builder->expr()->exists(
                $this->createSubqueryBuilder($builder, $creator)
                ->select('1')
                ->join("$creator.creatorIds", $creatorId)
                ->where("$creator.id = d_c.id")
                ->andWhere("$creatorId.creatorId LIKE :$searchedTextValue")
            ),
        ))
            ->setParameter($searchedTextValue, $searchedText);
    }

    private function applyCountries(QueryBuilder $builder): void
    {
        if ($this->choices->countries->isNotEmpty()) {
            $countries = $this->choices->countries->map(static fn ($value) => Consts::FILTER_VALUE_UNKNOWN === $value ? Consts::DATA_VALUE_UNKNOWN : $value);

            $countriesValue = $this->getUniqueId();

            $builder->andWhere("d_c.country IN (:$countriesValue)")->setParameter($countriesValue, $countries);
        }
    }

    private function applyStates(QueryBuilder $builder): void
    {
        if ($this->choices->states->isNotEmpty()) {
            $states = $this->choices->states->map(static fn ($value) => Consts::FILTER_VALUE_UNKNOWN === $value ? Consts::DATA_VALUE_UNKNOWN : $value);

            $statesValue = $this->getUniqueId();

            $builder->andWhere("d_c.state IN (:$statesValue)")->setParameter($statesValue, $states);
        }
    }

    private function applyWantsSfw(QueryBuilder $builder): void
    {
        if (true !== $this->choices->isAdult || false !== $this->choices->wantsSfw) {
            $creator = $this->getUniqueId();
            $creatorValue1 = $this->getUniqueId();
            $creatorValue2 = $this->getUniqueId();
            $cvFieldName1 = $this->getUniqueId();
            $cvFieldName2 = $this->getUniqueId();
            $cvValueFalse = $this->getUniqueId();

            $builder->andWhere($builder->expr()->exists(
                $this->createSubqueryBuilder($builder, $creator)
                    ->select('1')
                    ->join("$creator.values", $creatorValue1)
                    ->join("$creator.values", $creatorValue2)
                    ->where("$creator.id = d_c.id")
                    ->andWhere("$creatorValue1.fieldName = :$cvFieldName1")
                    ->andWhere("$creatorValue1.value = :$cvValueFalse")
                    ->andWhere("$creatorValue2.fieldName = :$cvFieldName2")
                    ->andWhere("$creatorValue2.value = :$cvValueFalse")
            ))
                ->setParameter($cvFieldName1, Field::NSFW_WEBSITE->value)
                ->setParameter($cvFieldName2, Field::NSFW_SOCIAL->value)
                ->setParameter($cvValueFalse, StrUtils::asStr(false));
        }
    }

    private function applyWorksWithMinors(QueryBuilder $builder): void
    {
        if (true !== $this->choices->isAdult) {
            $creator = $this->getUniqueId();
            $creatorValue = $this->getUniqueId();
            $cvFieldName = $this->getUniqueId();
            $cvValueTrue = $this->getUniqueId();

            $builder->andWhere($builder->expr()->exists(
                $this->createSubqueryBuilder($builder, $creator)
                    ->select('1')
                    ->join("$creator.values", $creatorValue)
                    ->where("$creator.id = d_c.id")
                    ->andWhere("$creatorValue.fieldName = :$cvFieldName")
                    ->andWhere("$creatorValue.value = :$cvValueTrue")
            ))
                ->setParameter($cvFieldName, Field::WORKS_WITH_MINORS->value)
                ->setParameter($cvValueTrue, StrUtils::asStr(true));
        }
    }

    /**
     * FIXME: https://github.com/veelkoov/fuzzrake/issues/305.
     *
     * This is absolute garbage.
     */
    private function applyPaymentPlans(QueryBuilder $builder): void
    {
        $paymentPlansValue = $this->getUniqueId();

        if ($this->choices->wantsUnknownPaymentPlans) {
            if ($this->choices->wantsAnyPaymentPlans) {
                if ($this->choices->wantsNoPaymentPlans) {
                    // Unknown + ANY + None
                    return;
                } else {
                    // Unknown + ANY
                    $andWhere = "d_c.paymentPlans <> :$paymentPlansValue";
                    $parameter = Consts::DATA_PAYPLANS_NONE;
                }
            } else {
                if ($this->choices->wantsNoPaymentPlans) {
                    // Unknown + None
                    $andWhere = "d_c.paymentPlans IN (:$paymentPlansValue)";
                    $parameter = [Consts::DATA_VALUE_UNKNOWN, Consts::DATA_PAYPLANS_NONE];
                } else {
                    // Unknown
                    $andWhere = "d_c.paymentPlans = :$paymentPlansValue";
                    $parameter = Consts::DATA_VALUE_UNKNOWN;
                }
            }
        } else {
            if ($this->choices->wantsAnyPaymentPlans) {
                if ($this->choices->wantsNoPaymentPlans) {
                    // ANY + None
                    $andWhere = "d_c.paymentPlans <> :$paymentPlansValue";
                    $parameter = Consts::DATA_VALUE_UNKNOWN;
                } else {
                    // ANY
                    $andWhere = "d_c.paymentPlans NOT IN (:$paymentPlansValue)";
                    $parameter = [Consts::DATA_PAYPLANS_NONE, Consts::DATA_VALUE_UNKNOWN];
                }
            } else {
                if ($this->choices->wantsNoPaymentPlans) {
                    // None
                    $andWhere = "d_c.paymentPlans = :$paymentPlansValue";
                    $parameter = Consts::DATA_PAYPLANS_NONE;
                } else {
                    // Nothing selected
                    return;
                }
            }
        }

        $builder
            ->andWhere($andWhere)
            ->setParameter($paymentPlansValue, $parameter);
    }

    private function applyWantsInactive(QueryBuilder $builder): void
    {
        if (!$this->choices->wantsInactive) {
            $inactiveReasonValue = $this->getUniqueId();

            $builder
                ->andWhere("d_c.inactiveReason = :$inactiveReasonValue")
                ->setParameter($inactiveReasonValue, '');
        }
    }

    private function applySpecies(QueryBuilder $builder): void
    {
        if ($this->choices->species->isEmpty()) {
            return;
        }

        $conditions = [];

        $items = new SpecialItemsExtractor($this->choices->species, Consts::FILTER_VALUE_UNKNOWN);

        if ($items->hasSpecial(Consts::FILTER_VALUE_UNKNOWN)) {
            $creatorSpecie = $this->getUniqueId();

            $conditions[] = $builder->expr()->not($builder->expr()->exists(
                $builder->getEntityManager()
                    ->getRepository(CreatorSpecie::class)
                    ->createQueryBuilder($creatorSpecie)
                    ->select('1')
                    ->join("$creatorSpecie.specie", $this->getUniqueId())
                    ->where("$creatorSpecie.creator = d_c")
            ));
        }

        if ($items->common->isNotEmpty()) {
            $creatorSpecie = $this->getUniqueId();
            $specie = $this->getUniqueId();
            $sNameValues = $this->getUniqueId();

            $conditions[] = $builder->expr()->exists(
                $builder->getEntityManager()
                    ->getRepository(CreatorSpecie::class)
                    ->createQueryBuilder($creatorSpecie)
                    ->select('1')
                    ->join("$creatorSpecie.specie", $specie)
                    ->where("$specie.name IN (:$sNameValues)")
                    ->andWhere("$creatorSpecie.creator = d_c")
            );

            $builder->setParameter($sNameValues, $items->common);
        }

        $this->addWheres($builder, $conditions);
    }

    private function applyOpenFor(QueryBuilder $builder): void
    {
        $conditions = [];

        $items = new SpecialItemsExtractor($this->choices->openFor,
            Consts::FILTER_VALUE_TRACKING_ISSUES, Consts::FILTER_VALUE_NOT_TRACKED);

        if ($items->hasSpecial(Consts::FILTER_VALUE_TRACKING_ISSUES)) {
            $cvdCsTrackerIssueValueTrue = $this->getUniqueId();

            $conditions[] = $builder->expr()->eq('d_cvd.csTrackerIssue', ":$cvdCsTrackerIssueValueTrue");

            $builder->setParameter($cvdCsTrackerIssueValueTrue, true, ParameterType::BOOLEAN);
        }

        if ($items->hasSpecial(Consts::FILTER_VALUE_NOT_TRACKED)) {
            $creatorUrl = $this->getUniqueId();
            $cuTypeValue = $this->getUniqueId();

            $conditions[] = $builder->expr()->not($builder->expr()->exists(
                $builder->getEntityManager()
                    ->getRepository(CreatorUrl::class)
                    ->createQueryBuilder($creatorUrl)
                    ->select('1')
                    ->where("$creatorUrl.creator = d_c")
                    ->andWhere("$creatorUrl.type = :$cuTypeValue")
            ));

            $builder->setParameter($cuTypeValue, Field::URL_COMMISSIONS->value);
        }

        if ($items->common->isNotEmpty()) {
            $creatorOfferStatus = $this->getUniqueId();
            $cosIsOpenValueTrue = $this->getUniqueId();
            $cosOfferValues = $this->getUniqueId();

            $conditions[] = $builder->expr()->exists(
                $builder->getEntityManager()
                    ->getRepository(CreatorOfferStatus::class)
                    ->createQueryBuilder($creatorOfferStatus)
                    ->select('1')
                    ->where("$creatorOfferStatus.isOpen = :$cosIsOpenValueTrue")
                    ->andWhere("$creatorOfferStatus.offer IN (:$cosOfferValues)")
                    ->andWhere("$creatorOfferStatus.creator = d_c")
            );

            $builder
                ->setParameter($cosOfferValues, $items->common)
                ->setParameter($cosIsOpenValueTrue, true, ParameterType::BOOLEAN);
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

    private function applyCreatorValuesCount(QueryBuilder $builder, StringSet $selectedItems, Field $primaryField,
        ?Field $otherField = null, bool $allInsteadOfAny = false): void
    {
        $conditions = [];

        $items = new SpecialItemsExtractor($selectedItems, Consts::FILTER_VALUE_OTHER, Consts::FILTER_VALUE_UNKNOWN);

        if ($items->hasSpecial(Consts::FILTER_VALUE_OTHER)) {
            if (null === $otherField) {
                throw new InvalidArgumentException('Other field not selected');
            }

            $creator = $this->getUniqueId();
            $creatorValue = $this->getUniqueId();
            $cvFieldNameValue = $this->getUniqueId();

            $conditions[] = $builder->expr()->exists(
                $this->createSubqueryBuilder($builder, $creator)
                    ->select('1')
                    ->join("$creator.values", $creatorValue)
                    ->where("$creator.id = d_c.id")
                    ->andWhere("$creatorValue.fieldName = :$cvFieldNameValue")
            );

            $builder->setParameter($cvFieldNameValue, $otherField->value);
        }

        if ($items->hasSpecial(Consts::FILTER_VALUE_UNKNOWN)) {
            $creator = $this->getUniqueId();
            $creatorValue = $this->getUniqueId();
            $cvFieldNameValue = $this->getUniqueId();

            $conditions[] = $builder->expr()->not($builder->expr()->exists(
                $this->createSubqueryBuilder($builder, $creator)
                    ->select('1')
                    ->join("$creator.values", $creatorValue)
                    ->where("$creator.id = d_c.id")
                    ->andWhere("$creatorValue.fieldName IN (:$cvFieldNameValue)")
            ));

            $builder->setParameter($cvFieldNameValue, Vec\filter_nulls([$primaryField->value, $otherField?->value]));
        }

        if ($items->common->isNotEmpty()) {
            $creator = $this->getUniqueId();
            $creatorValue = $this->getUniqueId();
            $cvFieldName = $this->getUniqueId();
            $cvValueValues = $this->getUniqueId();

            $having = $allInsteadOfAny
                ? $builder->expr()->eq("COUNT($creator)", $items->common->count())
                : $builder->expr()->gt("COUNT($creator)", 0);

            $conditions[] = $builder->expr()->exists(
                $this->createSubqueryBuilder($builder, $creator)
                ->select('1')
                ->join("$creator.values", $creatorValue)
                ->where("$creator.id = d_c.id")
                ->andWhere("$creatorValue.fieldName = :$cvFieldName")
                ->andWhere("$creatorValue.value IN (:$cvValueValues)")
                ->groupBy("$creator.id")
                ->having($having)
            );

            $builder->setParameter($cvFieldName, $primaryField->value);
            $builder->setParameter($cvValueValues, $items->common);
        }

        $this->addWheres($builder, $conditions);
    }

    private function getUniqueId(): string
    {
        return 'd_uid'.((string) $this->uniqueIdIndex++);
    }
}
