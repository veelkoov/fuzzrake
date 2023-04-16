<?php

declare(strict_types=1);

namespace App\Controller;

use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\Fields\Fields;
use App\Data\Species\SpeciesService;
use App\Filtering\FiltersData\FilterData;
use App\Filtering\FiltersData\Item;
use App\Repository\ArtisanCommissionsStatusRepository;
use App\Repository\ArtisanRepository;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\UnexpectedResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Annotation\Route;

class StatisticsController extends AbstractController
{
    private const MATCH_WORDS = [
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
    public function statistics(Request $request, ArtisanRepository $artisanRepository, ArtisanCommissionsStatusRepository $commissionsStatusRepository, SpeciesService $species): Response
    {
        $productionModels = $artisanRepository->getDistinctProductionModels();
        $orderTypes = $artisanRepository->getDistinctOrderTypes();
        $otherOrderTypes = $artisanRepository->getDistinctOtherOrderTypes();
        $styles = $artisanRepository->getDistinctStyles();
        $otherStyles = $artisanRepository->getDistinctOtherStyles();
        $features = $artisanRepository->getDistinctFeatures();
        $otherFeatures = $artisanRepository->getDistinctOtherFeatures();
        $countries = $artisanRepository->getDistinctCountriesToCountAssoc();
        $commissionsStats = $commissionsStatusRepository->getCommissionsStats();

        $artisans = Artisan::wrapAll($artisanRepository->getActive());

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
            'completeness'     => $this->prepareCompletenessData($artisans),
            'providedInfo'     => $this->prepareProvidedInfoData($artisans),
            'speciesStats'     => $species->getStats(),
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

        foreach ($input->items as $item) {
            $count = $item->count;

            if (!array_key_exists($count, $result)) {
                $result[$count] = [];
            }

            $result[$count][] = $item->label;
        }

        foreach ($result as $item => $items) {
            $result[$item] = implode(', ', $items); // @phpstan-ignore-line
        }

        $result = array_flip($result); // @phpstan-ignore-line
        arsort($result);

        foreach ($input->specialItems as $item) {
            $result[$item->label] = $item->count;
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

    /**
     * @param Artisan[] $artisans
     *
     * @return array<string, int>
     */
    private function prepareCompletenessData(array $artisans): array
    {
        $completeness = array_filter(array_map(fn (Artisan $artisan) => $artisan->getCompleteness(), $artisans));

        $result = [];

        $levels = ['100%' => 100, '90-99%' => 90, '80-89%' => 80, '70-79%' => 70, '60-69%' => 60, '50-59%' => 50,
                 '40-49%' => 40,  '30-39%' => 30, '20-29%' => 20, '10-19%' => 10, '0-9%' => 0, ];

        foreach ($levels as $description => $level) {
            $result[$description] = count(array_filter($completeness, fn (int $percent) => $percent >= $level));

            $completeness = array_filter($completeness, fn (int $percent) => $percent < $level);
        }

        return $result;
    }

    /**
     * @param Artisan[] $artisans
     *
     * @return array<string, int>
     *
     * @see SmartAccessDecorator::getLastMakerId()
     */
    private function prepareProvidedInfoData(array $artisans): array
    {
        $result = [];

        foreach (Fields::inStats() as $field) {
            $result[$field->name] = array_reduce($artisans, function (int $carry, Artisan $artisan) use ($field): int {
                $value = $artisan->get($field);

                if (Field::FORMER_MAKER_IDS === $field) {
                    /* Some makers were added before introduction of the maker IDs. They were assigned fake former IDs,
                     * so we can rely on SmartAccessDecorator::getLastMakerId() etc. Those IDs are "M000000", part
                     * where the digits is zero-padded artisan database ID. */

                    $placeholder = sprintf('M%06d', $artisan->getId());

                    if ($value === $placeholder) {
                        return $carry; // Fake former maker ID - don't add to the result
                    }
                }

                if (null === $value || '' === $value) { // Boolean fields count even if they're false
                    return $carry;
                } else {
                    return $carry + 1;
                }
            }, 0);
        }

        arsort($result);

        return $result;
    }
}
