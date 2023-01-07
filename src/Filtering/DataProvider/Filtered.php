<?php

declare(strict_types=1);

namespace App\Filtering\DataProvider;

use App\Filtering\Choices;
use App\Filtering\DataProvider\Filters\FeaturesFilter;
use App\Filtering\DataProvider\Filters\FilterInterface;
use App\Filtering\DataProvider\Filters\LanguagesFilter;
use App\Filtering\DataProvider\Filters\OpenForFilter;
use App\Filtering\DataProvider\Filters\OrderTypesFilter;
use App\Filtering\DataProvider\Filters\ProductionModelsFilter;
use App\Filtering\DataProvider\Filters\SpeciesFilterFactory;
use App\Filtering\DataProvider\Filters\StylesFilter;
use App\Repository\ArtisanRepository;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Psr\Cache\InvalidArgumentException;

use function Psl\Iter\all;
use function Psl\Vec\filter;
use function Psl\Vec\map;
use function Psl\Vec\values;

class Filtered implements FilteredInterface
{
    public function __construct(
        private readonly ArtisanRepository $repository,
        private readonly SpeciesFilterFactory $speciesFilterFactory,
    ) {
    }

    /**
     * @return array<array<psJsonFieldValue>>
     *
     * @throws InvalidArgumentException
     */
    public function getPublicDataFor(Choices $choices): array
    {
        $artisans = Artisan::wrapAll($this->repository->getFiltered($choices));

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
        if ([] !== $choices->commissionStatuses) {
            $filters[] = new OpenForFilter($choices->commissionStatuses);
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
