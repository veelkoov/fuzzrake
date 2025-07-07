<?php

declare(strict_types=1);

namespace App\Twig;

use App\Service\DataService;
use Twig\Attribute\AsTwigFunction;

class AppHeavyExtensions
{
    public function __construct(
        private readonly DataService $dataService,
    ) {
    }

    #[AsTwigFunction('get_latest_event_timestamp')]
    public function getLatestEventTimestamp(): ?string
    {
        return $this->dataService->getLatestEventTimestamp()?->format('Y-m-d H:i:s P');
    }
}
