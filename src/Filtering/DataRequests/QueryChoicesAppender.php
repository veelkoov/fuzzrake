<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests;

use App\Data\Definitions\Fields\Field;
use App\Entity\Artisan;
use App\Entity\CreatorSpecie;
use App\Service\CacheDigestProvider;
use App\Utils\StrUtils;
use Doctrine\ORM\QueryBuilder;
use Psl\Vec;

class QueryChoicesAppender implements CacheDigestProvider
{
    private readonly Choices $choices;

    public function __construct(Choices $choices)
    {
        $this->choices = new Choices($choices->makerId, $choices->countries, $choices->states, [], [], [], [], [], [], $choices->species, $choices->wantsUnknownPaymentPlans, $choices->wantsAnyPaymentPlans, $choices->wantsNoPaymentPlans, $choices->isAdult, $choices->wantsSfw, $choices->wantsInactive);
    }

    public function getCacheDigest(): string
    {
        return $this->choices->getCacheDigest();
    }

    public function applyChoices(QueryBuilder $builder): void
    {
        $this->applyMakerId($builder);
        $this->applyCountries($builder);
        $this->applyStates($builder);
        $this->applyPaymentPlans($builder);
        $this->applySpecies($builder);
        $this->applyWantsSfw($builder);
        $this->applyWorksWithMinors($builder);
        $this->applyWantsInactive($builder);
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

    private function applyCountries(QueryBuilder $builder): void
    {
        if ([] !== $this->choices->countries) {
            $countries = Vec\map($this->choices->countries,
                fn ($value) => Consts::FILTER_VALUE_UNKNOWN === $value ? Consts::DATA_VALUE_UNKNOWN : $value);

            $builder->andWhere('a.country IN (:countries)')->setParameter('countries', $countries);
        }
    }

    private function applyStates(QueryBuilder $builder): void
    {
        if ([] !== $this->choices->states) {
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
        if ([] === $this->choices->species) {
            return;
        }

        $builder->andWhere($builder->expr()->exists(
            $builder->getEntityManager()
                ->getRepository(CreatorSpecie::class)
                ->createQueryBuilder('cs1')
                ->select('1')
                ->join('cs1.specie', 'sp1')
                ->where('sp1.name IN (:specieNames)')
                ->andWhere('cs1.creator = a')
        ))
            ->setParameter('specieNames', $this->choices->species);
    }
}
