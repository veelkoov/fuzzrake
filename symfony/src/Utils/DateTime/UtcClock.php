<?php

declare(strict_types=1);

namespace App\Utils\DateTime;

use App\Utils\Traits\UtilityClass;
use App\Utils\UnbelievableRuntimeException;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use RuntimeException;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Clock\DatePoint;

final class UtcClock
{
    use UtilityClass;

    public static function get(): ClockInterface
    {
        $result = Clock::get();

        if ('UTC' !== $result->now()->getTimezone()->getName()) {
            throw new RuntimeException('Global clock is not set to UTC.');
        }

        return $result;
    }

    public static function getUtc(): DateTimeZone
    {
        return new DateTimeZone('UTC');
    }

    public static function now(): DateTimeImmutable
    {
        return self::get()->now();
    }

    public static function sleep(int $seconds): void
    {
        self::get()->sleep($seconds);
    }

    /**
     * @throws DateTimeException
     */
    public static function at(string $time): DateTimeImmutable
    {
        $timestamp = strtotime($time, self::time());

        if (false === $timestamp) {
            throw new DateTimeException("Failed to parse timestamp from input: '$time'");
        }

        return self::fromTimestamp($timestamp);
    }

    public static function fromTimestamp(int $timestamp): DateTimeImmutable
    {
        try {
            return DatePoint::createFromTimestamp($timestamp)->setTimezone(self::getUtc());
        } catch (Exception $exception) {
            throw new UnbelievableRuntimeException($exception); // Each timestamp can be converted to a date
        }
    }

    public static function passed(DateTimeImmutable $dateTime): bool
    {
        return self::now() > $dateTime;
    }

    public static function time(): int
    {
        return self::get()->now()->getTimestamp();
    }
}
