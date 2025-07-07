<?php

declare(strict_types=1);

namespace App\Twig;

use App\Utils\DateTime\DateTimeFormat;
use Twig\Attribute\AsTwigFilter;

class DateTimeExtensions
{
    private const string FORMAT = 'Y-m-d H:i T'; // grep-expected-utc-datetime-format

    #[AsTwigFilter('fragile_datetime_utc', isSafe: ['html'])]
    public function fragileDatetimeUtc(mixed $input): string
    {
        return '<span class="utc_datetime">'.DateTimeFormat::fragileUtc($input, self::FORMAT).'</span>';
    }

    #[AsTwigFilter('nullable_datetime_utc', isSafe: ['html'])]
    public function nullableDatetimeUtc(mixed $input): string
    {
        return '<span class="utc_datetime">'.DateTimeFormat::nullableUtc($input, self::FORMAT).'</span>';
    }
}
