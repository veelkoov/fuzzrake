<?php

declare(strict_types=1);

namespace App\Twig;

use App\Utils\DateTime\DateTimeFormat;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateTimeExtensions extends AbstractExtension
{
    private const SAFE_HTML = ['is_safe' => ['html']];
    private const FORMAT = 'Y-m-d H:i T'; // grep-expected-utc-datetime-format

    public function getFilters(): array
    {
        return [
            new TwigFilter('fragile_datetime_utc', fn (mixed $input): string => self::fragileUtc($input), self::SAFE_HTML),
            new TwigFilter('nullable_datetime_utc', fn (mixed $input): string => self::nullableUtc($input), self::SAFE_HTML),
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
