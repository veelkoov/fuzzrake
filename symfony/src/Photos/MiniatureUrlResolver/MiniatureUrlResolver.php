<?php

declare(strict_types=1);

namespace App\Photos\MiniatureUrlResolver;

use App\Photos\MiniaturesUpdateException;
use App\Utils\Web\Url\Url;

interface MiniatureUrlResolver
{
    public function supports(string $url): bool;

    /**
     * @throws MiniaturesUpdateException
     */
    public function getMiniatureUrl(Url $url): string;
}
