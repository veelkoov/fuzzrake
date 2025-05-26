<?php

declare(strict_types=1);

namespace App\Photos\MiniatureUrlResolver;

use App\Utils\Web\Url;

interface MiniatureUrlResolver
{
    public function supports(string $url): bool;

    public function getMiniatureUrl(Url $url): string;
}
