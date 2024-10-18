<?php

declare(strict_types=1);

namespace App\Twig;

use App\Twig\Utils\SafeFor;
use App\Utils\DateTime\DateTimeFormat;
use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateTimeExtensions extends AbstractExtension
{
    private const string FORMAT = 'Y-m-d H:i T'; // grep-expected-utc-datetime-format

    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('fragile_datetime_utc', self::fragileUtc(...), SafeFor::HTML),
            new TwigFilter('nullable_datetime_utc', self::nullableUtc(...), SafeFor::HTML),
        ];
    }

    private static function fragileUtc(mixed $input): string
    {
        return '<span class="utc_datetime">'.DateTimeFormat::fragileUtc($input, self::FORMAT).'</span>';
    }

    private static function nullableUtc(mixed $input): string
    {
        return '<span class="utc_datetime">'.DateTimeFormat::nullableUtc($input, self::FORMAT).'</span>';
    }
}
