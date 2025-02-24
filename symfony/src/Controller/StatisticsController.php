<?php

declare(strict_types=1);

namespace App\Controller;

use App\Data\Definitions\Fields\Field;
use App\Filtering\FiltersData\FilterData;
use App\Filtering\FiltersData\FiltersService;
use App\Filtering\FiltersData\Item;
use App\Service\DataService;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\UnexpectedResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

class StatisticsController extends AbstractController
{
    private const array MATCH_WORDS = [
        'accessor',
        'bases?|blanks?',
        'bendable|pose?able|lickable',
        'brush',
        'change?able|detach|remove?able',
        'claws?',
        'cosplay',
        'details?',
        '(?<!g)ears?',
        'eyes?',
        'jaw|muzzle',
        '(?<![a-z])(LCD|LED|EL)(?![a-z])',
        'magnet',
        'noses?|nostril',
        'paw ?pad|pads',
        'padd',
        'part(?!ial)s?|elements?',
        'paws?',
        'plush',
        'pocket',
        'props?',
        'sleeves?',
        'sneakers|sandals|feet',
        '(?<!de)tail',
        'wings?',
    ];

    /**
     * @throws UnexpectedResultException
     */
    #[Route(path: '/stats', name: RouteName::STATISTICS)]
    #[Cache(maxage: 3600, public: true)]
    public function statistics(FiltersService $filtersService, DataService $dataService): Response
    {
        $productionModels = $filtersService->getValuesFilterData(Field::PRODUCTION_MODELS);
        $orderTypes = $filtersService->getValuesFilterData(Field::ORDER_TYPES, Field::OTHER_ORDER_TYPES);
        $otherOrderTypes = $filtersService->getValuesFilterData(Field::OTHER_ORDER_TYPES);
        $styles = $filtersService->getValuesFilterData(Field::STYLES, Field::OTHER_STYLES);
        $otherStyles = $filtersService->getValuesFilterData(Field::OTHER_STYLES);
        $features = $filtersService->getValuesFilterData(Field::FEATURES, Field::OTHER_FEATURES);
        $otherFeatures = $filtersService->getValuesFilterData(Field::OTHER_FEATURES);
        $countries = $filtersService->getCountriesFilterData();

        return $this->render('statistics/statistics.html.twig', [
            'countries'        => $this->prepareTableData($countries),
            'productionModels' => $this->prepareTableData($productionModels),
            'orderTypes'       => $this->prepareTableData($orderTypes),
            'otherOrderTypes'  => $this->prepareListData($otherOrderTypes->items),
            'styles'           => $this->prepareTableData($styles),
            'otherStyles'      => $this->prepareListData($otherStyles->items),
            'features'         => $this->prepareTableData($features),
            'otherFeatures'    => $this->prepareListData($otherFeatures->items),
            'commissionsStats' => $dataService->getOfferStatusStats(),
            'completeness'     => $dataService->getCompletenessStats(),
            'providedInfo'     => $dataService->getProvidedInfoStats(),
            'matchWords'       => self::MATCH_WORDS,
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function prepareTableData(FilterData $input): array
    {
        $result = [];

        foreach ($this->getLeafItems($input->items) as $item) {
            $count = $item->count;

            if (!array_key_exists($count, $result)) {
                $result[$count] = [];
            }

            $result[$count][] = $item->label;
        }

        $result = array_flip(array_map(fn (array $items) => implode(', ', $items), $result));

        arsort($result);

        foreach ($input->specialItems as $item) {
            $result[$item->label] = $item->count;
        }

        return $result;
    }

    /**
     * @param list<Item> $input
     *
     * @return list<Item>
     */
    private function getLeafItems(array $input): array
    {
        $result = [];

        foreach ($input as $item) {
            if ([] !== $item->subitems) {
                $result = [...$result, ...$this->getLeafItems($item->subitems)];
            } else {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @param array<Item> $items
     *
     * @return array<Item>
     */
    private function prepareListData(array $items): array
    {
        usort($items, function (Item $itemA, Item $itemB) {
            if ($itemA->count !== $itemB->count) {
                return $itemB->count - $itemA->count;
            }

            return strcmp($itemA->label, $itemB->label);
        });

        return $items;
    }
}
