<?php

declare(strict_types=1);

namespace App\Tracking;

use Veelkoov\Debris\StringList;

class AnalysisAggregator
{
    /**
     * @param list<AnalysisResult> $results
     */
    public static function aggregate(array $results): AnalysisResults
    {
        return new AnalysisResults(new StringList(), new StringList(), true); // TODO
    }
}
