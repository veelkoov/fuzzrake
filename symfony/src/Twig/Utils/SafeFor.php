<?php

declare(strict_types=1);

namespace App\Twig\Utils;

use App\Utils\Traits\UtilityClass;

final class SafeFor
{
    use UtilityClass;

    public const array HTML = ['is_safe' => ['html']];
    public const array HTML_PRE = ['pre_escape' => 'html', 'is_safe' => ['html']];
    public const array JS = ['is_safe' => ['js']];
}
