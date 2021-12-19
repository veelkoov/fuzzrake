<?php

declare(strict_types=1);

namespace App\Utils\Console;

use App\Utils\Traits\UtilityClass;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

final class Formatter
{
    use UtilityClass;

    private const INVALID = 'invalid';
    private const FIX = 'fix';
    private const ADDED = 'diff_added';
    private const DELETED = 'diff_deleted';
    private const IMPORTED = 'diff_imported';
    private const SEP = 'sep';

    public static function setup(OutputFormatterInterface $formatter): void
    {
        $formatter->setStyle(self::ADDED, new OutputFormatterStyle('green'));
        $formatter->setStyle(self::DELETED, new OutputFormatterStyle('red'));
        $formatter->setStyle(self::IMPORTED, new OutputFormatterStyle('magenta'));

        $formatter->setStyle(self::INVALID, new OutputFormatterStyle('red'));
        $formatter->setStyle(self::FIX, new OutputFormatterStyle('blue'));
        $formatter->setStyle(self::SEP, new OutputFormatterStyle('gray'));
    }

    public static function invalid(string $item): string
    {
        return self::formatted(self::INVALID, $item);
    }

    public static function imported(string $item): string
    {
        return self::formatted(self::IMPORTED, $item);
    }

    public static function deleted(string $item): string
    {
        return self::formatted(self::DELETED, $item);
    }

    public static function added(string $item): string
    {
        return self::formatted(self::ADDED, $item);
    }

    public static function fix(string $item): string
    {
        return self::formatted(self::FIX, $item);
    }

    public static function shy(string $item): string
    {
        return self::formatted(self::SEP, $item);
    }

    private static function formatted(string $style, string $input): string
    {
        return '' === $input ? '' : '<'.$style.">$input</>";
    }
}
