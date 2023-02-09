<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests;

use App\Filtering\DataRequests\Filters\FeaturesFilter;
use App\Filtering\DataRequests\Filters\FilterInterface;
use App\Filtering\DataRequests\Filters\LanguagesFilter;
use App\Filtering\DataRequests\Filters\OpenForFilter;
use App\Filtering\DataRequests\Filters\OrderTypesFilter;
use App\Filtering\DataRequests\Filters\ProductionModelsFilter;
use App\Filtering\DataRequests\Filters\SpeciesFilterFactory;
use App\Filtering\DataRequests\Filters\StylesFilter;
use App\Repository\ArtisanRepository;
use App\Service\Cache;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\ValueObject\CacheTags;

use function Psl\Iter\all;
use function Psl\Vec\filter;
use function Psl\Vec\map;
use function Psl\Vec\values;

class FilteredDataProvider
{
    public function __construct(
        private readonly ArtisanRepository $repository,
        private readonly SpeciesFilterFactory $speciesFilterFactory,
        private readonly Cache $cache,
    ) {
    }

    /**
     * @return array<array<psJsonFieldValue>>
     */
    public function getPublicDataFor(Choices $choices): array
    {
        return $this->cache->getCached('Filtered.artisans.'.$choices->getCacheDigest(),
            CacheTags::ARTISANS, fn () => $this->retrievePublicDataFor($choices));
    }

    /**
     * @return array<array<psJsonFieldValue>>
     */
    private function retrievePublicDataFor(Choices $choices): array
    {
        $appender = new QueryChoicesAppender($choices);

        $artisans = $this->cache->getCached('Filtered.query.'.$appender->getCacheDigest(),
            CacheTags::ARTISANS, fn () => Artisan::wrapAll($this->repository->getFiltered($appender)));

        $filters = [];

        if ([] !== $choices->languages) {
            $filters[] = new LanguagesFilter($choices->languages);
        }
        if ([] !== $choices->features) {
            $filters[] = new FeaturesFilter($choices->features);
        }
        if ([] !== $choices->styles) {
            $filters[] = new StylesFilter($choices->styles);
        }
        if ([] !== $choices->productionModels) {
            $filters[] = new ProductionModelsFilter($choices->productionModels);
        }
        if ([] !== $choices->orderTypes) {
            $filters[] = new OrderTypesFilter($choices->orderTypes);
        }
        if ([] !== $choices->openFor) {
            $filters[] = new OpenForFilter($choices->openFor);
        }
        if ([] !== $choices->species) {
            $filters[] = $this->speciesFilterFactory->get($choices->species);
        }

        $artisans = filter($artisans,
            fn (Artisan $artisan) => all($filters,
                fn (FilterInterface $filter) => $filter->matches($artisan)
            )
        );

        return map($artisans, fn (Artisan $artisan) => values($artisan->getPublicData()));
    }
}
