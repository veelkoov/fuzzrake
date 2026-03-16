<?php

declare(strict_types=1);

namespace App\Utils;

interface HasEmailGetter
{
    public function getEmail(): string;
}
