<?php

declare(strict_types=1);

namespace App\Tracking;

use App\ValueObject\Messages\TrackCreatorsV1;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

final class TrackCreatorsTask
{
    #[AsMessageHandler]
    public function execute(TrackCreatorsV1 $message): void
    {
        // TODO
    }
}
