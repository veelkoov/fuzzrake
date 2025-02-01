<?php

declare(strict_types=1);

namespace App\Controller;

use App\Data\Definitions\Fields\Field;
use App\Filtering\FiltersData\Data\ItemList;
use App\Filtering\FiltersData\FilterData;
use App\Filtering\FiltersData\FiltersService;
use App\Filtering\FiltersData\Item;
use App\Repository\CreatorOfferStatusRepository;
use App\Service\DataService;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\UnexpectedResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function statistics(Request $request, CreatorOfferStatusRepository $offerStatusRepository, FiltersService $filtersService, DataService $dataService): Response
    {
        $productionModels = $filtersService->getValuesFilterData(Field::PRODUCTION_MODELS);
        $orderTypes = $filtersService->getValuesFilterData(Field::ORDER_TYPES, Field::OTHER_ORDER_TYPES);
        $otherOrderTypes = $filtersService->getValuesFilterData(Field::OTHER_ORDER_TYPES);
        $styles = $filtersService->getValuesFilterData(Field::STYLES, Field::OTHER_STYLES);
        $otherStyles = $filtersService->getValuesFilterData(Field::OTHER_STYLES);
        $features = $filtersService->getValuesFilterData(Field::FEATURES, Field::OTHER_FEATURES);
        $otherFeatures = $filtersService->getValuesFilterData(Field::OTHER_FEATURES);
        $countries = $filtersService->getCountriesFilterData();
        $commissionsStats = $offerStatusRepository->getCommissionsStats();

        return $this->render('statistics/statistics.html.twig', [
            'countries'        => $this->prepareTableData($countries),
            'productionModels' => $this->prepareTableData($productionModels),
            'orderTypes'       => $this->prepareTableData($orderTypes),
            'otherOrderTypes'  => $this->prepareListData($otherOrderTypes->items),
            'styles'           => $this->prepareTableData($styles),
            'otherStyles'      => $this->prepareListData($otherStyles->items),
            'features'         => $this->prepareTableData($features),
            'otherFeatures'    => $this->prepareListData($otherFeatures->items),
            'commissionsStats' => $this->prepareCommissionsStatsTableData($commissionsStats),
            'completeness'     => $dataService->getCompletenessStats(),
            'providedInfo'     => $dataService->getProvidedInfoStats(),
            'matchWords'       => self::MATCH_WORDS,
            'showIgnored'      => filter_var($request->get('showIgnored', 0), FILTER_VALIDATE_BOOL),
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

    private function getLeafItems(ItemList $input): ItemList
    {
        $result = ItemList::mut();

        $input->each(function (Item $item) use ($result): void {
            if ($item->subitems->isEmpty()) {
                $result->add($item);
            } else {
                $result->add(...$this->getLeafItems($item->subitems));
            }
        });

        return $result->frozen();
    }

    private function prepareListData(ItemList $items): ItemList
    {
        return $items->sorted(function (Item $itemA, Item $itemB) {
            if ($itemA->count !== $itemB->count) {
                return $itemB->count - $itemA->count;
            }

            return strcmp($itemA->label, $itemB->label);
        });
    }

    /**
     * @param psArtisanStatsArray $commissionsStats
     *
     * @return array<string, int>
     */
    private function prepareCommissionsStatsTableData(array $commissionsStats): array
    {
        return [
            'Open for anything'              => $commissionsStats['open_for_anything'],
            'Closed for anything'            => $commissionsStats['closed_for_anything'],
            'Status successfully tracked'    => $commissionsStats['successfully_tracked'],
            'Partially successfully tracked' => $commissionsStats['partially_tracked'],
            'Tracking failed completely'     => $commissionsStats['tracking_failed'],
            'Tracking issues'                => $commissionsStats['tracking_issues'],
            'Status tracked'                 => $commissionsStats['tracked'],
            'Total'                          => $commissionsStats['total'],
        ];
    }
}
