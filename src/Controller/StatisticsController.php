<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Utils\Artisan\Fields;
use App\Utils\FilterItem;
use App\Utils\FilterItems;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class StatisticsController extends AbstractController
{
    const MATCH_WORDS = [
        'accessor',
        'bases?|blanks?',
        'bendable|pose?able|lickable',
        'brush',
        'change?able|detach|remove?able',
        'claws?',
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
     * @Route("/statistics.html", name="statistics")
     * @Cache(maxage=3600, public=true)
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function statistics(ArtisanRepository $artisanRepository): Response
    {
        $productionModels = $artisanRepository->getDistinctProductionModels();
        $orderTypes = $artisanRepository->getDistinctOrderTypes();
        $otherOrderTypes = $artisanRepository->getDistinctOtherOrderTypes();
        $styles = $artisanRepository->getDistinctStyles();
        $otherStyles = $artisanRepository->getDistinctOtherStyles();
        $features = $artisanRepository->getDistinctFeatures();
        $otherFeatures = $artisanRepository->getDistinctOtherFeatures();
        $countries = $artisanRepository->getDistinctCountriesToCountAssoc();
        $commissionsStats = $artisanRepository->getCommissionsStats();

        return $this->render('statistics/statistics.html.twig', [
            'countries'        => $this->prepareTableData($countries),
            'productionModels' => $this->prepareTableData($productionModels),
            'orderTypes'       => $this->prepareTableData($orderTypes),
            'otherOrderTypes'  => $this->prepareListData($otherOrderTypes->getItems()),
            'styles'           => $this->prepareTableData($styles),
            'otherStyles'      => $this->prepareListData($otherStyles->getItems()),
            'features'         => $this->prepareTableData($features),
            'otherFeatures'    => $this->prepareListData($otherFeatures->getItems()),
            'commissionsStats' => $this->prepareCommissionsStatsTableData($commissionsStats),
            'completeness'     => $this->prepareCompletenessData($artisanRepository->getAll()),
            'providedInfo'     => $this->prepareProvidedInfoData($artisanRepository->getAll()),
            'matchWords'       => self::MATCH_WORDS,
        ]);
    }

    private function prepareTableData(FilterItems $input): array
    {
        $result = [];

        foreach ($input->getItems() as $item) {
            if (!array_key_exists($item->getCount(), $result)) {
                $result[$item->getCount()] = [];
            }

            $result[$item->getCount()][] = $item->getLabel();
        }

        foreach ($result as $item => $items) {
            $result[$item] = implode(', ', $items);
        }

        $result = array_flip($result);
        arsort($result);

        if ($input->isHasOther()) {
            $result['Other'] = $input->getOtherCount();
        }
        $result['Unknown'] = $input->getUnknownCount();

        return $result;
    }

    /**
     * @param FilterItem[] $items
     *
     * @return FilterItem[]
     */
    private function prepareListData(array $items): array
    {
        uksort($items, function ($keyA, $keyB) use ($items) {
            if ($items[$keyA]->getCount() !== $items[$keyB]->getCount()) {
                return $items[$keyB]->getCount() - $items[$keyA]->getCount();
            }

            return strcmp($items[$keyA]->getLabel(), $items[$keyB]->getLabel());
        });

        return $items;
    }

    private function prepareCommissionsStatsTableData(array $commissionsStats): array
    {
        return [
            'Open'                        => $commissionsStats['open'],
            'Closed'                      => $commissionsStats['closed'],
            'Status tracked'              => $commissionsStats['tracked'],
            'Status successfully tracked' => $commissionsStats['successfully_tracked'],
            'Total'                       => $commissionsStats['total'],
        ];
    }

    /**
     * @param Artisan[] $artisans
     */
    private function prepareCompletenessData(array $artisans): array
    {
        $completeness = array_filter(array_map(function (Artisan $artisan) {
            return $artisan->completeness();
        }, $artisans));

        $result = [];

        $levels = ['100%' => 100, '90-99%' => 90, '80-89%' => 80, '70-79%' => 70, '60-69%' => 60, '50-59%' => 50,
                 '40-49%' => 40,  '30-39%' => 30, '20-29%' => 20, '10-19%' => 10, '0-9%'   => 0, ];

        foreach ($levels as $description => $level) {
            $result[$description] = count(array_filter($completeness, function (int $percent) use ($level) {
                return $percent >= $level;
            }));

            $completeness = array_filter($completeness, function (int $percent) use ($level) {
                return $percent < $level;
            });
        }

        return $result;
    }

    private function prepareProvidedInfoData(array $artisans): array
    {
        $result = [];

        foreach (Fields::inStats() as $field) {
            $result[$field->name()] = array_reduce($artisans, function (int $carry, Artisan $artisan) use ($field) {
                return $carry + ('' !== $artisan->get($field) ? 1 : 0);
            }, 0);
        }

        arsort($result);

        return $result;
    }
}
