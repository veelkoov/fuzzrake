<?php

declare(strict_types=1);

namespace App\Controller\Frontend;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Utils\ArtisanMetadata;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/")
 */
class StatisticsController extends AbstractController
{
    const MATCH_WORDS = [
        'part(?!ial)s?|elements?',
        'props?',
        'remove?able',
        'pose?able',
        'bendable',
        'change?able',
        'brush',
        'details?',
        'pads?',
        'sleeves?',
        'claws?',
        'eyes?',
        'noses?|nostril',
        'ears?',
        'paws?',
        'jaw|muzzle',
        '(?<!de)tail',
        'wings?',
        'sneakers|sandals|feet',
        '(?<![a-z])(LCD|LED|EL)(?![a-z])',
        'plush',
        'pocket',
        'accessor',
        'blanks?',
        'bases?',
    ];

    /**
     * @Route("/statistics.html", name="statistics")
     *
     * @param ArtisanRepository $artisanRepository
     *
     * @return Response
     */
    public function statistics(ArtisanRepository $artisanRepository): Response
    {
        $types = $artisanRepository->getDistinctOrderTypes();
        $otherTypes = $artisanRepository->getDistinctOtherOrderTypes();
        $styles = $artisanRepository->getDistinctStyles();
        $otherStyles = $artisanRepository->getDistinctOtherStyles();
        $features = $artisanRepository->getDistinctFeatures();
        $otherFeatures = $artisanRepository->getDistinctOtherFeatures();
        $countries = $artisanRepository->getDistinctCountriesToCountAssoc();
        $commissionsStats = $artisanRepository->getCommissionsStats();

        return $this->render('frontend/statistics/statistics.html.twig', [
            'countries' => $this->prepareTableData($countries),
            'types' => $this->prepareTableData($types),
            'otherTypes' => $this->prepareListData($otherTypes),
            'styles' => $this->prepareTableData($styles),
            'otherStyles' => $this->prepareListData($otherStyles),
            'features' => $this->prepareTableData($features),
            'otherFeatures' => $this->prepareListData($otherFeatures),
            'commissionsStats' => $this->prepareCommissionsStatsTableData($commissionsStats),
            'completeness' => $this->prepareCompletenessData($artisanRepository->findAll()),
            'providedInfo' => $this->prepareProvidedInfoData($artisanRepository->findAll()),
            'matchWords' => self::MATCH_WORDS,
        ]);
    }

    /**
     * @Route("/ordering.html", name="ordering")
     *
     * @param ArtisanRepository $artisanRepository
     *
     * @return Response
     */
    public function ordering(ArtisanRepository $artisanRepository): Response
    {
        $otherItems = $artisanRepository->getOtherItemsData();

        return $this->render('frontend/statistics/ordering.html.twig', [
            'otherItems' => $this->prepareListData($otherItems),
            'matchWords' => self::MATCH_WORDS,
        ]);
    }

    private function prepareTableData(array $input): array
    {
        $result = [];

        foreach ($input as $item => $count) {
            if (!array_key_exists($count, $result)) {
                $result[$count] = [];
            }

            $result[$count][] = $item;
        }

        foreach ($result as $count => $items) {
            $result[$count] = implode(', ', $items);
        }

        $result = array_flip($result);
        arsort($result);

        return $result;
    }

    private function prepareListData(array $otherTypes): array
    {
        uksort($otherTypes, function ($a, $b) use ($otherTypes) {
            if ($otherTypes[$a] !== $otherTypes[$b]) {
                return $otherTypes[$b] - $otherTypes[$a];
            }

            return strcmp($a, $b);
        });

        return $otherTypes;
    }

    private function prepareCommissionsStatsTableData(array $commissionsStats): array
    {
        return [
            'Open' => $commissionsStats['open'],
            'Closed' => $commissionsStats['closed'],
            'Status tracked' => $commissionsStats['tracked'],
            'Status successfully tracked' => $commissionsStats['successfully_tracked'],
            'Total' => $commissionsStats['total'],
        ];
    }

    /**
     * @param Artisan[] $artisans
     *
     * @return array
     */
    private function prepareCompletenessData(array $artisans): array
    {
        $completeness = array_filter(array_map(function (Artisan $artisan) {
            return $artisan->completeness();
        }, $artisans));

        $result = [];

        $levels = ['100%' => 100, '90-99%' => 90, '80-89%' => 80, '70-79%' => 70, '60-69%' => 60, '50-59%' => 50,
            '40-49%' => 40, '30-39%' => 30, '20-29%' => 20, '10-19%' => 10, '0-9%' => 0, ];

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

        foreach (ArtisanMetadata::PRETTY_TO_MODEL_FIELD_NAMES_MAP as $prettyName => $modelName) {
            if (ArtisanMetadata::IGNORED_IU_FORM_FIELD !== $modelName) {
                $result[$prettyName] = array_reduce($artisans, function (int $carry, Artisan $item) use ($modelName) {
                    return $carry + ('' !== $item->get($modelName) ? 1 : 0);
                }, 0);
            }
        }

        arsort($result);

        return $result;
    }
}
