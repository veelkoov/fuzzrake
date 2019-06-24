<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

use App\Utils\Regexp\Match;

class AnalysisResult
{
    /**
     * @var Match|null
     */
    private $openMatch;

    /**
     * @var Match|null
     */
    private $closedMatch;

    public function __construct(?Match $openMatch, ?Match $closedMatch)
    {
        $this->openMatch = $openMatch;
        $this->closedMatch = $closedMatch;
    }

    public function getStatus(): ?bool
    {
        if (null !== $this->openMatch) {
            if (null !== $this->closedMatch) {
                return null;
            } else {
                return true;
            }
        } else {
            if (null !== $this->closedMatch) {
                return false;
            } else {
                return null;
            }
        }
    }
}
