<?php

declare(strict_types=1);

namespace App\Twig\Utils;

use App\Utils\Traits\UtilityClass;

final class SafeFor
{
    use UtilityClass;

    public const HTML = ['is_safe' => ['html']];
    public const HTML_PRE = ['pre_escape' => 'html', 'is_safe' => ['html']];
    public const JS = ['is_safe' => ['js']];
}
