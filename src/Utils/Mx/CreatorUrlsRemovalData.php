<?php

declare(strict_types=1);

namespace App\Utils\Mx;

final class CreatorUrlsRemovalData
{
    public function __construct(
        public readonly GroupedUrls $removedUrls,
        public readonly GroupedUrls $remainingUrls,
        public bool $hide,
        public bool $sendEmail,
    ) {
    }
}
