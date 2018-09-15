<?php

declare(strict_types=1);

namespace App\Controller\Frontend;

use App\Repository\ArtisanRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/")
 */
class StatisticsController extends AbstractController
{
    /**
     * @Route("/statistics.html", name="statistics")
     *
     * @return Response
     */
    public function statistics(ArtisanRepository $artisanRepository): Response
    {
        $types = $artisanRepository->getDistinctTypes();
        $otherTypes = $artisanRepository->getDistinctOtherTypes();
        $styles = $artisanRepository->getDistinctStyles();
        $otherStyles = $artisanRepository->getDistinctOtherStyles();
        $features = $artisanRepository->getDistinctFeatures();
        $otherFeatures = $artisanRepository->getDistinctOtherFeatures();
        $countries = $artisanRepository->getDistinctCountries();
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
}
